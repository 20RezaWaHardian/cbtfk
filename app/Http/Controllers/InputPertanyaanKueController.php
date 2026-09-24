<?php

namespace App\Http\Controllers;

use App\Models\KategoriSoalKue;
use App\Models\PertanyaanKuesioner;
use Illuminate\Http\Request;
use App\Imports\PertanyaanKuesionerImport;
use App\Exports\TemplatePertanyaanKuesionerExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class InputPertanyaanKueController extends Controller
{
    public function templateImport(Request $request)
    {
        abort_unless($request->user()->can('create kuesioner'), 403);

        return Excel::download(new TemplatePertanyaanKuesionerExport(), 'template-pertanyaan-kuesioner.xlsx');
    }

    public function import(Request $request, $id_kategori_kuesioner)
    {
        abort_unless($request->user()->can('create kuesioner'), 403);
        $kategori = KategoriSoalKue::findOrFail($id_kategori_kuesioner);
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file_excel.required' => 'Pilih file Excel terlebih dahulu.',
            'file_excel.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file_excel.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $import = new PertanyaanKuesionerImport($kategori->id_kategori_kuesioner);
        try {
            DB::transaction(function () use ($import, $request) {
                Excel::import($import, $request->file('file_excel'));
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['file_excel' => 'Import gagal. Pastikan file Excel valid dan sesuai template. Tidak ada pertanyaan yang disimpan.']);
        }

        return redirect()->route('kuesioner.pertanyaan-kue.index', $kategori->id_kategori_kuesioner)
            ->with('import_success', "{$import->count} pertanyaan berhasil diimpor.");
    }

   /**
     * Display a listing of the resource.
     */
    public function index($id_kategori_kuesioner)
    {
        $kategori= KategoriSoalKue::find($id_kategori_kuesioner);
        $pertanyaan = PertanyaanKuesioner::where('kategori_kuesioner_id',$id_kategori_kuesioner)->get();
        return view('kuesioner.pertanyaan.daftar_pertanyaan', compact('pertanyaan','kategori'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function buatPertanyaan(Request $request)
    {
        $kategoriKuesionerId = $request->input('kategoriKuesionerId');

        $pertanyaan = new PertanyaanKuesioner();
        $kategori = KategoriSoalKue::FindOrFail( $kategoriKuesionerId);
        return view('kuesioner.pertanyaan.action_pertanyaan', compact('pertanyaan','kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_kuesioner_id' => 'required',
            'pertanyaan' => 'required'
        ], [
            'kategori_kuesioner_id' => 'Kategori Kuesioner Tidak Ditemukan',
            'pertanyaan' => 'Pertanyaan Wajib Diinput'
        ]);

        PertanyaanKuesioner::create($validated);

        return redirect()->back()->with('success', 'Pertanyaan Berhasil Ditambahkan');
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
        $pertanyaan = PertanyaanKuesioner::find($id);
        $kategori = KategoriSoalKue::where('id_kategori_kuesioner', $pertanyaan->kategori_kuesioner_id)->first();
        return view('kuesioner.pertanyaan.action_pertanyaan', compact('kategori', 'pertanyaan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kategori_kuesioner_id' => 'required',
            'pertanyaan' => 'required'
        ], [
            'kategori_kuesioner_id' => 'Kategori Kuesioner Tidak Ditemukan',
            'pertanyaan' => 'Pertanyaan Wajib Diinput'
        ]);


        PertanyaanKuesioner::where('id_pertanyaan_kuesioner', $id)->update($validated);

        return redirect()->back()->with('success', 'Pertanyaan Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $data = PertanyaanKuesioner::findOrFail($id);

    $kategoriId = $data->getOriginal('id_kategori_kuesioner'); // Ambil sebelum delete

    $data->delete();



    return back()->with('success', 'Kategori Kuesioner berhasil dihapus.');
}
}
