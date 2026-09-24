<?php

namespace Tests\Feature;

use App\Exports\TemplatePertanyaanKuesionerExport;
use App\Http\Controllers\InputPertanyaanKueController;
use App\Imports\PertanyaanKuesionerImport;
use App\Models\PertanyaanKuesioner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PertanyaanKuesionerImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'import_test', 'database.connections.import_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('pertanyaan_kuesioner', function (Blueprint $table) {
            $table->increments('id_pertanyaan_kuesioner');
            $table->unsignedInteger('kategori_kuesioner_id');
            $table->text('pertanyaan');
            $table->string('jenis_pertanyaan');
            $table->timestamps();
        });
    }

    public function test_imports_template_excel_with_both_types(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'kue');
        try {
            file_put_contents($path, Excel::raw(new TemplatePertanyaanKuesionerExport(), \Maatwebsite\Excel\Excel::XLSX));
            $import = new PertanyaanKuesionerImport(17);
            DB::transaction(fn () => Excel::import($import, $path, null, \Maatwebsite\Excel\Excel::XLSX));
            $this->assertSame(2, $import->count);
            $this->assertSame(['point', 'terbuka'], PertanyaanKuesioner::pluck('jenis_pertanyaan')->all());
            $this->assertSame([17, 17], PertanyaanKuesioner::pluck('kategori_kuesioner_id')->all());
        } finally {
            unlink($path);
        }
    }

    public function test_normalizes_types_and_reads_headers_by_name(): void
    {
        $import = new PertanyaanKuesionerImport(7);
        $import->collection(collect([
            ['jenis', 'tambahan', 'pertanyaan'],
            [' POINT ', null, ' Pertanyaan satu '],
            [null, null, null],
            ['Terbuka', null, 'Saran?'],
        ]));
        $this->assertSame(2, $import->count);
        $this->assertSame('Pertanyaan satu', PertanyaanKuesioner::first()->pertanyaan);
    }

    public function test_invalid_row_does_not_save_valid_rows(): void
    {
        try {
            (new PertanyaanKuesionerImport(7))->collection(collect([
                ['pertanyaan', 'jenis'], ['Valid', 'point'], ['', 'essay'],
            ]));
            $this->fail('Validation exception expected.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Baris 3', implode(' ', $exception->errors()['file_excel']));
            $this->assertSame(0, PertanyaanKuesioner::count());
        }
    }

    public function test_rejects_missing_headers_and_empty_files(): void
    {
        foreach ([[], [['pertanyaan']], [['pertanyaan', 'jenis']], [['pertanyaan', 'jenis', 'jenis']]] as $rows) {
            try {
                (new PertanyaanKuesionerImport(7))->collection(collect($rows));
                $this->fail('Validation exception expected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('file_excel', $exception->errors());
            }
        }
    }

    public function test_import_and_template_require_create_permission(): void
    {
        $request = Request::create('/');
        $user = \Mockery::mock();
        $user->shouldReceive('can')->with('create kuesioner')->twice()->andReturn(false);
        $request->setUserResolver(fn () => $user);
        foreach (['import', 'templateImport'] as $method) {
            try {
                (new InputPertanyaanKueController())->$method($request, 7);
                $this->fail('Forbidden expected.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
    }
}