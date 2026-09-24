<?php

namespace Vizir\KeycloakWebGuard\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Vizir\KeycloakWebGuard\Exceptions\KeycloakCallbackException;
use Vizir\KeycloakWebGuard\Facades\KeycloakWeb;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Redirect to login
     *
     * @return view
     */
    public function login()
    {
        $url = KeycloakWeb::getLoginUrl();
        KeycloakWeb::saveState();

        return redirect($url);
    }

    /**
     * Redirect to logout
     *
     * @return view
     */
    public function logout(Request $request)
    {
        $url = KeycloakWeb::getLogoutUrl();
        KeycloakWeb::forgetToken();
        return redirect($url);
        // Auth::guard('web')->logout();

        // // 2️⃣ Hapus session Laravel
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        // // 3️⃣ Logout Keycloak
        // KeycloakWeb::forgetToken();
        // $logoutUrl = config('keycloak-web.base_url')
        // . '/realms/' . config('keycloak-web.realm')
        // . '/protocol/openid-connect/logout'
        // . '?client_id=' . config('keycloak-web.client_id')
        // . '&post_logout_redirect_uri=' . urlencode(url('/'));
        // return redirect($logoutUrl);
    }

    /**
     * Redirect to register
     *
     * @return view
     */
    public function register()
    {
        $url = KeycloakWeb::getRegisterUrl();
        return redirect($url);
    }

    /**
     * Keycloak callback page
     *
     * @throws KeycloakCallbackException
     *
     * @return view
     */
    public function callback(Request $request)
    {
        // Check for errors from Keycloak
        if (! empty($request->input('error'))) {
            $error = $request->input('error_description');
            $error = ($error) ?: $request->input('error');

            throw new KeycloakCallbackException($error);
        }

        // Check given state to mitigate CSRF attack
        $state = $request->input('state');
        //disabled
        /*if (empty($state) || ! KeycloakWeb::validateState($state)) {
            KeycloakWeb::forgetState();

            throw new KeycloakCallbackException('Invalid state');
        }
        */
        // Change code for token
        $code = $request->input('code');
        if (! empty($code)) {
            $token = KeycloakWeb::getAccessToken($code);

            if (Auth::validate($token)) {
                $url = config('keycloak-web.redirect_url', '/home');
                return redirect()->intended($url);
            }
        }

        return redirect(route('keycloak.login'));
        
        // if (! empty($request->input('error'))) {
        //     $error = $request->input('error_description') ?: $request->input('error');
        //     throw new KeycloakCallbackException($error);
        // }

        // $code = $request->input('code');

        // if (! empty($code)) {
        //     $token = KeycloakWeb::getAccessToken($code);
        //     // Ambil user dari Keycloak
        //     $kcUser = KeycloakWeb::getUserProfile($token);
        //     // dd($kcUser);

        //     if (Session::has('kamuflase')) {
        //         $username = Session::get('kamuflase');
        //     } else {
        //         $username = $kcUser["preferred_username"];
        //     }

        //     $checkSimpeg = DB::table('kepeg.users as a')->where('a.username', $username)
        //         ->join('kepeg.pegawai as b', 'a.id', '=', 'b.user_id')->first();

        //     $checkSiakad = DB::table('siakad.users as a')
        //         ->join('siakad.mhs_pt as b', 'b.no_mhs', 'a.username')
        //         ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', '=', 'b.id_mahasiswa')
        //         ->where('a.username', $username)->where('status', 1)->where('usertype', 'mahasiswa')->first();

            
        //     if (!$checkSimpeg && !$checkSiakad) {
        //         return redirect('/login')
        //             ->withErrors(['auth' => 'User tidak terdaftar']);
        //     }

        //     if ($checkSimpeg) {
        //         $userUnja = true;
        //         $idAsal =  $checkSimpeg->id_pegawai;
        //         $username =   $checkSimpeg->username;
        //         $name = $checkSimpeg->nama_pegawai;
        //         $userType = 'pegawai'; // Pegawai Unja
        //     } elseif ($checkSiakad) {
        //         $userUnja = true;
        //         $idAsal =  $checkSiakad->id_mhs_pt;
        //         $username =   $checkSiakad->username;
        //         $name = $checkSiakad->nama_mahasiswa;
        //         $userType = 'mahasiswa'; // Pegawai Unja
        //     }



        //     if ($userUnja) {
        //         $checkLocalUser = User::where('username', $username)->first();
        //         if (!$checkLocalUser) {
        //             $checkLocalUser = User::create([
        //                 'id_asal' => $idAsal,
        //                 'username' => strtolower($username),
        //                 'name' => $name,
        //                 'usertype' => $userType,

        //             ]);
        //         } else {
        //             if($checkSimpeg)
        //             {
        //                 if($checkSimpeg->status_kerja_id == 2){
        //                     $existingData = DB::table('model_has_roles')
        //                         ->where('model_id', $checkLocalUser->id)
        //                         ->where('role_id', 9)
        //                         ->exists();

        //                     if (!$existingData) {
        //                         DB::table('model_has_roles')->insert([
        //                             'role_id' => 9,
        //                             'model_type' => 'App\Models\User',
        //                             'model_id' =>  $checkLocalUser->id,
        //                         ]);
        //                     }
        //                 }elseif ($userType == 'pegawai') {
        //                     $existingData = DB::table('model_has_roles')
        //                         ->where('model_id', $checkLocalUser->id)
        //                         ->where('role_id', 6)
        //                         ->exists();

        //                     if (!$existingData) {
        //                         DB::table('model_has_roles')->insert([
        //                             'role_id' => 6,
        //                             'model_type' => 'App\Models\User',
        //                             'model_id' =>  $checkLocalUser->id,
        //                         ]);
        //                     }
        //                 } 
        //             }elseif ($userType == 'mahasiswa') {
        //                     $existingData = DB::table('model_has_roles')
        //                         ->where('model_id', $checkLocalUser->id)
        //                         ->where('role_id', 4)
        //                         ->exists();

        //                     if (!$existingData) {
        //                         DB::table('model_has_roles')->insert([
        //                             'role_id' => 4,
        //                             'model_type' => 'App\Models\User',
        //                             'model_id' =>  $checkLocalUser->id,
        //                         ]);
        //                     }
        //                 }
                    
        //         }
        //         if ($checkLocalUser->name == null) {
        //             $checkLocalUser->name = $name;
        //             $checkLocalUser->last_login_via = 'keycloak';
        //             $checkLocalUser->save();
        //         }
        //     }

        //     // Sinkron ke tabel users
        //     // $user = \App\Models\User::updateOrCreate(
        //     //     ['email' => $kcUser['email']],
        //     //     [
        //     //         'name' => $kcUser['name'] ?? $kcUser['preferred_username'],
        //     //         'keycloak_id' => $kcUser['sub'],
        //     //     ]
        //     // );

        //     // 🔥 INI YANG KURANG
        //     Auth::guard('web')->login($checkLocalUser);

        //     return redirect('/dashboard');
        // }
    }
}
