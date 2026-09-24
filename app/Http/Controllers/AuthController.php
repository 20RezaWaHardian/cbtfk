<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Helpers\LogAktifitas;
use App\Helpers\MyHelpers;

class AuthController extends Controller
{

    public function halamanLoginEksternal()
    {
        return view('auth.login');
    }
    public function loginPost(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $username = $request->input('username');
        $password = $request->input('password');
        $user = User::where('username', $username)->orWhere('email', $username)->first();
        $valid = $user && $user->password && Hash::check($password, $user->password);

        if (!$valid) {
            $source = DB::table('sistembl_siakad-uin .users as a')
                ->leftJoin('sistembl_siakad-uin .mahasiswa as b', 'a.id', '=', 'b.user_id')
                ->leftJoin('sistembl_siakad-uin .dosen as c', 'a.id', '=', 'c.user_id')
                ->where('a.username', $user ? $user->username : $username)
                ->select('a.*', 'b.id_mahasiswa', 'c.id_dosen')
                ->first();
            $valid = $source && $source->password && Hash::check($password, $source->password);
            if ($valid && !$user) {
                $type = $source->id_mahasiswa ? 'mahasiswa' : ($source->id_dosen ? 'dosen' : 'user');
                $user = DB::transaction(function () use ($source, $type, $password) {
                    $newUser = User::create([
                        'id_asal' => $source->id,
                        'username' => $source->username,
                        'name' => $source->name,
                        'usertype' => $type,
                        'password' => Hash::make($password),
                    ]);
                    $newUser->assignRole($type);
                    return $newUser;
                });
            }
        }

        if (!$valid || !$user) {
            return back()->with('error', 'Email atau Password Anda Salah!');
        }

        Auth::guard('web')->login($user, false);
        $request->session()->regenerate();
        $request->session()->forget(['kamuflase', 'impersonator_id', 'impersonator_userlogin']);
        $request->session()->put('userlogin', [
            'username' => $user->username,
            'nama_pelaku' => $user->name ?? $user->username,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'from' => 'manual',
        ]);
        LogAktifitas::catat('Login ke CBT-FKIK');
        return redirect()->route('dashboard');
    }

    public function logoutManual(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
