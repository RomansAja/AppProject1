<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginproses(Request $request)
    {
        if (Auth::guard('karyawan')->attempt(['kdpeg' => $request->kdpeg, 'password' => $request->password])) {
            return redirect('/dashboard');
        } else {
            return back()->with(['warning' => 'Username atau Password Salah']);
        }
    }

    public function LogoutProses()
    {
        if (Auth::guard('karyawan')->check()) {
            Auth::guard('karyawan')->logout();
            return redirect('/');
        }
    }

    public function proseslogoutadmin()
    {

        if (Auth::guard('user')->check()) {
            Auth::guard('user')->logout();
            return redirect('/panel');
        }
    }

    public function prosesloginadmin(Request $request)
    {
        if (Auth::guard('user')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect('/panel/dashboardadmin');
        } else {
            return redirect('/panel')->with(['warning' => 'Email atau Password Salah']);
        }
    }
}