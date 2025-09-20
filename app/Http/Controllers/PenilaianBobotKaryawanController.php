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
                'bobot' => 'required|numeric|min:1|max:100',
            ]);

            // Create criteria with pending status
            $KriteriaBobot = KriteriaBobot::create([
                'kriteria' => $request->kriteria,
                'bobot' => $request->bobot,
                'status' => 'Menunggu',
                'createby' => auth()->id(),
                'submitted_at' => now()
            ]);

            return redirect()->route('kriteria_bobot.index')
                ->with('success', 'Data Kriteria dan Bobot berhasil dibuat dan menunggu persetujuan.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function show($id)
    {
        $dataKriteria = KriteriaBobot::findOrFail($id);
        return view('master.kriteria_penilaian.show', compact('dataKriteria'));
    }

    public function edit($id)
    {
        $dataKriteria = KriteriaBobot::findOrFail($id);
        return view('master.kriteria_penilaian.edit', compact('dataKriteria'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kriteria' => 'required|string|max:100',
            'bobot' => 'required|numeric|min:1|max:100',
        ]);

        $kriteriaDanBobot = KriteriaBobot::findOrFail($id);

        // Only allow updates if not approved
        if ($kriteriaDanBobot->isApproved()) {
            return redirect()->back()->with('error', 'Kriteria yang sudah disetujui tidak dapat diubah.');
        }

        $oldStatus = $kriteriaDanBobot->status;

        $kriteriaDanBobot->update([
            'kriteria' => $request->kriteria,
            'bobot' => $request->bobot,
            'status' => 'Menunggu', // Reset to pending after update
            'submitted_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null
        ]);

        return redirect()->route('kriteria_bobot.index')
            ->with('success', 'Data Kriteria dan Bobot berhasil diperbarui dan menunggu persetujuan ulang.');
    }

    public function destroy($id)
    {
        $kriteria = KriteriaBobot::findOrFail($id);

        // Only allow deletion if not approved
        if ($kriteria->isApproved()) {
            return redirect()->back()->with('error', 'Kriteria yang sudah disetujui tidak dapat dihapus.');
        }

        $kriteria->delete();

        return redirect()->route('kriteria_bobot.index')
            ->with('success', 'Data Kriteria dan Bobot berhasil dihapus.');
    }
}
