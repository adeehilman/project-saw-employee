<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataKaryawan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KelolaKaryawanController extends Controller
{
    public function index()
    {
        $dataKaryawans = DataKaryawan::all();
        return view('master.kelola_karyawan.index', compact('dataKaryawans'));
    }

    public function create()
    {
        return view('master.kelola_karyawan.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_karyawan' => 'required|string|max:100',
                'jeniskelamin' => 'required|in:Laki-laki,Perempuan',
                'tanggal_masuk' => 'required',
                'email' => 'required|email|max:100|email|unique:users',
                'jabatan' => 'required|max:100',
            ]);


             // Generate username dari nama
            $namaParts = explode(' ', $request->nama_karyawan);
            if (count($namaParts) >= 2) {
                $username = strtolower($namaParts[0] . '.' . $namaParts[1]);
            } else {
                $nama = strtolower($namaParts[0]);
                $username = $nama . '.' . $nama[0] . substr($nama, -1); // contoh: afridol.al
            }

            // Create user from request
            $user = User::create([
                'name' => $request->nama_karyawan,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make('Passs0wrd'),
                'role' => 'Karyawan', // bisa diganti sesuai kebutuhan
            ]);


            DataKaryawan::create([
                'nama_karyawan' => $request->nama_karyawan,
                'jenis_kelamin' => $request->jeniskelamin,
                'tanggal_masuk' => $request->tanggal_masuk,
                'jabatan' => $request->jabatan,
                'user_id' => $user->id,
            ]);

            return redirect()->route('kelola-karyawan.index')->with('success', 'Data Karyawan created successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }

    }

    public function show($id)
    {
        $dataKaryawan = DataKaryawan::findOrFail($id);
        return view('master.kelola_karyawan.show', compact('dataKaryawan'));
    }

    public function edit($id)
    {
        $dataKaryawan = DataKaryawan::findOrFail($id);
        return view('master.kelola_karyawan.edit', compact('dataKaryawan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_karyawan' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'jabatan' => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'aktif' => 'required|in:true,false'
        ]);

        $dataKaryawan = DataKaryawan::findOrFail($id);
        $dataKaryawan->update([
            'nama_karyawan' => $request->nama_karyawan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'jabatan' => $request->jabatan,
            'tanggal_masuk' => $request->tanggal_masuk,
            'is_active' => $request->aktif,
        ]);

        return redirect()->route('kelola-karyawan.index')->with('success', 'Data Karyawan updated successfully.');
    }

    public function destroy($id)
    {
        $dataKaryawan = DataKaryawan::findOrFail($id);
        $dataKaryawan->delete();

        return redirect()->route('kelola-karyawan.index')->with('success', 'Data Guru deleted successfully.');
    }
}
