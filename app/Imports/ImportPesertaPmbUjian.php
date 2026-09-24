<?php

namespace App\Imports;

use App\Models\PesertaUjian;
use App\Models\PesertaUjianEksternal;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportPesertaPmbUjian implements ToCollection, WithHeadingRow
{
    protected Ujian $ujian;
    protected int $imported = 0;
    protected int $skipped = 0;

    public function __construct(Ujian $ujian)
    {
        $this->ujian = $ujian;
    }

    public function collection(Collection $collection)
    {
        $wajib = $this->ujian->is_kuesioner == 1 ? 1 : 0;
        $kuesionerId = $this->ujian->is_kuesioner == 1 ? $this->ujian->kuesioner_id : null;

        foreach ($collection as $index => $row) {
            $username = trim((string) ($row['username'] ?? ''));
            $namaPeserta = trim((string) ($row['nama_peserta'] ?? ''));
            $baris = $index + 2;

            if ($username === '') {
                continue;
            }

            if ($namaPeserta === '') {
                $namaPeserta = $username;
            }

            $passwordPlain = 'CBT-' . random_int(100000, 999999);
            $idPmb = (string) ($row['id_pmb'] ?? $row['id_user'] ?? $username);

            $pesertaEksternal = PesertaUjianEksternal::updateOrCreate(
                ['id_pmb' => $idPmb],
                [
                    'username'       => $username,
                    'password_plain' => $passwordPlain,
                    'nama_peserta'   => $namaPeserta,
                    'email'          => $row['email'] ?? null,
                    'no_hp'          => $row['no_hp'] ?? null,
                ]
            );

            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'id_asal'   => $idPmb,
                    'asal_user' => 'pmb',
                    'name'      => $namaPeserta,
                    'email'     => $username . '@peserta-eksternal.local',
                    'usertype'  => 'peserta-eksternal',
                    'password'  => Hash::make($passwordPlain),
                ]
            );

            if (method_exists($user, 'assignRole') && !$user->hasRole('peserta-eksternal')) {
                $user->assignRole('peserta-eksternal');
            }

            $pesertaUjian = PesertaUjian::updateOrCreate(
                [
                    'id_peserta_eksternal' => $pesertaEksternal->id_peserta_eksternal,
                    'ujian_id'             => $this->ujian->id_ujian,
                ],
                [
                    'need_kuesioner' => $wajib,
                    'kuesioner_id'   => $kuesionerId,
                ]
            );

            $pesertaUjian->wasRecentlyCreated ? $this->imported++ : $this->skipped++;
        }
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }
}