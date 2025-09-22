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

class PenilaianKaryawanDetailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $penilaianData;
    protected $employee;
    protected $period;

    public function __construct($penilaianData, $employee, $period)
    {
        $this->penilaianData = $penilaianData;
        $this->employee = $employee;
        $this->period = $period;
    }

    public function collection()
    {
        return collect($this->penilaianData);
    }

    public function headings(): array
    {
        return [
            'ID Penilaian',
            'Tanggal Penilaian',
            'Kriteria',
            'Bobot Kriteria (%)',
            'Nilai',
            'Catatan',
            'Dinilai Oleh'
        ];
    }

    public function map($penilaian): array
    {
        return [
            $penilaian->id_penilaian,
            Carbon::parse($penilaian->waktu_penilaian)->format('d/m/Y'),
            $penilaian->kriteriaBobot->kriteria,
            $penilaian->kriteriaBobot->bobot,
            $penilaian->nilai,
            $penilaian->catatan ?? '-',
            $penilaian->penilai->name ?? '-'
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
            'A:G' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Detail Penilaian Karyawan';
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

                // Set margins for better layout
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

                // Add title and metadata at the top
                $sheet->insertNewRowBefore(1, 6);
                
                // Main title
                $sheet->setCellValue('A1', 'DETAIL PENILAIAN KARYAWAN');
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // Employee info
                $sheet->setCellValue('A2', 'Nama Karyawan: ' . $this->employee->nama_karyawan);
                $sheet->setCellValue('A3', 'ID Karyawan: ' . $this->employee->id_karyawan);
                $sheet->setCellValue('A4', 'Jabatan: ' . $this->employee->jabatan);

                // Period and date info
                $sheet->setCellValue('A5', 'Periode: ' . $this->period);
                $sheet->setCellValue('A6', 'Tanggal Export: ' . now()->format('d/m/Y H:i:s'));

                // Style metadata
                $sheet->getStyle('A2:A6')->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                // Add some spacing
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(15);
            }
        ];
    }
}