<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class EmployeeAssessmentPDFExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
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

    public function collection()
    {
        return collect($this->sawResults);
    }

    public function headings(): array
    {
        return [
            'Rank',
            'ID Karyawan',
            'Nama Karyawan',
            'Jabatan',
            'Skor SAW (%)',
            'Status'
        ];
    }

    public function map($result): array
    {
        $employee = $result['employee'];
        $score = $result['saw_score_percentage'];
        
        // Determine status based on score
        $status = 'Perlu Perbaikan';
        if ($score >= 90) {
            $status = 'Excellent';
        } elseif ($score >= 80) {
            $status = 'Very Good';
        } elseif ($score >= 70) {
            $status = 'Good';
        } elseif ($score >= 60) {
            $status = 'Fair';
        }

        return [
            $result['rank'],
            $employee->id_karyawan,
            $employee->nama_karyawan,
            $employee->jabatan,
            number_format($score, 2) . '%',
            $status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
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
            ],
            // Data rows alignment
            'A:F' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Hasil Penilaian Karyawan';
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

                // Set margins for better PDF layout
                $event->sheet->getPageMargins()
                    ->setTop(0.75)
                    ->setRight(0.25)
                    ->setLeft(0.25)
                    ->setBottom(0.75);
            },
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get highest row and column
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Apply borders to all data
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Highlight top 3 ranks with colors
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

                // Add title and metadata at the top
                $sheet->insertNewRowBefore(1, 6);
                
                // Main title
                $sheet->setCellValue('A1', 'HASIL PENILAIAN KARYAWAN');
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // Subtitle
                $sheet->setCellValue('A2', 'Sistem Penilaian Kinerja Karyawan');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // Period and date info
                $sheet->setCellValue('A3', 'Periode: ' . Carbon::parse($this->period)->format('F Y'));
                $sheet->setCellValue('A4', 'Tanggal Export: ' . now()->format('d/m/Y H:i:s'));
                $sheet->setCellValue('A5', 'Metode: SAW (Simple Additive Weighting)');

                // Add summary statistics
                if (!empty($this->criteriaStats)) {
                    $totalEmployees = count($this->sawResults);
                    $avgScore = collect($this->sawResults)->avg('saw_score_percentage');
                    
                    $sheet->setCellValue('A6', 'Total Karyawan: ' . $totalEmployees . ' | Rata-rata Skor: ' . number_format($avgScore, 2) . '%');
                }

                // Style metadata
                $sheet->getStyle('A3:A6')->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                // Add some spacing
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(6)->setRowHeight(15);
            }
        ];
    }
}
