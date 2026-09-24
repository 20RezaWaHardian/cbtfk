<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;
    protected $table = 'soal';
    public $primaryKey = 'id_soal';
    protected $guarded = [];

    protected $attributes = ['status_validasi' => 0];

    protected $casts = [
        'status_validasi' => 'integer',
        'diperbaiki_at' => 'datetime',
        'diajukan_ulang_at' => 'datetime',
    ];

    public function milikPembuat($user): bool
    {
        return $user && ($this->created_by !== null
            ? $user->id_asal !== null && (string) $this->created_by === (string) $user->id_asal
            : $this->id_pelaku !== null && (string) $this->id_pelaku === (string) $user->id);
    }

    public function sidikIsi(): string
    {
        return hash('sha256', json_encode([
            (string) $this->pertanyaan, (string) $this->kunci,
            (string) $this->poin, (string) $this->sub_kategori_soal_id,
            $this->soal_pilgan()->orderBy('kode')->get(['kode', 'teks'])->toArray(),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function dapatDiajukanUlang(): bool
    {
        return $this->status_validasi === 2 && $this->diperbaiki_at !== null
            && $this->sidik_penolakan !== null && $this->sidikIsi() !== $this->sidik_penolakan;
    }

    public function soal_pilgan()
    {
        return $this->hasMany(Pilgan::class, 'soal_id');
    }
    public function soal_essay()
    {
        return $this->hasOne(Essay::class, 'soal_id');
    }

    public function paket_soal()
    {
        return $this->belongsToMany(PaketSoal::class, 'paket_has_soal', 'soal_id', 'paket_soal_id')->withPivot('poin');
    }

    public function kategori_soal()
    {
        return $this->hasOneThrough(
            KategoriSoal::class,
            SubKategoriSoal::class,
            'id_sub_kategori_soal',
            'id_kategori_soal',
            'sub_kategori_soal_id',
            'id_kategori_soal'
        );
    }

    public function getKategoriSoalIdAttribute()
    {
        return $this->sub_kategori_soal?->id_kategori_soal;
    }

    public function sub_kategori_soal()
    {
        return $this->belongsTo(SubKategoriSoal::class, 'sub_kategori_soal_id', 'id_sub_kategori_soal');
    }

    public function pembuat_soal()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_asal');
    }
}
