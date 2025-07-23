<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenilaianKaryawan;
use App\Models\User;
use App\Models\DataKaryawan;
use Illuminate\Support\Facades\Hash;

class PenilaianKaryawanController extends Controller
{
    public function index()
    {
        $PenilaianKaryawans = DataKaryawan::all();
        return view('master.penilaian_karyawan.index', compact('PenilaianKaryawans'));
    }

    public function create()
    {
        return view('master.penilaian_karyawan.create');
    }


    public function show($id)
    {
        $PenilaianKaryawans = User::findOrFail($id);
        return view('master.penilaian_karyawan.show', compact('PenilaianKaryawans'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'kriteria' => 'required|string|max:100',
                'bobot' => 'required',
            ]);

            // Create user from request
            $KriteriaBobot = PenilaianKaryawan::create([
                'kriteria' => $request->kriteria,
                'bobot' => $request->bobot,
            ]);

            return redirect()->route('kriteria_bobot.index')->with('success', 'Data Kriteria dan Bobot created successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }

    }


    public function edit($id)
    {
        $dataGuru = DataKaryawan::findOrFail($id);
        return view('master.kelola_karyawan.edit', compact('dataGuru'));
    }

    public function update(Request $request, $id)
    {
        $kriteriaDanBobot = PenilaianKaryawan::findOrFail($id);
        $kriteriaDanBobot->update($request->all());

        return redirect()->route('penilaian_karyawan.index')->with('success', 'Data Guru updated successfully.');
    }

    public function destroy($id)
    {
        $dataGuru = PenilaianKaryawan::findOrFail($id);
        $dataGuru->delete();

        return redirect()->route('penilaian_karyawan.index')->with('success', 'Data Guru deleted successfully.');
    }

    public function getNilaiKaryawan($id){
         $PenilaianKaryawans = PenilaianKaryawan::all();

        return response()->json($PenilaianKaryawans);

    }
}
