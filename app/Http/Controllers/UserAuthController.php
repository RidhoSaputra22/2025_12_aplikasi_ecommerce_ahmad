<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    //

    public function login()
    {
        return view('user.auth.login');
    }

    public function register()
    {
        return view('user.auth.register');
    }
}
