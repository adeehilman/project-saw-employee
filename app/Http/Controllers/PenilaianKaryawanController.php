<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataKaryawan;
use App\Models\KriteriaBobot;
use App\Models\PenilaianKaryawan;
use App\Services\SAWCalculationService;
use App\Exports\EmployeeAssessmentExport;
use App\Exports\EmployeeAssessmentCSVExport;
use App\Exports\EmployeeAssessmentPDFExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PenilaianKaryawanController extends Controller
{
    /**
     * Display list of employees for scoring with SAW calculations
     */
    public function index(Request $request)
    {
        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $employees = DataKaryawan::where('is_active', true)
            ->with(['penilaian' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('waktu_penilaian', [$startDate, $endDate]);
            }])
            ->get();

        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        // Calculate SAW scores and rankings
        $sawService = app(SAWCalculationService::class);
        $sawResults = $sawService->calculateSAWScores($startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);

        return view('penilaian_karyawan.index', compact(
            'employees',
            'approvedCriteria',
            'startDate',
            'endDate',
            'sawResults',
            'sawValidation'
        ));
    }

    /**
     * Show scoring form for specific employee
     */
    public function create($employeeId, Request $request)
    {
        $employee = DataKaryawan::findOrFail($employeeId);

        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get approved criteria
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')
            ->orderBy('kriteria')
            ->get();

        if ($approvedCriteria->isEmpty()) {
            return redirect()->route('penilaian_karyawan.index')
                ->with('error', 'Tidak ada kriteria yang disetujui. Silakan tunggu persetujuan kriteria terlebih dahulu.');
        }

        // Get existing assessments for this employee and date range
        $existingAssessments = PenilaianKaryawan::where('id_karyawan', $employeeId)
            ->whereBetween('waktu_penilaian', [$startDate, $endDate])
            ->with('kriteriaBobot')
            ->get()
            ->keyBy('id_kriteria_bobot');

        return view('penilaian_karyawan.create', compact('employee', 'approvedCriteria', 'startDate', 'endDate', 'existingAssessments'));
    }

    /**
     * Store employee scores
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:data_karyawan,id_karyawan',
            'waktu_penilaian' => 'required|date',
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0|max:100',
            'catatan' => 'array',
            'catatan.*' => 'nullable|string|max:500'
        ]);

        try {
            $employeeId = $request->id_karyawan;
            $period = $request->waktu_penilaian;
            $scores = $request->scores;
            $catatan = $request->catatan ?? [];

            foreach ($scores as $kriteriaId => $nilai) {
                // Check if assessment already exists
                $existingAssessment = PenilaianKaryawan::where('id_karyawan', $employeeId)
                    ->where('id_kriteria_bobot', $kriteriaId)
                    ->where('waktu_penilaian', $period)
                    ->first();

                if ($existingAssessment) {
                    // Update existing assessment
                    $existingAssessment->update([
                        'nilai' => $nilai,
                        'catatan' => $catatan[$kriteriaId] ?? null,
                        'dinilai_oleh' => Auth::id()
                    ]);
                } else {
                    // Create new assessment
                    PenilaianKaryawan::create([
                        'id_penilaian' => PenilaianKaryawan::generateId(),
                        'id_karyawan' => $employeeId,
                        'id_kriteria_bobot' => $kriteriaId,
                        'waktu_penilaian' => $period,
                        'nilai' => $nilai,
                        'catatan' => $catatan[$kriteriaId] ?? null,
                        'dinilai_oleh' => Auth::id()
                    ]);
                }
            }

            // Calculate date range for redirect (use the assessment date as both start and end for the month)
            $assessmentDate = \Carbon\Carbon::parse($period);
            $startDate = $assessmentDate;
            $endDate = $assessmentDate;

            return redirect()->route('penilaian_karyawan.index', ['start_date' => $startDate, 'end_date' => $endDate])
                ->with('success', 'Penilaian karyawan berhasil disimpan.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show employee assessment details with SAW calculations
     */
    public function show($employeeId, Request $request)
    {
        $employee = DataKaryawan::findOrFail($employeeId);

        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $assessments = PenilaianKaryawan::where('id_karyawan', $employeeId)
            ->whereBetween('waktu_penilaian', [$startDate, $endDate])
            ->with(['kriteriaBobot', 'penilai'])
            ->get();

        // SAW calculations
        $sawService = app(SAWCalculationService::class);
        $sawDetails = $sawService->getEmployeeSAWDetails($employeeId, $startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);
        $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);

        return view('penilaian_karyawan.show', compact(
            'employee',
            'assessments',
            'startDate',
            'endDate',
            'sawDetails',
            'sawValidation',
            'criteriaStats'
        ));
    }

    /**
     * Edit employee scores
     */
    public function edit($employeeId, Request $request)
    {
        return $this->create($employeeId, $request);
    }

    /**
     * Delete specific assessment
     */
    public function destroy($assessmentId)
    {
        $assessment = PenilaianKaryawan::findOrFail($assessmentId);
        $period = $assessment->waktu_penilaian;

        $assessment->delete();

        return redirect()->route('penilaian_karyawan.index', ['period' => $period])
            ->with('success', 'Penilaian berhasil dihapus.');
    }

    /**
     * Get assessment summary for dashboard
     */
    public function getSummary(Request $request)
    {
        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $totalEmployees = DataKaryawan::where('is_active', true)->count();
        $assessedEmployees = PenilaianKaryawan::whereBetween('waktu_penilaian', [$startDate, $endDate])
            ->distinct('id_karyawan')
            ->count('id_karyawan');

        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->count();
        $totalAssessments = PenilaianKaryawan::whereBetween('waktu_penilaian', [$startDate, $endDate])->count();

        $completedEmployees = 0;
        if ($approvedCriteria > 0) {
            $employees = DataKaryawan::where('is_active', true)->get();
            foreach ($employees as $employee) {
                // Check if employee has assessments for all approved criteria
                $employeeAssessments = PenilaianKaryawan::where('id_karyawan', $employee->id_karyawan)
                    ->whereBetween('waktu_penilaian', [$startDate, $endDate])
                    ->count();

                if ($employeeAssessments >= $approvedCriteria) {
                    $completedEmployees++;
                }
            }
        }

        return response()->json([
            'total_employees' => $totalEmployees,
            'assessed_employees' => $assessedEmployees,
            'completed_employees' => $completedEmployees,
            'approved_criteria' => $approvedCriteria,
            'total_assessments' => $totalAssessments,
            'completion_rate' => $totalEmployees > 0 ? round(($completedEmployees / $totalEmployees) * 100, 1) : 0
        ]);
    }

    /**
     * Export assessment data to Excel/CSV/PDF using Laravel Excel
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $format = $request->get('format', 'excel'); // excel, csv, pdf

        // Get SAW results for comprehensive export
        $sawService = app(SAWCalculationService::class);
        $sawResults = $sawService->calculateSAWScores($startDate, $endDate);
        $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        $filename = 'hasil_penilaian_' . \Carbon\Carbon::parse($startDate)->format('Y_m_d') . '_to_' . \Carbon\Carbon::parse($endDate)->format('Y_m_d');

        switch ($format) {
            case 'csv':
                $csvExport = new EmployeeAssessmentCSVExport($sawResults, $approvedCriteria, $period);
                return Excel::download($csvExport, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            case 'pdf':
                $pdfExport = new EmployeeAssessmentPDFExport($sawResults, $approvedCriteria, $period, $criteriaStats);
                return Excel::download($pdfExport, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            case 'excel':
            default:
                $excelExport = new EmployeeAssessmentExport($sawResults, $approvedCriteria, $period, $criteriaStats);

                $response = Excel::download($excelExport, $filename . '.xlsx');

                // Add explicit headers to prevent browser misinterpretation
                $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.xlsx"');
                $response->headers->set('Cache-Control', 'max-age=0');

                return $response;
        }
    }





    /**
     * Bulk delete assessments for a date range
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'employee_ids' => 'array',
            'employee_ids.*' => 'exists:data_karyawan,id_karyawan'
        ]);

        $query = PenilaianKaryawan::whereBetween('waktu_penilaian', [$request->start_date, $request->end_date]);

        if ($request->has('employee_ids') && !empty($request->employee_ids)) {
            $query->whereIn('id_karyawan', $request->employee_ids);
        }

        $deletedCount = $query->delete();

        return redirect()->route('penilaian_karyawan.index', ['start_date' => $request->start_date, 'end_date' => $request->end_date])
            ->with('success', "Berhasil menghapus {$deletedCount} penilaian.");
    }

    /**
     * Show SAW ranking for all employees
     */
    public function ranking(Request $request)
    {
        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $sawService = app(SAWCalculationService::class);
        $sawResults = $sawService->calculateSAWScores($startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);
        $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        return view('penilaian_karyawan.ranking', compact(
            'sawResults',
            'sawValidation',
            'criteriaStats',
            'approvedCriteria',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get SAW calculation details API
     */
    public function getSAWDetails(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $employeeId = $request->get('employee_id');

        $sawService = app(SAWCalculationService::class);

        if ($employeeId) {
            $details = $sawService->getEmployeeSAWDetails($employeeId, $startDate, $endDate);
            return response()->json($details);
        } else {
            $allResults = $sawService->calculateSAWScores($startDate, $endDate);
            return response()->json($allResults);
        }
    }

    /**
     * Get criteria statistics for SAW normalization
     */
    public function getCriteriaStats(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $sawService = app(SAWCalculationService::class);
        $stats = $sawService->getCriteriaStatistics($startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Results view for Pemimpin Perusahaan (read-only)
     */
    public function results(Request $request)
    {
        // Check if user has permission to view results
        if (!in_array(auth()->user()->role, ['Admin', 'Pemimpin Perusahaan'])) {
            abort(403, 'Akses ditolak. Hanya Admin dan Pemimpin Perusahaan yang dapat melihat hasil penilaian.');
        }

        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $employees = DataKaryawan::where('is_active', true)
            ->with(['penilaian' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('waktu_penilaian', [$startDate, $endDate]);
            }])
            ->get();

        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        // Calculate SAW scores and rankings
        $sawService = app(SAWCalculationService::class);
        $sawResults = $sawService->calculateSAWScores($startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);

        return view('results.index', compact(
            'employees',
            'approvedCriteria',
            'startDate',
            'endDate',
            'sawResults',
            'sawValidation'
        ));
    }

    /**
     * Results ranking for Pemimpin Perusahaan
     */
    public function resultsRanking(Request $request)
    {
        // Check permission
        if (!in_array(auth()->user()->role, ['Admin', 'Pemimpin Perusahaan'])) {
            abort(403, 'Akses ditolak.');
        }

        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $sawService = app(SAWCalculationService::class);
        $sawResults = $sawService->calculateSAWScores($startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);
        $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        return view('results.ranking', compact(
            'sawResults',
            'sawValidation',
            'criteriaStats',
            'approvedCriteria',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export results for Pemimpin Perusahaan
     */
    public function resultsExport(Request $request)
    {
        // Check permission
        if (!in_array(auth()->user()->role, ['Admin', 'Pemimpin Perusahaan'])) {
            abort(403, 'Akses ditolak.');
        }

        // Use the same export logic as admin
        return $this->export($request);
    }

    /**
     * Results summary for Pemimpin Perusahaan
     */
    public function getResultsSummary(Request $request)
    {
        // Check permission
        if (!in_array(auth()->user()->role, ['Admin', 'Pemimpin Perusahaan'])) {
            abort(403, 'Akses ditolak.');
        }

        // Use the same summary logic as admin
        return $this->getSummary($request);
    }

    /**
     * Show employee results for Pemimpin Perusahaan
     */
    public function resultsShow($employeeId, Request $request)
    {
        // Check permission
        if (!in_array(auth()->user()->role, ['Admin', 'Pemimpin Perusahaan'])) {
            abort(403, 'Akses ditolak.');
        }

        $employee = DataKaryawan::findOrFail($employeeId);

        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $assessments = PenilaianKaryawan::where('id_karyawan', $employeeId)
            ->whereBetween('waktu_penilaian', [$startDate, $endDate])
            ->with(['kriteriaBobot', 'penilai'])
            ->get();

        // SAW calculations
        $sawService = app(SAWCalculationService::class);
        $sawDetails = $sawService->getEmployeeSAWDetails($employeeId, $startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);
        $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);

        return view('results.show', compact(
            'employee',
            'assessments',
            'startDate',
            'endDate',
            'sawDetails',
            'sawValidation',
            'criteriaStats'
        ));
    }
}
