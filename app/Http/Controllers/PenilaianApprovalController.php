<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KriteriaBobot;
use Illuminate\Support\Facades\Auth;

class PenilaianApprovalController extends Controller
{
    /**
     * Display approval dashboard for leadership
     */
    public function index()
    {
        $pendingApprovals = KriteriaBobot::getPendingApprovals();
        
        $stats = [
            'Menunggu' => KriteriaBobot::where('status', 'Menunggu')->count(),
            'Disetujui' => KriteriaBobot::where('status', 'Disetujui')->count(),
            'Ditolak' => KriteriaBobot::where('status', 'Ditolak')->count(),
        ];

        return view('approval.dashboard', compact('pendingApprovals', 'stats'));
    }

    /**
     * Show approval details for a specific criteria
     */
    public function show($id)
    {
        $criteria = KriteriaBobot::with(['creator', 'approver'])
            ->findOrFail($id);
        
        return view('approval.show', compact('criteria'));
    }

    /**
     * Approve a criteria
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $criteria = KriteriaBobot::findOrFail($id);
        
        if (!$criteria->isPending()) {
            return redirect()->back()->with('error', 'Kriteria ini tidak dalam status menunggu persetujuan.');
        }

        $criteria->approve(Auth::id(), $request->reason);

        return redirect()->route('approval.index')
            ->with('success', 'Kriteria berhasil disetujui.');
    }

    /**
     * Reject a criteria
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $criteria = KriteriaBobot::findOrFail($id);
        
        if (!$criteria->isPending()) {
            return redirect()->back()->with('error', 'Kriteria ini tidak dalam status menunggu persetujuan.');
        }

        $criteria->reject(Auth::id(), $request->reason);

        return redirect()->route('approval.index')
            ->with('success', 'Kriteria berhasil ditolak.');
    }

    /**
     * Bulk approve multiple criteria
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'criteria_ids' => 'required|array',
            'criteria_ids.*' => 'exists:kriteria_bobot,id_kriteria',
            'reason' => 'nullable|string|max:500'
        ]);

        $approvedCount = 0;
        $errors = [];

        foreach ($request->criteria_ids as $id) {
            $criteria = KriteriaBobot::find($id);
            
            if ($criteria && $criteria->isPending()) {
                $criteria->approve(Auth::id(), $request->reason);
                $approvedCount++;
            } else {
                $errors[] = "Kriteria ID {$id} tidak dapat disetujui.";
            }
        }

        $message = "{$approvedCount} kriteria berhasil disetujui.";
        if (!empty($errors)) {
            $message .= " Namun ada beberapa error: " . implode(', ', $errors);
        }

        return redirect()->route('approval.index')->with('success', $message);
    }

    /**
     * Bulk reject multiple criteria
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'criteria_ids' => 'required|array',
            'criteria_ids.*' => 'exists:kriteria_bobot,id_kriteria',
            'reason' => 'required|string|max:500'
        ]);

        $rejectedCount = 0;
        $errors = [];

        foreach ($request->criteria_ids as $id) {
            $criteria = KriteriaBobot::find($id);
            
            if ($criteria && $criteria->isPending()) {
                $criteria->reject(Auth::id(), $request->reason);
                $rejectedCount++;
            } else {
                $errors[] = "Kriteria ID {$id} tidak dapat ditolak.";
            }
        }
        
        $message = "{$rejectedCount} kriteria berhasil ditolak.";
        if (!empty($errors)) {
            $message .= " Namun ada beberapa error: " . implode(', ', $errors);
        }

        return redirect()->route('approval.index')->with('success', $message);
    }

    /**
     * Show approval history
     */
    public function history()
    {
        $approvedCriteria = KriteriaBobot::getApproved();
        $rejectedCriteria = KriteriaBobot::getRejected();

        return view('approval.history', compact('approvedCriteria', 'rejectedCriteria'));
    }

    /**
     * Get approval statistics for dashboard
     */
    public function getStats()
    {
        $stats = [
            'Menunggu' => KriteriaBobot::where('status', 'Menunggu')->count(),
            'Disetujui' => KriteriaBobot::where('status', 'Disetujui')->count(),
            'Ditolak' => KriteriaBobot::where('status', 'Ditolak')->count(),
            'total' => KriteriaBobot::count(),
        ];

        return response()->json([
            'stats' => $stats,
        ]);
    }
}
