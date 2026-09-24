<?php

namespace Tests\Feature;

use App\Models\SiakadDosen;
use App\Models\User;
use App\Services\OsceParticipantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OsceParticipantFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::statement("ATTACH DATABASE ':memory:' AS siakad");
        DB::statement("ATTACH DATABASE ':memory:' AS sistem_blok");
        DB::statement('CREATE TABLE sistem_blok.mahasiswa (id_mahasiswa INTEGER PRIMARY KEY, nim TEXT, nama TEXT)');
        DB::statement('CREATE TABLE sistem_blok.peserta_blok (mahasiswa_id INTEGER, blok_id INTEGER)');
        DB::statement('CREATE TABLE siakad.mhs_pt (id_mhs_pt INTEGER PRIMARY KEY, no_mhs TEXT, id_mahasiswa INTEGER)');
        DB::statement('CREATE TABLE siakad.mahasiswa (id_mahasiswa INTEGER PRIMARY KEY, nama_mahasiswa TEXT)');
        Schema::create('jadwal_osce', function ($t) {
            $t->increments('id_jadwal_osce');
            $t->boolean('isDeleted')->default(false);
            $t->integer('status')->default(1);
            $t->integer('blok_id')->default(1);
            $t->string('keterangan')->default('Ujian');
            $t->date('tanggal_ujian')->nullable();
        });
        Schema::create('jadwal_has_mhs', function ($t) {
            $t->increments('id_jadwal_has_mhs');
            $t->integer('id_jadwal_osce');
            $t->integer('id_mhs_pt');
        });
        Schema::create('jadwal_has_stase', function ($t) {
            $t->increments('id_jadwal_has_stase');
            $t->integer('id_jadwal_osce');
            $t->integer('id_jenis_osce');
            $t->integer('id_pegawai');
        });
        Schema::create('jenis_osce', function ($t) {
            $t->increments('id_jenis_osce');
            $t->string('nama_jenis_osce');
        });
        Schema::create('komponen_nilai_osce', function ($t) {
            $t->increments('id_komponen_nilai_osce');
            $t->integer('id_jenis_osce');
            $t->integer('bobot_nilai')->default(1);
            $t->string('nama_komponen')->default('Komponen');
            $t->text('detail_komponen')->default('Detail');
            $t->boolean('isDeleted')->default(false);
        });
        Schema::create('instrumen_nilai_osce', function ($t) {
            $t->id();
            $t->integer('id_komponen_nilai_osce');
            $t->integer('nilai');
            $t->text('keterangan')->default('Benar');
        });
        (require database_path('migrations/2026_08_19_233645_create_peserta_station_osce_tables.php'))->up();
        foreach (glob(database_path('migrations/*create_permission_tables.php')) as $migration) {
            (require $migration)->up();
        }
        DB::table('jadwal_osce')->insert([['id_jadwal_osce' => 1], ['id_jadwal_osce' => 2]]);
        DB::table('jadwal_has_stase')->insert([
            ['id_jadwal_osce' => 1, 'id_jenis_osce' => 1, 'id_pegawai' => 10],
            ['id_jadwal_osce' => 1, 'id_jenis_osce' => 2, 'id_pegawai' => 20],
            ['id_jadwal_osce' => 2, 'id_jenis_osce' => 3, 'id_pegawai' => 20],
        ]);
        foreach ([1, 2, 3] as $id) {
            DB::table('sistem_blok.mahasiswa')->insert(['id_mahasiswa' => $id, 'nim' => 'NIM'.$id, 'nama' => 'Peserta '.$id]);
            DB::table('sistem_blok.peserta_blok')->insert(['mahasiswa_id' => $id, 'blok_id' => 1]);
            DB::table('jenis_osce')->insert(['id_jenis_osce' => $id, 'nama_jenis_osce' => 'Stase '.$id]);
            DB::table('komponen_nilai_osce')->insert(['id_komponen_nilai_osce' => $id, 'id_jenis_osce' => $id]);
            DB::table('instrumen_nilai_osce')->insert(['id_komponen_nilai_osce' => $id, 'nilai' => 3]);
            DB::table('siakad.mhs_pt')->insert(['id_mhs_pt' => $id, 'no_mhs' => 'NIM'.$id, 'id_mahasiswa' => $id]);
            DB::table('siakad.mahasiswa')->insert(['id_mahasiswa' => $id, 'nama_mahasiswa' => 'Peserta '.$id]);
        }
        foreach (['create', 'read', 'delete'] as $action) {
            Gate::define($action.' data-master/jadwal-osce', fn ($user) => $user->username === 'admin-osce');
        }
        $this->asExaminer(10);
    }

    private function asExaminer(int $id): void
    {
        $user = new User(['id' => $id, 'username' => 'penguji']);
        $user->exists = true;
        $user->setRelation('dosen', new SiakadDosen(['id_dosen' => $id]));
        foreach (['read osce-antrian', 'update osce-antrian'] as $permission) {
            $user->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate($permission, 'web'));
        }
        $this->actingAs($user);
    }

    private function place(): void
    {
        app(OsceParticipantService::class)->setParticipants(1, [1], 1);
    }

    public function test_assignment_without_permission_cannot_access_queue(): void
    {
        $this->place();
        auth()->user()->revokePermissionTo(['read osce-antrian', 'update osce-antrian']);
        $this->get(route('osce-antrian.index'))->assertForbidden();
        $this->get(route('osce-antrian.station', encrypt(1)))->assertForbidden();
        $this->finish(2)->assertForbidden();
    }

    public function test_read_only_examiner_cannot_start_or_save_assessment(): void
    {
        $this->place();
        auth()->user()->revokePermissionTo('update osce-antrian');
        $this->get(route('osce-antrian.nilai', encrypt(1)))->assertForbidden();
        $this->postJson(route('osce-antrian.simpan-nilai-komponen'), [
            'id_peserta_station_osce' => 1, 'id_komponen_nilai_osce' => 1, 'nilai' => 3,
        ])->assertForbidden();
        $this->finish(2)->assertForbidden();
        $this->assertDatabaseHas('peserta_station_osce', ['id_peserta_station_osce' => 1, 'status' => 'menunggu']);
        $this->assertDatabaseCount('nilai_peserta_station_osce', 0);
    }

    public function test_developer_without_dosen_can_score_other_assignments_but_not_bypass_progress(): void
    {
        $this->place();
        $user = new User(['id' => 99, 'username' => 'developer']);
        $user->exists = true;
        $user->setRelation('dosen', null);
        $user->assignRole(\Spatie\Permission\Models\Role::findOrCreate('developer', 'web'));
        $this->actingAs($user);
        $this->assertTrue(Gate::allows('read osce-antrian'));
        $this->assertFalse(Gate::allows('delete unrelated-module'));
        $this->finish(2)->assertSessionHas('success');
        $this->assertDatabaseHas('nilai_peserta_station_osce', ['id_peserta_station_osce' => 1, 'id_pegawai' => null]);
        $this->finish(2)->assertStatus(409);
        $this->finish('selesai_ujian', 2, 2)->assertSessionHas('success');
        DB::table('jadwal_osce')->where('id_jadwal_osce', 1)->update(['status' => 0]);
        $this->get(route('osce-antrian.station', encrypt(1)))->assertForbidden();
    }

    private function finish($destination, int $id = 1, int $component = 1)
    {
        return $this->post(route('osce-antrian.simpan-nilai'), [
            'id_peserta_station_osce' => $id, 'tombol' => 'selesai',
            'id_station_berikutnya' => $destination, 'nilai_mhs' => [$component => 3],
        ]);
    }

    public function test_registration_is_once_per_schedule_and_placement_is_optional(): void
    {
        $this->actingAs(new User(['id' => 1, 'username' => 'admin-osce']));
        $data = ['id_jadwal_osce' => encrypt(1), 'id_mhs_pt' => [1, 2]];
        $this->post(route('data-master.storePesertaOsce'), $data)->assertRedirect();
        $this->post(route('data-master.storePesertaOsce'), $data)->assertRedirect();
        $this->assertDatabaseCount('jadwal_has_mhs', 2);
        $this->assertDatabaseCount('peserta_station_osce', 0);
        app(OsceParticipantService::class)->setParticipants(1, [1, 2], 1);
        $this->assertDatabaseHas('peserta_station_osce', ['id_mhs_pt' => 2, 'urutan_antrian' => 2]);
        app(OsceParticipantService::class)->setParticipants(2, [1], 3);
        $this->assertDatabaseCount('jadwal_has_mhs', 3);
    }

    public function test_foreign_station_rejects_entire_registration(): void
    {
        try {
            app(OsceParticipantService::class)->setParticipants(1, [1], 3);
            $this->fail('Foreign station accepted');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('jadwal_has_mhs', 0);
        }
    }

    public function test_queue_query_uses_registered_student_identity(): void
    {
        $this->place();
        $view = app(\App\Http\Controllers\OsceAntrianController::class)->peserta(encrypt(1), encrypt(1));
        $rows = $view->getData()['peserta'];
        $this->assertCount(1, $rows);
        $this->assertSame('NIM1', $rows->first()->no_mhs);
        $this->assertSame('Peserta 1', $rows->first()->nama_mahasiswa);
    }

    public function test_examiner_cannot_access_other_assignment_or_registration(): void
    {
        $this->place();
        $this->asExaminer(20);
        $this->get(route('osce-antrian.peserta', [encrypt(1), encrypt(1)]))->assertForbidden();
        $this->get(route('osce-antrian.nilai', encrypt(1)))->assertForbidden();
        $this->postJson(route('osce-antrian.simpan-nilai-komponen'), [
            'id_peserta_station_osce' => 1, 'id_komponen_nilai_osce' => 1, 'nilai' => 3,
        ])->assertForbidden();
        $this->finish(2)->assertForbidden();
        $this->postJson(route('data-master.storePesertaOsce'), [])->assertForbidden();
        $this->post('/osce-antrian/peserta/store', [])->assertNotFound();
        $this->post('/osce-antrian/peserta/import', [])->assertNotFound();
        $this->assertDatabaseCount('nilai_peserta_station_osce', 0);
    }

    public function test_transfer_preserves_history_and_repeated_request_cannot_duplicate_queue(): void
    {
        $this->place();
        $this->finish(2)->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseCount('peserta_station_osce', 2);
        $this->assertDatabaseHas('peserta_station_osce', ['id_peserta_station_osce' => 1, 'status' => 'selesai_dinilai']);
        $this->assertDatabaseHas('nilai_peserta_station_osce', ['id_peserta_station_osce' => 1, 'id_pegawai' => 10, 'nilai' => 3]);
        $this->finish(2)->assertStatus(409);
        $this->assertDatabaseCount('peserta_station_osce', 2);
        $this->asExaminer(20);
        $this->finish('selesai_ujian', 2, 2)->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('peserta_station_osce', ['id_peserta_station_osce' => 2, 'status' => 'selesai_ujian']);
    }

    public function test_invalid_destination_or_incomplete_exam_does_not_save(): void
    {
        $this->place();
        foreach ([3, 1, 'selesai_ujian'] as $destination) {
            $this->finish($destination)->assertRedirect()->assertSessionHas('error');
            $this->assertDatabaseCount('nilai_peserta_station_osce', 0);
            $this->assertDatabaseCount('peserta_station_osce', 1);
        }
        $this->post(route('osce-antrian.simpan-nilai'), [
            'id_peserta_station_osce' => 1, 'tombol' => 'selesai', 'id_station_berikutnya' => 2,
        ])->assertSessionHas('error');
    }

    public function test_placement_repeat_and_progress_lock(): void
    {
        $this->place();
        $this->place();
        $this->assertDatabaseCount('jadwal_has_mhs', 1);
        $this->assertDatabaseCount('peserta_station_osce', 1);
        $this->postJson(route('osce-antrian.simpan-nilai-komponen'), [
            'id_peserta_station_osce' => 1, 'id_komponen_nilai_osce' => 1, 'nilai' => 3,
        ])->assertOk();
        try {
            $this->place();
            $this->fail('Progress reset permitted');
        } catch (ValidationException $e) {
            $this->assertDatabaseHas('peserta_station_osce', ['status' => 'sedang_dinilai']);
            $this->assertDatabaseHas('nilai_peserta_station_osce', ['nilai' => 3]);
        }
        try {
            app(OsceParticipantService::class)->removeParticipant(1);
            $this->fail('Scored participant deleted');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('jadwal_has_mhs', 1);
        }
    }

    public function test_foreign_block_rolls_back_entire_selection(): void
    {
        DB::table('sistem_blok.peserta_blok')->where('mahasiswa_id', 2)->update(['blok_id' => 2]);
        try {
            app(OsceParticipantService::class)->setParticipants(1, [1, 2], 1);
            $this->fail('Foreign block accepted');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('jadwal_has_mhs', 0);
            $this->assertDatabaseCount('peserta_station_osce', 0);
        }
    }

    public function test_import_routes_are_removed(): void
    {
        foreach (['importPeserta', 'simpanImportPeserta', 'downloadFormatPeserta'] as $name) {
            $this->assertFalse(\Illuminate\Support\Facades\Route::has('data-master.'.$name));
        }
    }

    public function test_existing_participant_survives_contract_change(): void
    {
        $this->place();
        DB::table('sistem_blok.peserta_blok')->where('mahasiswa_id', 1)->delete();
        app(OsceParticipantService::class)->setParticipants(1, [1], 2);
        $this->assertDatabaseHas('peserta_station_osce', ['id_mhs_pt' => 1, 'id_jenis_osce' => 2]);
    }
}