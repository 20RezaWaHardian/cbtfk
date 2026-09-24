<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Session;

class LogAktifitas
{
    public static function catat($aktivitas)
    {
        $userLogin = auth()->user();

        if ($userLogin) {
            if ($userLogin->username != '20220017' && $userLogin->username != '200106202025061006' && !session()->has('loginsakti')) {

                $datauseraktif = User::where('username', $userLogin->username)->first();
                if ($datauseraktif) {
                    $inserlog = new LogAktivitas();
                    $inserlog->ip_address = request()->ip();
                    $inserlog->username = $datauseraktif->username;
                    $inserlog->nama_pelaku = $datauseraktif->name ?? $datauseraktif->username;
                    $inserlog->user_agent = request()->server('HTTP_USER_AGENT', 'Unknown');;
                    $inserlog->tanggal = now();
                    $inserlog->aktivitas = $aktivitas;
                    $inserlog->save();
                }
            }
        } 
    }
}
