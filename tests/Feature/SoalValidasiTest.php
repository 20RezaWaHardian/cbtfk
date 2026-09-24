<?php

namespace Tests\Feature;

use App\Http\Controllers\BankSoal\SoalController;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SoalValidasiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'review_test', 'database.connections.review_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('soal', function (Blueprint $table) {
            $table->increments('id_soal');
            $table->integer('status_validasi')->default(0);
            $table->text('pertanyaan')->nullable();
            $table->string('kunci')->nullable();
            $table->integer('poin')->nullable();
            $table->integer('sub_kategori_soal_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('id_pelaku')->nullable();
            $table->timestamps();
        });
        (require database_path('migrations/2026_09_12_000001_add_review_columns_to_soal_table.php'))->up();
        Schema::create('pilgan', function (Blueprint $table) {
            $table->increments('id_pilgan');
            $table->integer('soal_id');
            $table->string('kode');
            $table->text('teks');
            $table->timestamps();
        });
        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = 7;
        $user->id_asal = 17;
        $user->shouldReceive('hasAnyRole')->andReturn(false);
        $this->actingAs($user);
        Gate::swap(new \Illuminate\Auth\Access\Gate(app(), fn () => auth()->user()));
        Gate::before(fn () => true);
    }

    private function decide(array $ids, int $status, ?string $comment = null)
    {
        return app(SoalController::class)->validasiSoal(Request::create('/', 'POST', [
            'id' => $ids, 'status_validasi' => $status, 'komentar_validasi' => $comment,
        ]));
    }

    public function test_accepts_multiple_pending_questions(): void
    {
        $a = Soal::create(['pertanyaan' => 'Satu']);
        $b = Soal::create(['pertanyaan' => 'Dua']);
        $this->assertSame(0, $a->status_validasi);
        $this->decide([$a->id_soal, $b->id_soal], 1);
        $this->assertSame(2, Soal::where('status_validasi', 1)->count());
    }

    public function test_rejection_requires_non_blank_comment(): void
    {
        $soal = Soal::create(['pertanyaan' => 'Soal']);
        foreach ([null, '', '   '] as $comment) {
            try {
                $this->decide([$soal->id_soal], 2, $comment);
                $this->fail('Expected validation error.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('komentar_validasi', $exception->errors());
            }
        }
        $this->assertSame(0, $soal->fresh()->status_validasi);
    }

    public function test_rejection_and_resubmission_preserve_comment(): void
    {
        $soal = Soal::create(['pertanyaan' => 'Lama', 'created_by' => 17]);
        $this->decide([$soal->id_soal], 2, ' Perbaiki kunci ');
        $soal->refresh();
        $this->assertFalse($soal->dapatDiajukanUlang());
        $soal->update(['kunci' => 'Baru', 'diperbaiki_at' => now()]);
        $this->assertTrue($soal->dapatDiajukanUlang());
        $request = Request::create('/', 'POST');
        $request->setUserResolver(fn () => auth()->user());
        app(SoalController::class)->ajukanUlang($soal->id_soal, $request);
        $soal->refresh();
        $this->assertSame(0, $soal->status_validasi);
        $this->assertNotNull($soal->diajukan_ulang_at);
        $this->assertSame('Perbaiki kunci', $soal->komentar_validasi);
    }

    public function test_bulk_decision_rolls_back_when_one_question_already_reviewed(): void
    {
        $a = Soal::create(['pertanyaan' => 'Satu']);
        $b = Soal::create(['pertanyaan' => 'Dua', 'status_validasi' => 1]);
        try {
            $this->decide([$a->id_soal, $b->id_soal], 2, 'Perbaiki');
            $this->fail('Expected conflict.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
            $this->assertSame(0, $a->fresh()->status_validasi);
        }
    }

    public function test_validation_requires_permission(): void
    {
        Gate::swap(new \Illuminate\Auth\Access\Gate(app(), fn () => auth()->user()));
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->decide([1], 1);
    }

    public function test_comment_is_escaped(): void
    {
        $soal = new Soal(['status_validasi' => 2, 'komentar_validasi' => '<script>alert(1)</script>']);
        $html = view('bank-soal.soal.status-validasi', compact('soal'))->render();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Ditolak', $html);
    }

    public function test_successful_edit_records_actual_option_changes_without_resetting_status(): void
    {
        $soal = Soal::create(['pertanyaan' => 'Soal', 'created_by' => 17]);
        $soal->soal_pilgan()->create(['kode' => 'A', 'teks' => 'Lama']);
        $this->decide([$soal->id_soal], 2, 'Perbaiki pilihan');
        $request = Request::create('/', 'POST');
        $route = new \Illuminate\Routing\Route('POST', '/{id}', fn () => null);
        $route->bind(Request::create('/'.$soal->id_soal, 'POST'));
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => auth()->user());
        $middleware = app(SoalController::class)->getMiddleware()[1]['middleware'];
        $middleware($request, fn () => response('OK'));
        $this->assertNull($soal->fresh()->diperbaiki_at);
        $middleware($request, function () use ($soal) {
            $soal->soal_pilgan()->first()->update(['teks' => 'Baru']);
            return response('OK');
        });
        $soal->refresh();
        $this->assertSame(2, $soal->status_validasi);
        $this->assertNotNull($soal->diperbaiki_at);
        $this->assertTrue($soal->dapatDiajukanUlang());
    }

    public function test_resubmission_rejects_non_owner_and_unchanged_question(): void
    {
        $soal = Soal::create(['pertanyaan' => 'Soal', 'created_by' => 99]);
        $this->decide([$soal->id_soal], 2, 'Perbaiki');
        $request = Request::create('/', 'POST');
        $request->setUserResolver(fn () => auth()->user());
        foreach ([99 => 403, 17 => 409] as $owner => $status) {
            $soal->update(['created_by' => $owner]);
            try {
                app(SoalController::class)->ajukanUlang($soal->id_soal, $request);
                $this->fail('Expected rejected request.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                $this->assertSame($status, $exception->getStatusCode());
                $this->assertSame(2, $soal->fresh()->status_validasi);
            }
        }
    }
}