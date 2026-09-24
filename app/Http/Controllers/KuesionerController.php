<?php

namespace App\Http\Controllers;

use App\Models\Kuesioner;
use Illuminate\Http\Request;

class KuesionerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read kuesioner');
    }

    public function index()
    {
        $kuesioner = Kuesioner::all();
        return view('kuesioner.daftar', compact('kuesioner'));
    }

    public function create()
    {
        $kuesioner = new Kuesioner();
        return view('kuesioner.action_kue', compact('kuesioner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_kuesioner' => 'required'
        ], [
            'judul_kuesioner' => 'Judul Kuesioner Wajib Diinput'
        ]);

        Kuesioner::create($validated);

        return redirect()->back()->with('success', 'Berhasil Mmebuat Kuesioner');
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
        $kuesioner = Kuesioner::find($id);
        return view('kuesioner.action_kue', compact('kuesioner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'judul_kuesioner' => 'required'
        ], [
            'judul_kuesioner' => 'Judul Kuesioner Wajib Diinput'
        ]);

        Kuesioner::where('id_kuesioner', $id)->update([
            'judul_kuesioner' => $request->judul_kuesioner,
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Berhasil Mengubah Kuesioner');
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        $kuesioner->delete();

        return redirect()->route('kuesioner.index')->with('success', 'Kuesioner berhasil dihapus.');
    }
}
