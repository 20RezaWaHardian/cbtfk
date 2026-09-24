<?php

namespace App\Http\Controllers\BankSoal;

use App\DataTables\DaftarSoalButuhValidasiDataTable;
use App\Models\Soal;
use App\Models\Essay;
use App\Models\Pilgan;
use App\Imports\SoalImport2;
use App\Models\KategoriSoal;
use App\Models\SubKategoriSoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\DaftarSoalDataTable;
use App\Http\Requests\JenisSoalRequest;
use App\Http\Requests\SoalEssayRequest;
use App\Http\Requests\SoalPilganRequest;
use App\Http\Requests\SoalEssayWithKategoriRequest;
use App\Http\Requests\SoalPilganWithKategoriRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read bank-soal/soal');
        $this->middleware(function ($request, $next) {
            return DB::transaction(function () use ($request, $next) {
                $soal = Soal::lockForUpdate()->findOrFail($request->route('id'));
                if ($soal->status_validasi === 2) {
                    $this->authorize('update soal');
                    abort_unless($soal->milikPembuat($request->user()), 403);
                }
                $response = $next($request);
                if ($response instanceof \Illuminate\Http\RedirectResponse && $response->getSession()->has('error')) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['pertanyaan' => 'Perbaikan gagal disimpan. Periksa isian soal.']);
                }
                $soal->refresh();
                if ($soal->status_validasi === 2) {
                    $soal->diperbaiki_at = $soal->sidikIsi() !== $soal->sidik_penolakan ? now() : null;
                    $soal->save();
                }
                return $response;
            });
        })->only([
            'storeSoalPilgan', 'updateSoalPilgan', 'storeSoalEssay', 'updateSoalEssay',
            'storeSoalPilganWithoutKategori', 'updateSoalPilganWithoutKategori',
            'storeSoalEssayWithoutKategori', 'updateSoalEssayWithoutKategori',
        ]);
    }

    // Show soal berdasarkan kategori soal
    public function showSoalByKategori($id)
    {
        if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
            $kategori_soal = KategoriSoal::with(['sub_kategori_soal.soal.soal_pilgan', 'sub_kategori_soal.soal.soal_essay'])->findorfail($id);
            $soal = Soal::whereHas('sub_kategori_soal', function ($query) use ($id) {
                $query->where('id_kategori_soal', $id)->active();
            })->with('soal_pilgan', 'soal_essay', 'sub_kategori_soal')->get();
        } else {
            $kategori_soal = KategoriSoal::with(['sub_kategori_soal.soal.soal_pilgan', 'sub_kategori_soal.soal.soal_essay'])->findorfail($id);
            $soal = Soal::whereHas('sub_kategori_soal', function ($query) use ($id) {
                $query->where('id_kategori_soal', $id)->active();
            })->with('soal_pilgan', 'soal_essay', 'sub_kategori_soal')->get();
        }



        $sub_kategori_soal = $kategori_soal->sub_kategori_soal;
        $soal_tanpa_sub_kategori = $soal->whereNull('sub_kategori_soal_id');

        return view('bank-soal.soal.show-kategori', compact('kategori_soal', 'sub_kategori_soal', 'soal', 'soal_tanpa_sub_kategori'));
    }
    public function daftarSoalBelumDivalidasi(DaftarSoalButuhValidasiDataTable $dataTable)
    {
        $this->authorize('validasi-soal');
        if (Gate::allows('read bank-soal/soal')) {
            $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
            return $dataTable->render('bank-soal.soal.butuh-validasi', compact('kategori_soal'));
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    public function validasiSoal(Request $request)
    {
        $this->authorize('validasi-soal');
        if (is_string($request->komentar_validasi)) {
            $request->merge(['komentar_validasi' => trim($request->komentar_validasi)]);
        }
        $data = $request->validate([
            'id' => ['required', 'array', 'min:1', 'max:100'],
            'id.*' => ['required', 'integer', 'distinct', 'exists:soal,id_soal'],
            'status_validasi' => ['required', 'integer', 'in:1,2'],
            'komentar_validasi' => ['required_if:status_validasi,2', 'nullable', 'string', 'max:5000'],
        ]);
        DB::transaction(function () use ($data) {
            $rows = Soal::whereIn('id_soal', $data['id'])->orderBy('id_soal')->lockForUpdate()->get();
            abort_unless($rows->count() === count($data['id']), 409, 'Daftar soal berubah. Muat ulang halaman.');
            foreach ($rows as $soal) {
                if (auth()->user()->hasAnyRole(['koordinator-blok'])) {
                    $kelas = DB::table('sistembl_siakad-uin .pengelola_blok as a')
                        ->join('sistembl_siakad-uin .dosen as b', 'a.dosen_id', 'b.id_dosen')
                        ->where('a.id_dosen', auth()->user()->dosen?->id_dosen)
                        ->pluck('a.id_kelas');
                    abort_unless($soal->kategori_soal && $kelas->contains($soal->kategori_soal->id_kelas), 403);
                }
                abort_unless($soal->status_validasi === 0 && trim((string) $soal->pertanyaan) !== '', 409,
                    'Hanya soal belum validasi yang dapat diproses.');
                $soal->status_validasi = (int) $data['status_validasi'];
                $soal->komentar_validasi = $soal->status_validasi === 2 ? $data['komentar_validasi'] : null;
                $soal->sidik_penolakan = $soal->status_validasi === 2 ? $soal->sidikIsi() : null;
                $soal->diperbaiki_at = null;
                $soal->save();
            }
        });
        return response()->json(['status' => 200, 'pesan' => 'Keputusan validasi berhasil disimpan.']);
    }

    public function ajukanUlang($id, Request $request)
    {
        $this->authorize('update soal');
        DB::transaction(function () use ($id, $request) {
            $soal = Soal::lockForUpdate()->findOrFail($id);
            abort_unless($soal->milikPembuat($request->user()), 403);
            abort_unless($soal->dapatDiajukanUlang(), 409, 'Perbaiki dan simpan soal ditolak sebelum mengajukan ulang.');
            $soal->status_validasi = 0;
            $soal->diajukan_ulang_at = now();
            $soal->save();
        });
        return back()->with('success', 'Soal berhasil diajukan ulang.');
    }

    //Get Soal By Kategori Untuk URL
    public function getSoalByKategori($id)
    {
        if (auth()->user()->hasAnyRole(['developer', 'admin'])) {
            $data = Soal::whereHas('sub_kategori_soal', function ($query) use ($id) {
                $query->where('id_kategori_soal', $id)->active();
            })->with('soal_pilgan', 'soal_essay')->get();
        } else {
            $data = Soal::whereHas('sub_kategori_soal', function ($query) use ($id) {
                $query->where('id_kategori_soal', $id);
            })
                ->where('created_by', auth()->user()->id_asal)
                ->with('soal_pilgan', 'soal_essay')
                ->get();
        }

        return response()->json($data);
    }


    //Jenis Soal
    public function createJenisSoal($id)
    {
        $this->authorize('create soal');
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->findorfail($id);
        return view('bank-soal.soal.add-jenis-soal-action', ['soal' => new Soal(), 'kategori_soal' => $kategori_soal]);
    }

    public function storeJenisSoal($id, JenisSoalRequest $request)
    {
        $this->authorize('create soal');

        $data = $request->all();
        unset($data['kategori_soal_id']);
        if ($request->filled('sub_kategori_soal_id')) {
            $subKategori = SubKategoriSoal::where('id_kategori_soal', $id)->findOrFail($request->sub_kategori_soal_id);
            $data['sub_kategori_soal_id'] = $subKategori->id_sub_kategori_soal;
        }
        $data['created_by'] = auth()->user()->id_asal;
        Soal::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Create Data Success'
        ]);
    }

    public function excelJenisSoal($id)
    {
        $this->authorize('create soal');
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->findorfail($id);
        return view('bank-soal.soal.excel-jenis-soal-action', ['soal' => new Soal(), 'kategori_soal' => $kategori_soal]);
    }




    public function storeExcelJenisSoal(Request $request, $id)
    {
    
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'sub_kategori_soal_id' => 'required|exists:sub_kategori_soal,id_sub_kategori_soal',
        ]);

        $subKategori = SubKategoriSoal::where('id_kategori_soal', $id)
            ->where('is_delete', false)
            ->findOrFail($request->sub_kategori_soal_id);


        $file = $request->file('file');
        try {

            $collection = Excel::toCollection(null, $file);

            $rows = $collection[0]; // Get the first sheet
            $jumlahSoal = $rows->count(); // Count total rows

            $jumlahSoal -= 1; // Skip header row (if applicable)

            $totalPoin = 100;

            $poinPersoal = $totalPoin / $jumlahSoal;

            if ($jumlahSoal <= 0) {
                return redirect()->back()->with('error', 'Tidak ada soal yang ditemukan untuk diimpor.');
            }
            Excel::import(new SoalImport2($subKategori->id_sub_kategori_soal, $poinPersoal), $file);
            return redirect()->back()->with('success', 'Soal sedang diproses di background, Anda akan diberi tahu saat selesai.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor soal: ' . $e->getMessage());
        }
    }

    public function destroyJenisSoal($id)
    {
        $this->authorize('delete bank-soal/soal');
        $soal = Soal::findOrFail($id);
        $soal->delete();

        return redirect()->back()->with('success', 'Soal Berhasil Dihapus');
    }

    //Soal Pilihan Ganda

    public function createSoalPilgan($id)
    {
        $this->authorize('create soal');
        $soal = Soal::findorfail($id);
        $pilgan = new Pilgan();
        return view('bank-soal.soal.pilgan-form', compact('soal', 'pilgan'));
    }


    public function storeSoalPilgan($id, SoalPilganRequest $request)
    {
        $this->authorize('create soal');
        try {
            DB::beginTransaction();
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'id_pelaku' => auth()->user()->id
            ]);

            $kode = $request->input('kode');
            $teks = $request->input('teks');

            foreach ($kode as $index => $value) {
                Pilgan::create([
                    'soal_id' => $soal->id_soal,
                    'kode' => $value,
                    'teks' => $teks[$index],
                ]);
            }

            DB::commit();

            return redirect()->route('bank-soal.soal.showSoalByKategori', $soal->sub_kategori_soal?->id_kategori_soal)
                ->with('success', 'Soal Pilihan Ganda Berhasil Ditambahkan');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan soal pilihan ganda: ' . $e->getMessage());
        }
    }

    public function editSoalPilgan($id)
    {
        $this->authorize('update soal');
        $soal = Soal::findorfail($id);
        $pilgan = Pilgan::where('soal_id', $soal->id_soal)->get();
        return view('bank-soal.soal.pilgan-form', compact('soal', 'pilgan'));
    }

    public function updateSoalPilgan($id, SoalPilganRequest $request)
    {
        $this->authorize('update soal');

        try {
            DB::beginTransaction();
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'id_pelaku' => auth()->user()->id
            ]);

            $existingOptions = Pilgan::where('soal_id', $soal->id_soal)->get();

            $kode = $request->input('kode');
            $teks = $request->input('teks');
            $idsToDelete = [];

            foreach ($existingOptions as $existingOption) {
                if (!in_array($existingOption->kode, $kode)) {
                    $idsToDelete[] = $existingOption->id_pilgan;
                }
            }
            Pilgan::whereIn('id_pilgan', $idsToDelete)->delete();

            foreach ($kode as $index => $value) {
                $existingOption = $existingOptions->where('kode', $value)->first();
                if ($existingOption) {
                    $existingOption->update([
                        'teks' => $teks[$index],
                    ]);
                } else {
                    Pilgan::create([
                        'soal_id' => $soal->id_soal,
                        'kode' => $value,
                        'teks' => $teks[$index],
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('bank-soal.soal.showSoalByKategori', $soal->sub_kategori_soal?->id_kategori_soal)
                ->with('success', 'Soal Pilihan Ganda Berhasil Diperbarui');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui soal pilihan ganda: ' . $e->getMessage());
        }
    }


    public function destroySoalPilgan($id)
    {
        $this->authorize('delete soal');
        $soal = Soal::findOrFail($id);
        Pilgan::where('soal_id', $soal->id_soal)->delete();
        $soal->delete();
        return redirect()->back()->with('success', 'Soal Berhasil Dihapus');
    }


    //Soal Essay

    public function createSoalEssay($id)
    {
        $this->authorize('create soal');
        $soal = Soal::findorfail($id);
        return view('bank-soal.soal.essay-form', compact('soal'));
    }


    public function storeSoalEssay($id, SoalEssayRequest $request)
    {
        $this->authorize('create soal');
        try {
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'id_pelaku' => auth()->user()->id
            ]);

            $existingSoal = Essay::where('soal_id', $soal->id_soal)->first();

            if (!$existingSoal) {
                Essay::create([
                    'soal_id' => $soal->id_soal,
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            } else {
                $existingSoal->update([
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            }
            return redirect()->route('bank-soal.soal.showSoalByKategori', $soal->sub_kategori_soal?->id_kategori_soal)->with('success', 'Soal Essay Berhasil Ditambahkan');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan soal essay: ' . $e->getMessage());
        }
    }

    public function editSoalEssay($id)
    {
        $this->authorize('update soal');
        $soal = Soal::findorfail($id);
        return view('bank-soal.soal.essay-form', compact('soal'));
    }

    public function updateSoalEssay($id, SoalEssayRequest $request)
    {
        $this->authorize('update soal');
        try {
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'id_pelaku' => auth()->user()->id
            ]);

            $existingSoal = Essay::where('soal_id', $soal->id_soal)->first();

            if (!$existingSoal) {
                Essay::create([
                    'soal_id' => $soal->id_soal,
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            } else {
                $existingSoal->update([
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            }

            return redirect()->route('bank-soal.soal.showSoalByKategori', $soal->sub_kategori_soal?->id_kategori_soal)
                ->with('success', 'Soal Essay berhasil diperbarui.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui soal essay: ' . $e->getMessage());
        }
    }

    public function destroySoalEssay($id)
    {
        $this->authorize('delete soal');

        $soal = Soal::findOrFail($id);
        $soal->delete();
        return redirect()->back()->with('success', 'Soal Berhasil Dihapus');
    }


    //Daftar Soal
    public function daftarSoal(DaftarSoalDataTable $dataTable)
    {
        if (Gate::allows('read bank-soal/soal/daftar-soal')) {

            if (auth()->user()->hasAnyRole(['koordinator-blok'])) {
                $co_blok = DB::table('sistembl_siakad-uin .pengelola_blok as a')
                    ->join('sistembl_siakad-uin .dosen as b','a.dosen_id','b.id_dosen')
                    ->where('a.id_dosen', auth()->user()->dosen->id_dosen)
                    ->pluck('a.id_kelas')->toArray();
                $blm_validasi = Soal::with('kategori_soal')
                    ->whereHas('kategori_soal', function ($q) use ($co_blok) {
                        $q->whereIn('id_kelas', $co_blok);
                    })
                    ->where('status_validasi', 0)->whereNotNull('pertanyaan')->count();
            } else {
                $blm_validasi = Soal::with('kategori_soal')
                    ->where('status_validasi', 0)->whereNotNull('pertanyaan')->count();
            }
            $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
            return $dataTable->render('bank-soal.soal.daftar-soal', compact('blm_validasi', 'kategori_soal'));
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    public function createJenisSoalWithoutKategori()
    {
        $this->authorize('create soal');
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
        return view('bank-soal.soal.add-jenis-soal-without-kategori-action', ['soal' => new Soal(), 'kategori_soal' => $kategori_soal]);
    }

    public function storeJenisSoalWithoutKategori(JenisSoalRequest $request)
    {
        $this->authorize('create soal');

        $data = $request->all();
        $new_soal = Soal::create($data);

        $soal = Soal::findorfail($new_soal->id_soal);
        if ($soal->jenis_soal == 'pilgan') {
            $pilgan = new Pilgan();
            return response()->json([
                'status' => 'success',
                'message' => 'Create Data Success',
                'redirect' => route('bank-soal.soal.createSoalPilganWithoutKategori', ['id' => $soal->id_soal]),
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Create Data Success',
                'redirect' => route('bank-soal.soal.createSoalEssayWithoutKategori', ['id' => $soal->id_soal]),
            ]);
        }
    }

    //Pilgan2
    public function createSoalPilganWithoutKategori($id)
    {
        $this->authorize('create soal');
        $soal = Soal::findorfail($id);
        $pilgan = new Pilgan();
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
        return view('bank-soal.soal.pilgan-form2', compact('soal', 'pilgan', 'kategori_soal'));
    }
    public function storeSoalPilganWithoutKategori($id, SoalPilganWithKategoriRequest $request)
    {
        $this->authorize('create soal');
        try {
            DB::beginTransaction();
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'sub_kategori_soal_id' => $request->input('sub_kategori_soal_id'),
            ]);

            $kode = $request->input('kode');
            $teks = $request->input('teks');

            foreach ($kode as $index => $value) {
                Pilgan::create([
                    'soal_id' => $soal->id_soal,
                    'kode' => $value,
                    'teks' => $teks[$index],
                ]);
            }

            DB::commit();

            return redirect()->route('bank-soal.soal.daftarSoal')
                ->with('success', 'Soal Pilihan Ganda Berhasil Ditambahkan');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan soal pilihan ganda: ' . $e->getMessage());
        }
    }

    public function editSoalPilganWithoutKategori($id)
    {
        $this->authorize('update soal');
        $soal = Soal::findorfail($id);
        $pilgan = Pilgan::where('soal_id', $soal->id_soal)->get();
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
        return view('bank-soal.soal.pilgan-form2', compact('soal', 'pilgan', 'kategori_soal'));
    }

    public function updateSoalPilganWithoutKategori($id, SoalPilganWithKategoriRequest $request)
    {
        $this->authorize('update soal');

        try {
            DB::beginTransaction();
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'sub_kategori_soal_id' => $request->input('sub_kategori_soal_id'),
            ]);

            $existingOptions = Pilgan::where('soal_id', $soal->id_soal)->get();

            $kode = $request->input('kode');
            $teks = $request->input('teks');
            $idsToDelete = [];

            foreach ($existingOptions as $existingOption) {
                if (!in_array($existingOption->kode, $kode)) {
                    $idsToDelete[] = $existingOption->id_pilgan;
                }
            }
            Pilgan::whereIn('id_pilgan', $idsToDelete)->delete();

            foreach ($kode as $index => $value) {
                $existingOption = $existingOptions->where('kode', $value)->first();
                if ($existingOption) {
                    $existingOption->update([
                        'teks' => $teks[$index],
                    ]);
                } else {
                    Pilgan::create([
                        'soal_id' => $soal->id_soal,
                        'kode' => $value,
                        'teks' => $teks[$index],
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('bank-soal.soal.daftarSoal')
                ->with('success', 'Soal Pilihan Ganda Berhasil Diperbarui');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui soal pilihan ganda: ' . $e->getMessage());
        }
    }

    #Essay2
    public function createSoalEssayWithoutKategori($id)
    {
        $this->authorize('create soal');
        $soal = Soal::findorfail($id);
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
        return view('bank-soal.soal.essay-form2', compact('soal', 'kategori_soal'));
    }

    public function storeSoalEssayWithoutKategori($id, SoalEssayWithKategoriRequest $request)
    {
        $this->authorize('create soal');
        try {
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'sub_kategori_soal_id' => $request->input('sub_kategori_soal_id'),
            ]);

            $existingSoal = Essay::where('soal_id', $soal->id_soal)->first();

            if (!$existingSoal) {
                Essay::create([
                    'soal_id' => $soal->id_soal,
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            } else {
                $existingSoal->update([
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            }


            return redirect()->route('bank-soal.soal.daftarSoal')->with('success', 'Soal Essay Berhasil Ditambahkan');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan soal essay: ' . $e->getMessage());
        }
    }

    public function editSoalEssayWithoutKategori($id)
    {
        $this->authorize('update soal');
        $soal = Soal::findorfail($id);
        $kategori_soal = KategoriSoal::with('sub_kategori_soal')->get();
        return view('bank-soal.soal.essay-form2', compact('soal', 'kategori_soal'));
    }

    public function updateSoalEssayWithoutKategori($id, SoalEssayWithKategoriRequest $request)
    {
        $this->authorize('update soal');
        try {
            $soal = Soal::findOrFail($id);
            $soal->update([
                'poin' => $request->input('poin'),
                'pertanyaan' => $request->input('pertanyaan'),
                'kunci' => $request->input('kunci'),
                'sub_kategori_soal_id' => $request->input('sub_kategori_soal_id'),
            ]);

            $existingSoal = Essay::where('soal_id', $soal->id_soal)->first();

            if (!$existingSoal) {
                Essay::create([
                    'soal_id' => $soal->id_soal,
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            } else {
                $existingSoal->update([
                    'pertanyaan' => $request->input('pertanyaan'),
                    'kunci' => $request->input('kunci'),
                ]);
            }

            return redirect()->route('bank-soal.soal.daftarSoal')
                ->with('success', 'Soal Essay berhasil diperbarui.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'ID Soal Tidak Ditemukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui soal essay: ' . $e->getMessage());
        }
    }
}
