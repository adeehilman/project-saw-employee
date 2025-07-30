<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class EmployeeAssessmentExport implements WithMultipleSheets
{
    protected $sawResults;
    protected $approvedCriteria;
    protected $period;
    protected $criteriaStats;

    public function __construct($sawResults, $approvedCriteria, $period, $criteriaStats)
    {
        $this->sawResults = $sawResults;
        $this->approvedCriteria = $approvedCriteria;
        $this->period = $period;
        $this->criteriaStats = $criteriaStats;
    }

    public function sheets(): array
    {
        return [
            'Hasil Penilaian' => new EmployeeScoresSheet($this->sawResults, $this->approvedCriteria, $this->period),
            'Statistik Kriteria' => new CriteriaStatsSheet($this->criteriaStats, $this->period),
            'Detail Perhitungan' => new SAWCalculationSheet($this->sawResults, $this->approvedCriteria, $this->period),
        ];
    }
}

class EmployeeScoresSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
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
            'Waktu Penilaian Karyawan'
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
            $result['waktu_penilaian_karyawan'] ?? '-'
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Hasil Penilaian';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet
                    ->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
            },
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Apply borders to all data
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Highlight top 3 ranks
                for ($row = 2; $row <= min(4, $highestRow); $row++) {
                    $rank = $sheet->getCell('A' . $row)->getValue();
                    $color = match($rank) {
                        1 => 'FFD700', // Gold
                        2 => 'C0C0C0', // Silver
                        3 => 'CD7F32', // Bronze
                        default => null
                    };
                    
                    if ($color) {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $color]
                            ]
                        ]);
                    }
                }

                // Add title and metadata
                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', 'HASIL PENILAIAN KARYAWAN');
                $sheet->setCellValue('A3', 'Tanggal Export: ' . now()->format('d/m/Y H:i:s'));
                $sheet->setCellValue('A4', 'Metode: SAW (Simple Additive Weighting)');

                // Style title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->mergeCells('A1:' . $highestColumn . '1');
            }
        ];
    }


}

class CriteriaStatsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $criteriaStats;
    protected $period;

    public function __construct($criteriaStats, $period)
    {
        $this->criteriaStats = $criteriaStats;
        $this->period = $period;
    }

    public function collection()
    {
        return collect($this->criteriaStats);
    }

    public function headings(): array
    {
        return [
            'Kriteria',
            'Bobot (%)',
            'Nilai Min',
            'Nilai Max',
            'Rata-rata',
            'Jumlah Penilaian'
        ];
    }

    public function map($stat): array
    {
        return [
            $stat['criterion']->kriteria,
            $stat['criterion']->bobot,
            $stat['min'],
            $stat['max'],
            number_format($stat['avg'], 2),
            $stat['count']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Statistik Kriteria';
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

class SAWCalculationSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
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
        $detailData = [];

        foreach ($this->sawResults as $result) {
            foreach ($this->approvedCriteria as $criteria) {
                $scoreData = $result['weighted_scores'][$criteria->id_kriteria] ?? null;
                if ($scoreData) {
                    $detailData[] = [
                        'employee' => $result['employee'],
                        'criteria' => $criteria,
                        'score_data' => $scoreData,
                        'max_value' => $result['max_values'][$criteria->id_kriteria] ?? 0
                    ];
                }
            }
        }

        return collect($detailData);
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Kriteria',
            'Bobot (%)',
            'Nilai Asli',
            'Nilai Max',
            'Normalisasi',
            'Skor Tertimbang'
        ];
    }

    public function map($item): array
    {
        return [
            $item['employee']->nama_karyawan,
            $item['criteria']->kriteria,
            $item['criteria']->bobot,
            $item['score_data']['raw_value'],
            $item['max_value'],
            number_format($item['score_data']['normalized_value'], 4),
            number_format($item['score_data']['weighted_score'], 4)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF3E0']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Detail Perhitungan';
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
