<?php

namespace App\Services;

use App\Models\DataKaryawan;
use App\Models\KriteriaBobot;
use App\Models\PenilaianKaryawan;
use Illuminate\Support\Collection;

class SAWCalculationService
{
    /**
     * Calculate SAW scores for all employees in a given period
     */
    public function calculateSAWScores($period)
    {
        // Step 1: Get approved criteria with weights
        $criteria = KriteriaBobot::where('status', 'Disetujui')->get();
        
        if ($criteria->isEmpty()) {
            return collect();
        }

        // Step 2: Get all employees with assessments for the period
        $employees = DataKaryawan::where('is_active', true)
            ->with(['penilaian' => function($query) use ($period) {
                $query->where('periode_penilaian', $period)
                      ->with('kriteriaBobot');
            }])
            ->get();

        // Filter employees who have at least one assessment
        $assessedEmployees = $employees->filter(function($employee) {
            return $employee->penilaian->count() > 0;
        });

        if ($assessedEmployees->isEmpty()) {
            return collect();
        }

        // Step 3: Create decision matrix
        $decisionMatrix = $this->createDecisionMatrix($assessedEmployees, $criteria, $period);
        
        // Step 4: Normalize the matrix
        $normalizedMatrix = $this->normalizeMatrix($decisionMatrix, $criteria);
        
        // Step 5: Calculate SAW scores
        $sawScores = $this->calculateFinalScores($normalizedMatrix, $criteria);
        
        // Step 6: Rank employees
        $rankedEmployees = $this->rankEmployees($sawScores);

        return $rankedEmployees;
    }

    /**
     * Create decision matrix from employee assessments
     */
    private function createDecisionMatrix($employees, $criteria, $period)
    {
        $matrix = [];

        foreach ($employees as $employee) {
            $employeeScores = [];
            
            foreach ($criteria as $criterion) {
                $assessment = $employee->penilaian->firstWhere('id_kriteria_bobot', $criterion->id_kriteria);
                $employeeScores[$criterion->id_kriteria] = $assessment ? $assessment->nilai : 0;
            }
            
            $matrix[$employee->id_karyawan] = [
                'employee' => $employee,
                'scores' => $employeeScores,
                'raw_total' => array_sum($employeeScores)
            ];
        }

        return $matrix;
    }

    /**
     * Normalize matrix using max value for each criterion (benefit criteria)
     */
    private function normalizeMatrix($decisionMatrix, $criteria)
    {
        $normalizedMatrix = [];
        
        // Find max value for each criterion
        $maxValues = [];
        foreach ($criteria as $criterion) {
            $maxValues[$criterion->id_kriteria] = 0;
            foreach ($decisionMatrix as $employeeData) {
                $value = $employeeData['scores'][$criterion->id_kriteria];
                if ($value > $maxValues[$criterion->id_kriteria]) {
                    $maxValues[$criterion->id_kriteria] = $value;
                }
            }
        }

        // Normalize each value
        foreach ($decisionMatrix as $employeeId => $employeeData) {
            $normalizedScores = [];
            
            foreach ($criteria as $criterion) {
                $rawValue = $employeeData['scores'][$criterion->id_kriteria];
                $maxValue = $maxValues[$criterion->id_kriteria];
                
                // Normalization formula: r_ij = x_ij / max(x_ij)
                $normalizedScores[$criterion->id_kriteria] = $maxValue > 0 ? $rawValue / $maxValue : 0;
            }
            
            $normalizedMatrix[$employeeId] = [
                'employee' => $employeeData['employee'],
                'raw_scores' => $employeeData['scores'],
                'normalized_scores' => $normalizedScores,
                'max_values' => $maxValues
            ];
        }

        return $normalizedMatrix;
    }

    /**
     * Calculate final SAW scores using weighted sum
     */
    private function calculateFinalScores($normalizedMatrix, $criteria)
    {
        $finalScores = [];
        
        // Calculate total weight for normalization
        $totalWeight = $criteria->sum('bobot');
        
        foreach ($normalizedMatrix as $employeeId => $employeeData) {
            $sawScore = 0;
            $weightedScores = [];
            
            foreach ($criteria as $criterion) {
                $normalizedValue = $employeeData['normalized_scores'][$criterion->id_kriteria];
                $weight = $criterion->bobot / 100; // Convert percentage to decimal
                $weightedScore = $normalizedValue * $weight;
                
                $weightedScores[$criterion->id_kriteria] = [
                    'raw_value' => $employeeData['raw_scores'][$criterion->id_kriteria],
                    'normalized_value' => $normalizedValue,
                    'weight' => $weight,
                    'weighted_score' => $weightedScore
                ];
                
                $sawScore += $weightedScore;
            }
            
            $finalScores[$employeeId] = [
                'employee' => $employeeData['employee'],
                'weighted_scores' => $weightedScores,
                'saw_score' => $sawScore,
                'saw_score_percentage' => $sawScore * 100, // Convert to percentage for display
                'max_values' => $employeeData['max_values']
            ];
        }

        return $finalScores;
    }

    /**
     * Rank employees based on SAW scores
     */
    private function rankEmployees($sawScores)
    {
        // Sort by SAW score in descending order
        $sorted = collect($sawScores)->sortByDesc('saw_score')->values();
        
        // Add ranking
        $ranked = $sorted->map(function($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return $ranked;
    }

    /**
     * Get SAW calculation details for a specific employee
     */
    public function getEmployeeSAWDetails($employeeId, $period)
    {
        $allScores = $this->calculateSAWScores($period);
        
        return $allScores->firstWhere('employee.id_karyawan', $employeeId);
    }

    /**
     * Get criteria statistics for normalization reference
     */
    public function getCriteriaStatistics($period)
    {
        $criteria = KriteriaBobot::where('status', 'Disetujui')->get();
        $statistics = [];

        foreach ($criteria as $criterion) {
            $assessments = PenilaianKaryawan::where('periode_penilaian', $period)
                ->where('id_kriteria_bobot', $criterion->id_kriteria)
                ->pluck('nilai');

            if ($assessments->isNotEmpty()) {
                $statistics[$criterion->id_kriteria] = [
                    'criterion' => $criterion,
                    'min' => $assessments->min(),
                    'max' => $assessments->max(),
                    'avg' => round($assessments->avg(), 2),
                    'count' => $assessments->count()
                ];
            }
        }

        return $statistics;
    }

    /**
     * Validate if SAW calculation can be performed
     */
    public function canPerformSAW($period)
    {
        $approvedCriteria = KriteriaBobot::where('status', 'Disetujui')->count();
        $assessmentsCount = PenilaianKaryawan::where('periode_penilaian', $period)->count();
        
        return [
            'can_calculate' => $approvedCriteria > 0 && $assessmentsCount > 0,
            'approved_criteria' => $approvedCriteria,
            'assessments_count' => $assessmentsCount,
            'message' => $this->getValidationMessage($approvedCriteria, $assessmentsCount)
        ];
    }

    /**
     * Get validation message for SAW calculation
     */
    private function getValidationMessage($criteriaCount, $assessmentsCount)
    {
        if ($criteriaCount == 0) {
            return 'Tidak ada kriteria yang disetujui untuk perhitungan SAW.';
        }
        
        if ($assessmentsCount == 0) {
            return 'Tidak ada penilaian karyawan untuk periode ini.';
        }
        
        return 'Perhitungan SAW dapat dilakukan.';
    }
}
