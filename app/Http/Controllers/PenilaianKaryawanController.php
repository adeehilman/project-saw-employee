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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
            // Ensure start date is not after end date
            if ($startDate > $endDate) {
                $temp = $startDate;
                $startDate = $endDate;
                $endDate = $temp;
            }
        } else {
            // Set default values for display purposes only
            $startDate = null;
            $endDate = null;
        }

        // Load employees with conditional date filtering
        $employees = DataKaryawan::where('is_active', true)
            ->with(['penilaian' => function($query) use ($startDate, $endDate, $hasValidDates) {
                $query->where('nilai', '>', 0);  // Only load assessments with nilai > 0
                if ($hasValidDates) {
                    $query->whereBetween('waktu_penilaian', [$startDate, $endDate]);
                }
                // If no valid dates, load all penilaian data
            }])
            ->get();

        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        // Calculate SAW scores and rankings
        $sawService = app(SAWCalculationService::class);
        $sawResults = $hasValidDates ? $sawService->calculateSAWScores($startDate, $endDate) : $sawService->calculateSAWScores(null, null);
        $sawValidation = $hasValidDates ? $sawService->canPerformSAW($startDate, $endDate) : $sawService->canPerformSAW(null, null);

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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

        // Get approved criteria
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')
            ->orderBy('kriteria')
            ->get();

        if ($approvedCriteria->isEmpty()) {
            return redirect()->route('penilaian_karyawan.index')
                ->with('error', 'Tidak ada kriteria yang disetujui. Silakan tunggu persetujuan kriteria terlebih dahulu.');
        }

        // Get existing assessments for this employee and date range
        $existingAssessmentsQuery = PenilaianKaryawan::where('id_karyawan', $employeeId)
            ->where('nilai', '>', 0);  // Only get assessments with nilai > 0

        if ($hasValidDates) {
            $existingAssessmentsQuery->whereBetween('waktu_penilaian', [$startDate, $endDate]);
        }

        $existingAssessments = $existingAssessmentsQuery
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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

        $assessmentsQuery = PenilaianKaryawan::where('id_karyawan', $employeeId)
            ->where('nilai', '>', 0);  // Only show assessments with nilai > 0

        if ($hasValidDates) {
            $assessmentsQuery->whereBetween('waktu_penilaian', [$startDate, $endDate]);
        }

        $assessments = $assessmentsQuery
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
        $employee = DataKaryawan::findOrFail($employeeId);

        // Handle date range parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

        // Get approved criteria
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')
            ->orderBy('kriteria')
            ->get();

        if ($approvedCriteria->isEmpty()) {
            return redirect()->route('penilaian_karyawan.index')
                ->with('error', 'Tidak ada kriteria yang disetujui. Silakan tunggu persetujuan kriteria terlebih dahulu.');
        }

        // Get existing assessments for this employee and date range
        $existingAssessmentsQuery = PenilaianKaryawan::where('id_karyawan', $employeeId)
            ->where('nilai', '>', 0);  // Only get assessments with nilai > 0

        if ($hasValidDates) {
            $existingAssessmentsQuery->whereBetween('waktu_penilaian', [$startDate, $endDate]);
        }

        $existingAssessments = $existingAssessmentsQuery
            ->with('kriteriaBobot')
            ->get()
            ->keyBy('id_kriteria_bobot');

        // Return the edit view instead of the create view
        return view('penilaian_karyawan.edit', compact('employee', 'approvedCriteria', 'startDate', 'endDate', 'existingAssessments'));
    }

    /**
     * Update employee scores
     */
    public function update(Request $request, $employeeId)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:data_karyawan,id_karyawan',
            'scores' => 'required|array',
            'scores.*' => 'required|numeric|min:0|max:100',
            'catatan' => 'array',
            'catatan.*' => 'nullable|string|max:500'
        ]);

        try {
            $employeeId = $request->id_karyawan;
            $scores = $request->scores;
            $catatan = $request->catatan ?? [];

            foreach ($scores as $kriteriaId => $nilai) {
                // Check if assessment already exists
                $existingAssessment = PenilaianKaryawan::where('id_karyawan', $employeeId)
                    ->where('id_kriteria_bobot', $kriteriaId)
                    ->first();

                if (COUNT($existingAssessment) > 0) {
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
                        'waktu_penilaian' => now(),
                        'nilai' => $nilai,
                        'catatan' => $catatan[$kriteriaId] ?? null,
                        'dinilai_oleh' => Auth::id()
                    ]);
                }
            }

            // Calculate date range for redirect (use the assessment date as both start and end for the month)

            return redirect()->route('penilaian_karyawan.index')
                ->with('success', 'Penilaian karyawan berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

        // Check if dates are provided and valid
        $hasValidDates = $startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate);

        $totalEmployees = DataKaryawan::where('is_active', true)->count();

        if ($hasValidDates) {
            $assessedEmployees = PenilaianKaryawan::whereBetween('waktu_penilaian', [$startDate, $endDate])
                ->where('nilai', '>', 0)  // Only count assessments with nilai > 0
                ->distinct('id_karyawan')
                ->count('id_karyawan');
            $totalAssessments = PenilaianKaryawan::whereBetween('waktu_penilaian', [$startDate, $endDate])
                ->where('nilai', '>', 0)  // Only count assessments with nilai > 0
                ->count();
        } else {
            $assessedEmployees = PenilaianKaryawan::where('nilai', '>', 0)  // Only count assessments with nilai > 0
                ->distinct('id_karyawan')
                ->count('id_karyawan');
            $totalAssessments = PenilaianKaryawan::where('nilai', '>', 0)  // Only count assessments with nilai > 0
                ->count();
        }

        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->count();

        $completedEmployees = 0;
        if ($approvedCriteria > 0) {
            $employees = DataKaryawan::where('is_active', true)->get();
            foreach ($employees as $employee) {
                // Check if employee has assessments for all approved criteria
                $employeeAssessmentsQuery = PenilaianKaryawan::where('id_karyawan', $employee->id_karyawan)
                    ->where('nilai', '>', 0);  // Only count assessments with nilai > 0

                if ($hasValidDates) {
                    $employeeAssessmentsQuery->whereBetween('waktu_penilaian', [$startDate, $endDate]);
                }

                $employeeAssessments = $employeeAssessmentsQuery->count();

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

        // Check if dates are provided and valid
        $hasValidDates = $startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate);

        try {
            // Get SAW results for comprehensive export
            $sawService = app(SAWCalculationService::class);
            $sawResults = $sawService->calculateSAWScores($startDate, $endDate);

            $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);
            $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

            // Create filename
            if ($hasValidDates) {
                $startFormatted = \Carbon\Carbon::parse($startDate)->format('Y_m_d');
                $endFormatted = \Carbon\Carbon::parse($endDate)->format('Y_m_d');
                $filename = 'hasil_penilaian_' . $startFormatted . '_to_' . $endFormatted;
            } else {
                $filename = 'hasil_penilaian_all_data_' . now()->format('Y_m_d');
            }

            // Set period for export classes
            $period = $hasValidDates ? "$startDate to $endDate" : "All Data";

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

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete assessments for a date range
     */
    public function bulkDelete(Request $request)
    {
        if ($request->has('employee_ids') && !empty($request->employee_ids)) {
            $query = PenilaianKaryawan::whereIn('id_karyawan', $request->employee_ids);
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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

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

        // Check if dates are provided and valid
        $hasValidDates = $startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate);

        if (!$hasValidDates) {
            $startDate = null;
            $endDate = null;
        }

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

        // Check if dates are provided and valid
        $hasValidDates = $startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate);

        if (!$hasValidDates) {
            $startDate = null;
            $endDate = null;
        }

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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

        $employees = DataKaryawan::where('is_active', true)
            ->with(['penilaian' => function($query) use ($startDate, $endDate, $hasValidDates) {
                if ($hasValidDates) {
                    $query->whereBetween('waktu_penilaian', [$startDate, $endDate]);
                }
                // If no valid dates, load all penilaian data
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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

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
     * Show employee's own assessment results
     * UC10-TC01: Karyawan melihat hasil sendiri
     */
    public function myResults(Request $request)
    {
        // Get the employee data for the logged-in user
        $employee = DataKaryawan::where('user_id', auth()->id())
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            abort(404, 'Data karyawan tidak ditemukan atau tidak aktif.');
        }

        // Date range handling
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $hasValidDates = $startDate && $endDate;

        // Get employee's assessments
        $assessmentsQuery = PenilaianKaryawan::where('id_karyawan', $employee->id_karyawan);

        if ($hasValidDates) {
            $assessmentsQuery->whereBetween('waktu_penilaian', [$startDate, $endDate]);
        }

        $assessments = $assessmentsQuery
            ->with(['kriteriaBobot', 'penilai'])
            ->get();

        // Get approved criteria
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->get();

        // SAW calculations
        $sawService = app(SAWCalculationService::class);
        $sawDetails = $sawService->getEmployeeSAWDetails($employee->id_karyawan, $startDate, $endDate);
        $sawValidation = $sawService->canPerformSAW($startDate, $endDate);
        $criteriaStats = $sawService->getCriteriaStatistics($startDate, $endDate);

        // Get all SAW results to determine ranking
        $allSawResults = $sawService->calculateSAWScores($startDate, $endDate);

        return view('employee.my_results', compact(
            'employee',
            'assessments',
            'approvedCriteria',
            'startDate',
            'endDate',
            'sawDetails',
            'sawValidation',
            'criteriaStats',
            'allSawResults'
        ));
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

        // Check if dates are provided and valid
        $hasValidDates = false;
        if ($startDate && $endDate && $this->isValidDate($startDate) && $this->isValidDate($endDate)) {
            $hasValidDates = true;
        } else {
            $startDate = null;
            $endDate = null;
        }

        $assessmentsQuery = PenilaianKaryawan::where('id_karyawan', $employeeId);

        if ($hasValidDates) {
            $assessmentsQuery->whereBetween('waktu_penilaian', [$startDate, $endDate]);
        }

        $assessments = $assessmentsQuery
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

    /**
     * Helper method to validate date format
     */
    private function isValidDate($date)
    {
        if (!$date) {
            return false;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
