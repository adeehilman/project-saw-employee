<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KriteriaBobot;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PenilaianBobotKaryawanController extends Controller
{
    /**
     **********************************************************
     * kriteria_penilaian adalah folder didalam reseource/view
     * kriteria_bobot ada nama route
     **********************************************************
     */
    public function index()
    {
        $KriteriaBobots = KriteriaBobot::all();
        return view('master.kriteria_penilaian.index', compact('KriteriaBobots'));
    }

    public function create()
    {
        return view('master.kriteria_penilaian.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'kriteria' => 'required|string|max:100',
                'bobot' => 'required',
            ]);

            // Create user from request
            $KriteriaBobot = KriteriaBobot::create([
                'kriteria' => $request->kriteria,
                'bobot' => $request->bobot,
            ]);

            return redirect()->route('kriteria_bobot.index')->with('success', 'Data Kriteria dan Bobot created successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }

    }

    public function show($id)
    {
        $dataGuru = KriteriaBobot::findOrFail($id);
        return view('master.kriteria_penilaian.show', compact('dataGuru'));
    }

    public function edit($id)
    {
        $dataGuru = KriteriaBobot::findOrFail($id);
        return view('master.kriteria_penilaian.edit', compact('dataGuru'));
    }

    public function update(Request $request, $id)
    {
        $kriteriaDanBobot = KriteriaBobot::findOrFail($id);
        $kriteriaDanBobot->update($request->all());

        return redirect()->route('kriteria_bobot.index')->with('success', 'Data Guru updated successfully.');
    }

    public function destroy($id)
    {
        $dataGuru = KriteriaBobot::findOrFail($id);
        $dataGuru->delete();

        return redirect()->route('kriteria_bobot.index')->with('success', 'Data Guru deleted successfully.');
    }
}
