<?php

namespace App\Services;

use App\Models\JadwalOsce;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OsceParticipantService
{
    public function setParticipants(int $idJadwal, array $ids, ?int $idStase = null, ?int $urutan = null): void
    {
        DB::transaction(function () use ($idJadwal, $ids, $idStase, $urutan) {
            $jadwal = JadwalOsce::where('isDeleted', false)->lockForUpdate()->findOrFail($idJadwal);
            if ($idStase && !DB::table('jadwal_has_stase')->where('id_jadwal_osce', $idJadwal)->where('id_jenis_osce', $idStase)->exists()) {
                throw ValidationException::withMessages(['id_jenis_osce' => 'Stase tidak terdaftar pada jadwal ini.']);
            }

            foreach (array_values(array_unique($ids)) as $offset => $idMhs) {
                $terdaftar = DB::table('jadwal_has_mhs')->where('id_jadwal_osce', $idJadwal)->where('id_mhs_pt', $idMhs)->exists();
                if (!$terdaftar && !DB::table('sistembl_siakad-uin .peserta_blok as pb')
                    ->join('sistembl_siakad-uin .mahasiswa as m', 'm.id_mahasiswa', '=', 'pb.mahasiswa_id')
                    ->where('pb.blok_id', $jadwal->blok_id)->where('pb.mahasiswa_id', $idMhs)->exists()) {
                    throw ValidationException::withMessages(['id_mhs_pt' => 'Mahasiswa bukan peserta kontrak blok jadwal ini.']);
                }
                $riwayat = DB::table('peserta_station_osce')->where('id_jadwal_osce', $idJadwal)->where('id_mhs_pt', $idMhs)->get();
                if ($idStase && ($riwayat->count() > 1 || $riwayat->contains(fn ($row) => $row->status !== 'menunggu' || $row->waktu_mulai !== null)
                    || DB::table('nilai_peserta_station_osce')->whereIn('id_peserta_station_osce', $riwayat->pluck('id_peserta_station_osce'))->exists())) {
                    throw ValidationException::withMessages(['id_mhs_pt' => 'Pembagian peserta yang sudah mulai dinilai tidak boleh diubah.']);
                }

                DB::table('jadwal_has_mhs')->updateOrInsert(['id_jadwal_osce' => $idJadwal, 'id_mhs_pt' => $idMhs], []);
                if (!$idStase) {
                    continue;
                }
                $awal = $riwayat->first();
                $nomor = $urutan ? $urutan + $offset : ($awal && (int) $awal->id_jenis_osce === $idStase ? $awal->urutan_antrian :
                    ((int) DB::table('peserta_station_osce')->where('id_jadwal_osce', $idJadwal)->where('id_jenis_osce', $idStase)->max('urutan_antrian') + 1));
                $bentrok = DB::table('peserta_station_osce')->where('id_jadwal_osce', $idJadwal)->where('id_jenis_osce', $idStase)
                    ->where('urutan_antrian', $nomor)->when($awal, fn ($q) => $q->where('id_peserta_station_osce', '!=', $awal->id_peserta_station_osce))->exists();
                if ($bentrok) {
                    throw ValidationException::withMessages(['urutan_antrian' => 'Urutan antrean sudah digunakan pada stase ini.']);
                }
                $data = ['id_jenis_osce' => $idStase, 'urutan_antrian' => $nomor, 'updated_at' => now()];
                if ($awal) {
                    DB::table('peserta_station_osce')->where('id_peserta_station_osce', $awal->id_peserta_station_osce)->update($data);
                } else {
                    DB::table('peserta_station_osce')->insert($data + [
                        'id_jadwal_osce' => $idJadwal, 'id_mhs_pt' => $idMhs, 'status' => 'menunggu', 'waktu_masuk' => now(), 'created_at' => now(),
                    ]);
                }
            }
        });
    }

    public function removeParticipant(int $id): void
    {
        DB::transaction(function () use ($id) {
            $peserta = DB::table('jadwal_has_mhs')->where('id_jadwal_has_mhs', $id)->first();
            abort_if(!$peserta, 404);
            JadwalOsce::where('isDeleted', false)->lockForUpdate()->findOrFail($peserta->id_jadwal_osce);
            $riwayat = DB::table('peserta_station_osce')->where('id_jadwal_osce', $peserta->id_jadwal_osce)->where('id_mhs_pt', $peserta->id_mhs_pt)->get();
            if ($riwayat->contains(fn ($row) => $row->status !== 'menunggu' || $row->waktu_mulai !== null)
                || DB::table('nilai_peserta_station_osce')->whereIn('id_peserta_station_osce', $riwayat->pluck('id_peserta_station_osce'))->exists()) {
                throw ValidationException::withMessages(['peserta' => 'Peserta yang sudah mulai dinilai tidak boleh dihapus.']);
            }
            DB::table('peserta_station_osce')->whereIn('id_peserta_station_osce', $riwayat->pluck('id_peserta_station_osce'))->delete();
            DB::table('jadwal_has_mhs')->where('id_jadwal_has_mhs', $id)->delete();
        });
    }
}