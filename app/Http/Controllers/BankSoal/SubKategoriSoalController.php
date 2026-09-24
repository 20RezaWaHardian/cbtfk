<?php

namespace App\Http\Controllers\BankSoal;

use App\Helpers\LogAktifitas;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubKategoriSoalRequest;
use App\Models\SubKategoriSoal;

class SubKategoriSoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read bank-soal/soal');
    }

    public function store(SubKategoriSoalRequest $request)
    {
        $this->authorize('create bank-soal/soal');

        $subKategori = SubKategoriSoal::create([
            'id_kategori_soal' => $request->id_kategori_soal,
            'nama_kategori' => $request->nama_kategori,
            'status' => $request->status ?? 1,
            'is_delete' => false,
            'id_kelas' => $request->id_kelas,
            'id_pelaku' => auth()->user()->id,
        ]);

        LogAktifitas::catat('Membuat Sub Kategori ' . $subKategori->nama_kategori . ' Pada Kategori Soal.');

        return redirect()->back()->with('success', 'Berhasil Membuat Sub Kategori Soal');
    }

    public function update(SubKategoriSoalRequest $request, $id_sub_kategori)
    {
        $this->authorize('update bank-soal/soal');

        $subKategori = SubKategoriSoal::findOrFail($id_sub_kategori);
        $namaLama = $subKategori->nama_kategori;
        $subKategori->update([
            'id_kategori_soal' => $request->id_kategori_soal,
            'nama_kategori' => $request->nama_kategori,
            'status' => $request->status ?? 1,
            'id_kelas' => $request->id_kelas,
            'id_pelaku' => auth()->user()->id,
        ]);

        LogAktifitas::catat('Merubah Sub Kategori ' . $namaLama . ' Menjadi ' . $subKategori->nama_kategori . ' Pada Kategori Soal.');

        return redirect()->back()->with('success', 'Berhasil Mengupdate Sub Kategori Soal');
    }

    public function destroy($id_sub_kategori)
    {
        // dd('okdas');
        $this->authorize('delete bank-soal/soal');

        $subKategori = SubKategoriSoal::active()->withCount('soal')->findOrFail($id_sub_kategori);
        // dd($subKategori);
        // if ($subKategori->soal_count > 0) {
        //     return redirect()->back()->with('error', 'Sub kategori masih memiliki soal, tidak dapat dihapus.');
        // }

        $nama = $subKategori->nama_kategori;
        $subKategori->update(['is_delete' => true]);
        LogAktifitas::catat('Menghapus Sub Kategori ' . $nama . ' Pada Kategori Soal.');

        return redirect()->back()->with('success', 'Berhasil Menghapus Sub Kategori Soal');
    }
}
