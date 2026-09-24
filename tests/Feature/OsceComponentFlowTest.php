<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\JenisOsce;
use App\Models\KomponenNilaiOsce;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OsceComponentFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('jenis_osce', function ($table) {
            $table->increments('id_jenis_osce');
            $table->string('nama_jenis_osce');
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });
        Schema::create('komponen_nilai_osce', function ($table) {
            $table->increments('id_komponen_nilai_osce');
            $table->integer('id_jenis_osce');
            $table->string('nama_komponen');
            $table->text('detail_komponen');
            $table->integer('bobot_nilai');
            $table->boolean('isDeleted')->default(false);
            $table->timestamps();
        });
        Schema::create('instrumen_nilai_osce', function ($table) {
            $table->id();
            $table->integer('id_komponen_nilai_osce');
            $table->integer('nilai');
            $table->text('keterangan');
            $table->timestamps();
        });
        Schema::create('nilai_peserta_station_osce', function ($table) {
            $table->id();
            $table->integer('id_komponen_nilai_osce');
            $table->integer('nilai');
        });
        foreach (glob(database_path('migrations/*create_permission_tables.php')) as $migration) {
            (require $migration)->up();
        }
        foreach (['read', 'create', 'update', 'delete'] as $action) {
            Gate::define($action.' data-master/komponen-nilai-osce', fn ($user) => $user->username === '20220017');
        }
        $this->actingAs(new User(['id' => 1, 'username' => '20220017']));
        JenisOsce::create(['nama_jenis_osce' => 'Pemeriksaan']);
    }

    private function createComponent(): KomponenNilaiOsce
    {
        return KomponenNilaiOsce::create(['id_jenis_osce' => 1, 'nama_komponen' => 'Fisik', 'detail_komponen' => 'Periksa pasien', 'bobot_nilai' => 2]);
    }

    public function test_store_redirects_to_separate_instrument_page(): void
    {
        $response = $this->post(route('data-master.komponen-nilai-osce.store'), [
            'id_jenis_osce' => encrypt(1), 'nama_komponen' => 'Fisik', 'detail_komponen' => 'Periksa pasien', 'bobot_nilai' => 0,
        ]);
        $response->assertRedirect();
        $this->assertStringEndsWith('/instrumen', $response->headers->get('Location'));
        $this->assertDatabaseHas('komponen_nilai_osce', ['id_jenis_osce' => 1, 'bobot_nilai' => 0]);
        $controller = app(\App\Http\Controllers\KomponenNilaiOsceController::class);
        $this->assertSame('data-master.komponen-nilai.action', $controller->create(encrypt(1))->name());
        $this->assertSame('data-master.komponen-nilai.instrumen', $controller->instrumen(encrypt(1))->name());
    }

    public function test_instrument_validation_and_parent_identity(): void
    {
        $komponen = $this->createComponent();
        $url = route('data-master.komponen-nilai-osce.storeInstrumen');
        $data = ['id_komponen_nilai_osce' => encrypt($komponen->id_komponen_nilai_osce), 'nilai' => 0, 'keterangan' => 'Tidak dilakukan'];
        $this->postJson($url, array_merge($data, ['nilai' => 4]))->assertUnprocessable()->assertJsonValidationErrors('nilai');
        $this->postJson($url, $data)->assertOk();
        $this->postJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors('nilai');
        $this->putJson(route('data-master.komponen-nilai-osce.updateInstrumen', encrypt(1)), [
            'id_komponen_nilai_osce' => encrypt(999), 'nilai' => 1, 'keterangan' => 'Sebagian',
        ])->assertOk();
        $this->assertDatabaseHas('instrumen_nilai_osce', ['id' => 1, 'id_komponen_nilai_osce' => 1, 'nilai' => 1]);
        $this->deleteJson(route('data-master.komponen-nilai-osce.destroyInstrumen', encrypt(1)))->assertOk();
        $this->assertDatabaseCount('instrumen_nilai_osce', 0);
    }

    public function test_used_instrument_cannot_be_deleted(): void
    {
        $komponen = $this->createComponent();
        $instrumen = $komponen->instrumenNilai()->create(['nilai' => 0, 'keterangan' => 'Tidak dilakukan']);
        DB::table('nilai_peserta_station_osce')->insert(['id_komponen_nilai_osce' => $komponen->id_komponen_nilai_osce, 'nilai' => 0]);
        $this->deleteJson(route('data-master.komponen-nilai-osce.destroyInstrumen', encrypt($instrumen->id)))->assertUnprocessable();
        $this->assertDatabaseCount('instrumen_nilai_osce', 1);
    }

    public function test_component_validation_preserves_input(): void
    {
        $this->from(route('data-master.komponen-nilai-osce.create', encrypt(1)))
            ->post(route('data-master.komponen-nilai-osce.store'), ['nama_komponen' => 'Fisik'])
            ->assertSessionHasErrors(['detail_komponen', 'bobot_nilai'])->assertSessionHasInput('nama_komponen', 'Fisik');
        $this->assertDatabaseCount('komponen_nilai_osce', 0);
    }

    public function test_user_without_permission_cannot_save(): void
    {
        $this->actingAs(new User(['id' => 2, 'username' => 'ordinary']));
        $this->postJson(route('data-master.komponen-nilai-osce.store'), [])->assertForbidden();
        $this->postJson(route('data-master.komponen-nilai-osce.storeInstrumen'), [])->assertForbidden();
    }

    public function test_jenis_table_counts_only_active_components_for_each_jenis(): void
    {
        $this->createComponent();
        $this->createComponent();
        $this->createComponent()->update(['isDeleted' => true]);
        $empty = JenisOsce::create(['nama_jenis_osce' => 'Kosong']);
        $other = JenisOsce::create(['nama_jenis_osce' => 'Lain']);
        $this->createComponent()->update(['id_jenis_osce' => $other->id_jenis_osce]);

        $table = app(\App\DataTables\JenisOsceDataTable::class);
        $rows = $table->query(new JenisOsce())->get()->keyBy('id_jenis_osce');
        $this->assertSame(2, (int) $rows[1]->komponen_count);
        $this->assertSame(0, (int) $rows[$empty->id_jenis_osce]->komponen_count);
        $this->assertSame(1, (int) $rows[$other->id_jenis_osce]->komponen_count);
        $column = collect($table->getColumns())->first(fn ($column) => $column->data === 'komponen_count');
        $this->assertNotNull($column);
        $this->assertSame('Jumlah Komponen Nilai', $column->title);
    }
}