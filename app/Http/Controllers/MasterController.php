<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function master_profil()
    {
        return view('master.master_profil');
    }
    //tools
    public function tools_impor_data_master()
    {
        return view('master.tools_impor_data_master');
    }
    public function tools_ekspor_data_master()
    {
        return view('master.tools_ekspor_data_master');
    }
    public function tools_backup_database()
    {
        return view('master.tools_backup_database');
    }
    public function tools_data_login()
    {
        return view('master.tools_data_login');
    }
}
