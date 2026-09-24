<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\DataTables\JenisOsceDataTable;
use App\Models\JenisOsce;
use App\Models\KomponenNilaiOsce;
use App\Helpers\LogAktifitas;

class JenisOsceController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read data-master/jenis-osce');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(JenisOsceDataTable $dataTable)
    {
        if (Gate::allows('read data-master/jenis-osce')) {
            return $dataTable->render('data-master.jenis-osce.index');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jenis_osce = new JenisOsce();

        return view('data-master.jenis-osce.action',compact('jenis_osce'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_jenis_osce' => 'required'
        ]);

        $data = JenisOsce::create([
            'nama_jenis_osce' => $request->nama_jenis_osce
        ]);

        LogAktifitas::catat("Menambah Jenis OSCE dengan id ".$data->id_jenis_osce);
        return redirect()->back()->with('success','Berhasil Menambah Jenis OSCE');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jenis_osce = JenisOsce::find(decrypt($id));

        return view('data-master.jenis-osce.action',compact('jenis_osce'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_jenis_osce' => 'required',
            'status' => 'required'
        ]);

        JenisOsce::where('id_jenis_osce',$id)->update($validated);
        LogAktifitas::catat("Mengubah Jenis OSCE dengan id " . $id);

        return redirect()->back()->with('success','Berhasil Mengubah Jenis OSCE');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete data-master/jenis-osce');
        $data = JenisOsce::find(decrypt($id));
        $nama_jenis_osce = $data->nama_jenis_osce;
        $data->update([
            'isDeleted' => 1
        ]);
        LogAktifitas::catat("Menghapus Jenis OSCE " . $nama_jenis_osce . " Pada Jenis OSCE.");

        return response()->json([
            'status' => 'success',
            'message' => 'Delete Data Success'
        ]);
    }
}
