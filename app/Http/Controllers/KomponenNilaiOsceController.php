<?php

namespace App\Http\Controllers;

use App\Models\KomponenNilaiOsce;
use App\Models\InstrumenNilaiOsce;
use App\Models\JenisOsce;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use App\DataTables\KomponenNilaiOsceDataTable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KomponenNilaiOsceController extends Controller
{
    private function authorizeManage()
    {
        abort_unless(Gate::allows('create data-master/komponen-nilai-osce') || Gate::allows('update data-master/komponen-nilai-osce'), 403);
    }

    private function component($id)
    {
        return KomponenNilaiOsce::with('jenis')->where('isDeleted', false)->findOrFail(decrypt($id));
    }

    public function index(KomponenNilaiOsceDataTable $dataTable, $id_jenis_osce)
    {
        abort_unless(Gate::allows('read data-master/komponen-nilai-osce') || Gate::allows('create data-master/komponen-nilai-osce') || Gate::allows('update data-master/komponen-nilai-osce'), 403);
        $jenis = JenisOsce::findOrFail(decrypt($id_jenis_osce));
        return $dataTable->with('id_jenis_osce', $id_jenis_osce)->render('data-master.komponen-nilai.index', compact('id_jenis_osce', 'jenis'));
    }

    public function create($id_jenis_osce)
    {
        Gate::authorize('create data-master/komponen-nilai-osce');
        $jenis = JenisOsce::findOrFail(decrypt($id_jenis_osce));
        $komponen = new KomponenNilaiOsce();
        return view('data-master.komponen-nilai.action', compact('komponen', 'id_jenis_osce', 'jenis'));
    }

    private function componentData(Request $request)
    {
        return $request->validate([
            'nama_komponen' => 'required|string|max:255',
            'detail_komponen' => 'required|string',
            'bobot_nilai' => 'required|integer|between:0,3',
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create data-master/komponen-nilai-osce');
        $data = $this->componentData($request);
        $request->validate(['id_jenis_osce' => 'required|string']);
        $jenis = JenisOsce::findOrFail(decrypt($request->id_jenis_osce));
        $data['id_jenis_osce'] = $jenis->id_jenis_osce;
        $data['isDeleted'] = false;
        $komponen = KomponenNilaiOsce::create($data);
        LogAktifitas::catat('Menambah Komponen Nilai OSCE dengan id '.$komponen->id_komponen_nilai_osce);
        return redirect()->route('data-master.komponen-nilai-osce.instrumen', encrypt($komponen->id_komponen_nilai_osce))
            ->with('success', 'Komponen tersimpan. Lanjutkan dengan menambahkan instrumen penilaian.');
    }

    public function editForm($id, $id_jenis_osce)
    {
        Gate::authorize('update data-master/komponen-nilai-osce');
        $komponen = $this->component($id);
        abort_unless((string) $komponen->id_jenis_osce === (string) decrypt($id_jenis_osce), 404);
        $jenis = $komponen->jenis;
        return view('data-master.komponen-nilai.action', compact('komponen', 'id_jenis_osce', 'jenis'));
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('update data-master/komponen-nilai-osce');
        $komponen = $this->component($id);
        $komponen->update($this->componentData($request));
        LogAktifitas::catat('Mengubah Komponen Nilai OSCE dengan id '.$komponen->id_komponen_nilai_osce);
        return redirect()->route('data-master.komponen-nilai-osce.index', encrypt($komponen->id_jenis_osce))->with('success', 'Komponen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Gate::authorize('delete data-master/komponen-nilai-osce');
        $komponen = $this->component($id);
        $this->ensureUnused($komponen);
        $komponen->update(['isDeleted' => true]);
        LogAktifitas::catat('Menghapus Komponen Nilai OSCE '.$komponen->nama_komponen);
        return response()->json(['message' => 'Komponen berhasil dihapus.']);
    }

    public function instrumen($id)
    {
        $this->authorizeManage();
        $komponen = $this->component($id);
        $komponen->load(['instrumenNilai' => fn ($query) => $query->orderBy('nilai')->orderBy('id')]);
        return view('data-master.komponen-nilai.instrumen', compact('komponen'));
    }

    public function formInstrumen($id_komponen_nilai_osce)
    {
        $this->authorizeManage();
        $komponen = $this->component($id_komponen_nilai_osce);
        $instrumen = new InstrumenNilaiOsce();
        return view('data-master.komponen-nilai.formInstrumen', compact('instrumen', 'komponen'));
    }

    private function instrumentData(Request $request, $komponen, $instrumen = null)
    {
        return $request->validate([
            'nilai' => ['required', 'integer', 'between:0,3', Rule::unique('instrumen_nilai_osce', 'nilai')->where('id_komponen_nilai_osce', $komponen->id_komponen_nilai_osce)->ignore($instrumen?->id)],
            'keterangan' => 'required|string',
        ], ['nilai.unique' => 'Nilai ini sudah memiliki instrumen. Edit instrumen yang tersedia.', 'nilai.required' => 'Pilih nilai.', 'keterangan.required' => 'Isi keterangan penilaian.']);
    }

    public function storeInstrumen(Request $request)
    {
        $this->authorizeManage();
        $request->validate(['id_komponen_nilai_osce' => 'required|string']);
        $komponen = $this->component($request->id_komponen_nilai_osce);
        $this->ensureUnused($komponen);
        $komponen->instrumenNilai()->create($this->instrumentData($request, $komponen));
        return response()->json(['message' => 'Instrumen berhasil ditambahkan.']);
    }

    public function editInstrumen($id)
    {
        $this->authorizeManage();
        $instrumen = InstrumenNilaiOsce::findOrFail(decrypt($id));
        $komponen = $this->component(encrypt($instrumen->id_komponen_nilai_osce));
        return view('data-master.komponen-nilai.formInstrumen', compact('instrumen', 'komponen'));
    }

    public function updateInstrumen(Request $request, $id)
    {
        $this->authorizeManage();
        $instrumen = InstrumenNilaiOsce::findOrFail(decrypt($id));
        $komponen = $this->component(encrypt($instrumen->id_komponen_nilai_osce));
        $this->ensureUnused($komponen);
        // Parent identity comes from the stored instrument, never from submitted input.
        $instrumen->update($this->instrumentData($request, $komponen, $instrumen));
        return response()->json(['message' => 'Instrumen berhasil diperbarui.']);
    }

    public function destroyInstrumen($id)
    {
        Gate::authorize('delete data-master/komponen-nilai-osce');
        $instrumen = InstrumenNilaiOsce::findOrFail(decrypt($id));
        $komponen = $this->component(encrypt($instrumen->id_komponen_nilai_osce));
        $this->ensureUnused($komponen);
        $instrumen->delete();
        return response()->json(['message' => 'Instrumen berhasil dihapus.']);
    }

    private function ensureUnused($komponen)
    {
        // Same-name components can use this component's instruments as a template.
        $ids = KomponenNilaiOsce::where('nama_komponen', $komponen->nama_komponen)->pluck('id_komponen_nilai_osce');
        if (DB::table('nilai_peserta_station_osce')->whereIn('id_komponen_nilai_osce', $ids)->exists()) {
            throw ValidationException::withMessages(['instrumen' => 'Komponen atau template bernama sama sudah digunakan dalam penilaian. Instrumen tidak dapat diubah atau dihapus.']);
        }
    }
}
