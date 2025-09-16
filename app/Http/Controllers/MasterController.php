<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function master_profil()
    {
        return view('master.master_profil');
    }
}