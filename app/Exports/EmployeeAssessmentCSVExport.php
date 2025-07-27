<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Carbon\Carbon;

class EmployeeAssessmentCSVExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $sawResults;
    protected $approvedCriteria;
    protected $period;

    public function __construct($sawResults, $approvedCriteria, $period)
    {
        $this->sawResults = $sawResults;
        $this->approvedCriteria = $approvedCriteria;
        $this->period = $period;
    }

    public function collection()
    {
        return collect($this->sawResults);
    }

    public function headings(): array
    {
        $headers = [
            'Rank',
            'ID Karyawan',
            'Nama Karyawan',
            'Jabatan',
            'Jenis Kelamin',
            'Tanggal Masuk',
            'Skor SAW (%)',
            'Periode'
        ];

        // Add individual criteria headers
        foreach ($this->approvedCriteria as $criteria) {
            $headers[] = $criteria->kriteria . ' (Nilai)';
            $headers[] = $criteria->kriteria . ' (Normalisasi)';
            $headers[] = $criteria->kriteria . ' (Tertimbang)';
        }

        return $headers;
    }

    public function map($result): array
    {
        $employee = $result['employee'];
        $row = [
            $result['rank'],
            $employee->id_karyawan,
            $employee->nama_karyawan,
            $employee->jabatan,
            $employee->jenis_kelamin,
            Carbon::parse($employee->tanggal_masuk)->format('d/m/Y'),
            number_format($result['saw_score_percentage'], 2),
            Carbon::parse($this->period)->format('F Y')
        ];

        // Add individual criteria scores
        foreach ($this->approvedCriteria as $criteria) {
            $scoreData = $result['weighted_scores'][$criteria->id_kriteria] ?? null;
            if ($scoreData) {
                $row[] = $scoreData['raw_value'];
                $row[] = number_format($scoreData['normalized_value'], 4);
                $row[] = number_format($scoreData['weighted_score'], 4);
            } else {
                $row[] = 0;
                $row[] = 0;
                $row[] = 0;
            }
        }

        return $row;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet
                    ->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            },
        ];
    }
}
