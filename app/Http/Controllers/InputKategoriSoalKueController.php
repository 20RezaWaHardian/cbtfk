<?php

namespace App\Http\Controllers;

use App\Models\KategoriSoalKue;
use App\Models\Kuesioner;
use Illuminate\Http\Request;

class InputKategoriSoalKueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id_kuesioner)
    {
        $kuesioner = Kuesioner::find($id_kuesioner);
        $kategori = KategoriSoalKue::where('kuesioner_id',$id_kuesioner)->get();
        return view('kuesioner.kategori.daftar_kategori', compact('kuesioner','kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function buatKategori(Request $request)
    {
        $kuesionerId = $request->input('kuesionerId');

        $kategori = new KategoriSoalKue();
        $kuesioner = Kuesioner::findorfail($kuesionerId);
        return view('kuesioner.kategori.action_kategori', compact('kategori', 'kuesioner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kuesioner_id' => 'required',
            'nama_kategori' => 'required'
        ], [
            'kuesioner_id' => 'Kuesioner Wajib Diinput',
            'nama_kategori' => 'Nama Kategori Wajib Diinput'
        ]);

        KategoriSoalKue::create($validated);

        return redirect()->back()->with('success', 'Kategori Berhasil Diinput');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)

    {
        $kategori = KategoriSoalKue::find($id);
        $kuesioner = Kuesioner::where('id_kuesioner', $kategori->kuesioner_id)->first();

        return view('kuesioner.kategori.action_kategori', compact('kategori', 'kuesioner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kuesioner_id' => 'required',
            'nama_kategori' => 'required'
        ], [
            'kuesioner_id' => 'Kuesioner Wajib Diinput',
            'nama_kategori' => 'Nama Kategori Wajib Diinput'
        ]);

        KategoriSoalKue::where('id_kategori_kuesioner', $id)->update($validated);

        return redirect()->back()->with('success', 'Kategori Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $data = KategoriSoalKue::findOrFail($id);
    $kuesionerId = $data->kuesioner_id;
    $data->delete();

    return redirect()->route('kuesioner.kategori-kue.index', ['id_kuesioner' => $kuesionerId])
                     ->with('success', 'Kategori Kuesioner berhasil dihapus.');
}

}
