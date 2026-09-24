<?php

namespace Vizir\KeycloakWebGuard\Auth\Guard;

use Log;
use Auth;
use App\Models\User;
use App\Helpers\MyHelpers;
use Illuminate\Http\Request;
use App\Helpers\LogAktifitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Vizir\KeycloakWebGuard\Facades\KeycloakWeb;
use Vizir\KeycloakWebGuard\Models\KeycloakUser;
use Vizir\KeycloakWebGuard\Auth\KeycloakAccessToken;
use Vizir\KeycloakWebGuard\Exceptions\KeycloakCallbackException;

class KeycloakWebGuard implements Guard
{
    /**
     * @var null|Authenticatable|KeycloakUser
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        #Log::info(__METHOD__ . print_r($this->user(), true));
        return (bool) $this->user();
    }

    public function hasUser()
    {
        return (bool) $this->user();
    }

    #public function hasUser()
    #{
    #    $x  = ! is_null($this->user);
    #    Log::info(__METHOD__ . " : " .$x );
    #    return ! is_null($this->user);
    #}
    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (empty($this->user)) {
            $this->authenticate();
        }

        return $this->user;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(?Authenticatable $user)
    {
        $this->user = $user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        $user = $this->user();
        return $user->id ?? null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     *
     * @throws BadMethodCallException
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials['access_token']) || empty($credentials['id_token'])) {
            return false;
        }

        /**
         * Store the section
         */
        $credentials['refresh_token'] = $credentials['refresh_token'] ?? '';
        KeycloakWeb::saveToken($credentials);

        return $this->authenticate();
    }

    /**
     * Try to authenticate the user
     *
     * @throws KeycloakCallbackException
     * @return boolean
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = KeycloakWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = KeycloakWeb::getUserProfile($credentials);

        if (empty($user)) {
            KeycloakWeb::forgetToken();
            #//ilham: return false saja tidak usah exception
            return false;
            if (Config::get('app.debug', false)) {
                throw new KeycloakCallbackException('User cannot be authenticated.');
            }

            return false;
        }
        #========
        $token = new KeycloakAccessToken($credentials);
        $token = $token->parseAccessToken();

        // dd($token);
        //tambahan
        if (Session::has('kamuflase')) {
            $username = Session::get('kamuflase');
        } else {
            $username = $token["preferred_username"];
        }

        // if (session()->has('loginas')) {
        //     $username = session()->get('loginas');
        // } else {
        //     $username = $token["preferred_username"];
        // }

        $checkSimpeg = DB::table('kepeg.users as a')->where('a.username', $username)
            ->join('kepeg.pegawai as b', 'a.id', '=', 'b.user_id')->first();

        $checkSiakad = DB::table('siakad.users as a')
            ->join('siakad.mhs_pt as b', 'b.no_mhs', 'a.username')
            ->join('siakad.mahasiswa as c', 'c.id_mahasiswa', '=', 'b.id_mahasiswa')
            ->where('a.username', $username)->where('status', 1)->where('usertype', 'mahasiswa')->first();

        // if ($checkSiakad) {
        //     return false;
        // }
        
        if (!$checkSimpeg && !$checkSiakad) {
            return false;
        }
        if ($checkSimpeg) {
            $userUnja = true;
            $idAsal =  $checkSimpeg->id_pegawai;
            $username =   $checkSimpeg->username;
            $name = $checkSimpeg->nama_pegawai;
            $userType = 'pegawai'; // Pegawai Unja
        } elseif ($checkSiakad) {
            $userUnja = true;
            $idAsal =  $checkSiakad->id_mhs_pt;
            $username =   $checkSiakad->username;
            $name = $checkSiakad->nama_mahasiswa;
            $userType = 'mahasiswa'; // Pegawai Unja
        }



        if ($userUnja) {
            $checkLocalUser = User::where('username', $username)->first();
            if (!$checkLocalUser) {
                $checkLocalUser = User::create([
                    'id_asal' => $idAsal,
                    'username' => strtolower($username),
                    'name' => $name,
                    'usertype' => $userType,

                ]);
            } else {
                if($checkSimpeg)
                {
                    if($checkSimpeg->status_kerja_id == 2){
                        $existingData = DB::table('model_has_roles')
                            ->where('model_id', $checkLocalUser->id)
                            ->where('role_id', 9)
                            ->exists();

                        if (!$existingData) {
                            DB::table('model_has_roles')->insert([
                                'role_id' => 9,
                                'model_type' => 'App\Models\User',
                                'model_id' =>  $checkLocalUser->id,
                            ]);
                        }
                    }elseif ($userType == 'pegawai') {
                        $existingData = DB::table('model_has_roles')
                            ->where('model_id', $checkLocalUser->id)
                            ->where('role_id', 6)
                            ->exists();

                        if (!$existingData) {
                            DB::table('model_has_roles')->insert([
                                'role_id' => 6,
                                'model_type' => 'App\Models\User',
                                'model_id' =>  $checkLocalUser->id,
                            ]);
                        }
                    } 
                }elseif ($userType == 'mahasiswa') {
                        $existingData = DB::table('model_has_roles')
                            ->where('model_id', $checkLocalUser->id)
                            ->where('role_id', 4)
                            ->exists();

                        if (!$existingData) {
                            DB::table('model_has_roles')->insert([
                                'role_id' => 4,
                                'model_type' => 'App\Models\User',
                                'model_id' =>  $checkLocalUser->id,
                            ]);
                        }
                    }
                
            }
            if ($checkLocalUser->name == null) {
                $checkLocalUser->name = $name;
                $checkLocalUser->save();
            }
            $this->createLoginData($checkLocalUser);
        }



        $this->setUser($checkLocalUser);
        return true;
    }

    private function createLoginData($user)
    {
        $token = $user->createToken($user->username);
        $data = [];
        $data['id_asal'] = $user->id_asal;
        $data['name'] = $user->name;
        $data['username'] = $user->username;
        $data['usertype'] = $user->usertype;
        $data['token'] = $token->plainTextToken;
        return $data;
    }

    /**
     * Check user is authenticated and return his resource roles
     *
     * @param string $resource Default is empty: point to client_id
     *
     * @return array
     */
    public function hasRole($roles, $resource = '')
    {
        if (empty($resource)) {
            $resource = Config::get('keycloak-web.client_id');
        }

        if (!$this->check()) {
            return false;
        }

        $token = KeycloakWeb::retrieveToken();

        if (empty($token) || empty($token['access_token'])) {
            return false;
        }

        $token = new KeycloakAccessToken($token);
        $token = $token->parseAccessToken();

        $resourceRoles = $token['resource_access'] ?? [];
        $resourceRoles = $resourceRoles[$resource] ?? [];
        $resourceRoles = $resourceRoles['roles'] ?? [];

        return empty(array_diff((array) $roles, $resourceRoles));
    }
}
