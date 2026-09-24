<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\User;
use App\Models\KepegUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\DataTables\UserSiabDataTable;

class UserController extends Controller
{
    public function index(UserSiabDataTable $dataTable)
    {
        return $dataTable->render('konfigurasi.users.index');
    }

    public function loginAs(Request $request, $id)
    {
        abort_if($request->session()->has('impersonator_id'), 403, 'Keluar dari Login As terlebih dahulu');
        Gate::authorize('update konfigurasi/users');
        $target = User::where('username', $id)->firstOrFail();
        abort_if($target->id == Auth::id(), 422, 'Pilih akun lain');

        $request->session()->put('impersonator_id', Auth::id());
        $request->session()->put('impersonator_userlogin', $request->session()->get('userlogin', []));
        Auth::guard('web')->login($target, false);
        $request->session()->regenerate();
        $request->session()->put('kamuflase', $target->username);
        $request->session()->put('userlogin', [
            'username' => $target->username,
            'nama_pelaku' => $target->name ?? $target->username,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'from' => 'login-as',
        ]);

        return $request->expectsJson()
            ? response()->json(['redirect' => route('dashboard')])
            : redirect()->route('dashboard')->with('success', 'Anda Berhasil Login');
    }

    public function logoutAs(Request $request)
    {
        abort_unless($request->session()->has('impersonator_id'), 403);
        $original = User::findOrFail($request->session()->get('impersonator_id'));
        Auth::guard('web')->login($original, false);
        $request->session()->put('userlogin', array_merge(
            (array) $request->session()->get('impersonator_userlogin', []),
            ['username' => $original->username, 'nama_pelaku' => $original->name ?? $original->username]
        ));
        $request->session()->forget(['kamuflase', 'impersonator_id', 'impersonator_userlogin']);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Kembali Ke Akun Utama');
    }

    public function edit($username)
    {

        $user = User::where('username', $username)->first();
        if ($user) {
            $user = User::Find($user->id);
            $getRole = Role::get();
            return view('konfigurasi.users.user-action', compact(['user', 'getRole']));
        } else {
            $user_kepeg = KepegUser::where('username', $username)->with('kepeg_pegawai', 'kepeg_pegawai.biodata')->first();
            $user = User::create([
                'id_asal' => $user_kepeg->kepeg_pegawai->id_pegawai ?? '',
                'username' => $user_kepeg->username,
                'email' => $user_kepeg->kepeg_pegawai->biodata->email ?? '',
                'usertype' => 'pegawai',
            ]);
            $getRole = Role::get();
            return view('konfigurasi.users.user-action', compact(['user', 'getRole']));
        }
    }

    public function update(Request $request, $id)
    {
        if (Gate::allows('update konfigurasi/users')) {
            $user = User::findOrFail($id);
            $user->syncRoles($request->usertypes);
            return redirect()->back()->with('success', 'Update Data Success');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Gate::allows('delete konfigurasi/users')) {
            $user = User::find($id);
            $user->delete();
            return redirect()->back()->with('success', 'Delete Data Success');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }
}
