@extends('inc.main')
@section('title', 'Hasil Penilaian Karyawan')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/datagrid/datatables/datatables.bundle.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Hasil',
            'category_2' => 'Penilaian',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'chart-line',
                'heading1' => 'Hasil',
                'heading2' => 'Penilaian Karyawan',
            ])
            @endcomponent
        </div>

        <!-- Period Selection -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="period-select">Periode Penilaian:</label>
                    <select id="period-select" class="form-control">
                        <option value="{{ now()->format('Y-m-01') }}" {{ $currentPeriod == now()->format('Y-m-01') ? 'selected' : '' }}>
                            {{ now()->format('F Y') }} (Bulan Ini)
                        </option>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period['value'] }}" {{ $currentPeriod == $period['value'] ? 'selected' : '' }}>
                                {{ $period['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button class="btn btn-info" onclick="loadSummary()">
                            <i class="fal fa-chart-bar"></i> Lihat Statistik
                        </button>
                        <div class="btn-group ml-2">
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                <i class="fal fa-download"></i> Unduh Hasil
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="exportData('excel')">
                                    <i class="fal fa-file-excel"></i> Export Excel
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportData('csv')">
                                    <i class="fal fa-file-csv"></i> Export CSV
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                    <i class="fal fa-file-pdf"></i> Export PDF
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="showExportModal()">
                                    <i class="fal fa-cog"></i> Opsi Export
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row" id="summary-cards" style="display: none;">
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            <span id="total-employees">0</span>
                            <small class="m-0 l-h-n">Total Karyawan</small>
                        </h3>
                    </div>
                    <i class="fal fa-users position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-warning-300 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            <span id="assessed-employees">0</span>
                            <small class="m-0 l-h-n">Sudah Dinilai</small>
                        </h3>
                    </div>
                    <i class="fal fa-star position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-success-300 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            <span id="completed-employees">0</span>
                            <small class="m-0 l-h-n">Penilaian Lengkap</small>
                        </h3>
                    </div>
                    <i class="fal fa-check-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-info-300 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            <span id="completion-rate">0</span>%
                            <small class="m-0 l-h-n">Tingkat Kelengkapan</small>
                        </h3>
                    </div>
                    <i class="fal fa-percentage position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
        </div>

        <!-- Employee Results -->
        <x-panel.show title="Hasil Penilaian Karyawan" subtitle="Ranking dan hasil penilaian berdasarkan metode SAW">
            <x-slot name="paneltoolbar">
                <x-panel.tool-bar>
                    @if($approvedCriteria->count() > 0)
                        <span class="badge badge-success">{{ $approvedCriteria->count() }} Kriteria Disetujui</span>
                        @if($sawValidation['can_calculate'])
                            <span class="badge badge-info ml-2">SAW Ready</span>
                            <a href="{{ route('results.ranking', ['period' => $currentPeriod]) }}" class="btn btn-primary btn-sm ml-2">
                                <i class="fal fa-trophy"></i> Lihat Ranking SAW
                            </a>
                        @else
                            <span class="badge badge-warning ml-2">{{ $sawValidation['message'] }}</span>
                        @endif
                    @else
                        <span class="badge badge-warning">Belum ada kriteria yang disetujui</span>
                    @endif
                </x-panel.tool-bar>
            </x-slot>
            
            @if($approvedCriteria->count() > 0)
                <table id="employees-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>ID Karyawan</th>
                            <th>Nama Karyawan</th>
                            <th>Jabatan</th>
                            <th>Status Penilaian</th>
                            <th>Skor SAW</th>
                            <th>Terakhir Dinilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            @php
                                $assessmentCount = $employee->penilaian->count();
                                $totalCriteria = $approvedCriteria->count();
                                $isComplete = $assessmentCount >= $totalCriteria;
                                $lastAssessment = $employee->penilaian->sortByDesc('updated_at')->first();
                                
                                // Get SAW data for this employee
                                $sawData = $sawResults->firstWhere('employee.id_karyawan', $employee->id_karyawan);
                                $sawScore = $sawData ? $sawData['saw_score_percentage'] : 0;
                                $sawRank = $sawData ? $sawData['rank'] : '-';
                            @endphp
                            <tr>
                                <td>
                                    @if($sawRank !== '-')
                                        <span class="badge badge-primary">#{{ $sawRank }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $employee->id_karyawan }}</td>
                                <td>{{ $employee->nama_karyawan }}</td>
                                <td>{{ $employee->jabatan }}</td>
                                <td>
                                    @if($isComplete)
                                        <span class="badge badge-success">
                                            <i class="fal fa-check-circle"></i> Lengkap ({{ $assessmentCount }}/{{ $totalCriteria }})
                                        </span>
                                    @elseif($assessmentCount > 0)
                                        <span class="badge badge-warning">
                                            <i class="fal fa-clock"></i> Sebagian ({{ $assessmentCount }}/{{ $totalCriteria }})
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fal fa-minus-circle"></i> Belum Dinilai
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($sawScore > 0)
                                        <span class="badge badge-success">{{ number_format($sawScore, 2) }}</span>
                                        <small class="text-muted d-block">SAW Method</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @if($lastAssessment)
                                        {{ $lastAssessment->updated_at->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">Belum pernah</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('results.show', ['employee' => $employee->id_karyawan, 'period' => $currentPeriod]) }}" 
                                       class="btn btn-info btn-sm">
                                        <i class="fal fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="fal fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Belum Ada Kriteria yang Disetujui</h4>
                    <p class="text-muted">Silakan setujui kriteria terlebih dahulu untuk melihat hasil penilaian.</p>
                    @if(auth()->user()->role == 'Pemimpin Perusahaan')
                        <a href="{{ route('approval.index') }}" class="btn btn-primary">
                            <i class="fal fa-check"></i> Kelola Persetujuan
                        </a>
                    @endif
                </div>
            @endif
        </x-panel.show>
    </main>

    <!-- Export Options Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Opsi Export Hasil Penilaian</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="exportForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="export-format">Format File:</label>
                            <select id="export-format" class="form-control">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="export-period">Periode:</label>
                            <select id="export-period" class="form-control">
                                <option value="{{ $currentPeriod }}">{{ \Carbon\Carbon::parse($currentPeriod)->format('F Y') }} (Saat Ini)</option>
                                @foreach($availablePeriods as $period)
                                    <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fal fa-info-circle"></i> Informasi Export</h6>
                            <ul class="mb-0">
                                <li><strong>Excel:</strong> Format .xlsx dengan multiple sheets (Hasil, Statistik, Detail Perhitungan)</li>
                                <li><strong>CSV:</strong> Format data mentah, cocok untuk analisis lebih lanjut</li>
                                <li><strong>PDF:</strong> Format laporan siap cetak dengan ranking dan statistik</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" onclick="processExport()">
                            <i class="fal fa-download"></i> Unduh Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('pages-script')
    <script src="/admin/js/datagrid/datatables/datatables.bundle.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#employees-table').dataTable({
                responsive: true,
                order: [[0, 'asc']] // Sort by rank
            });

            // Load summary on page load
            loadSummary();
        });

        // Period selection change
        $('#period-select').change(function() {
            const selectedPeriod = $(this).val();
            window.location.href = `{{ route('results.index') }}?period=${selectedPeriod}`;
        });

        // Load summary statistics
        function loadSummary() {
            const period = $('#period-select').val();
            
            $.get(`{{ route('results.summary') }}?period=${period}`)
                .done(function(data) {
                    $('#total-employees').text(data.total_employees);
                    $('#assessed-employees').text(data.assessed_employees);
                    $('#completed-employees').text(data.completed_employees);
                    $('#completion-rate').text(data.completion_rate);
                    $('#summary-cards').show();
                })
                .fail(function() {
                    console.error('Failed to load summary data');
                });
        }

        // Export data with format
        function exportData(format = 'excel') {
            const period = $('#period-select').val();
            const url = `{{ route('results.export') }}?period=${period}&format=${format}`;
            
            // Create temporary link and trigger download
            const link = document.createElement('a');
            link.href = url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Show export modal
        function showExportModal() {
            $('#export-period').val($('#period-select').val());
            $('#exportModal').modal('show');
        }

        // Process export from modal
        function processExport() {
            const format = $('#export-format').val();
            const period = $('#export-period').val();
            
            let url = `{{ route('results.export') }}?period=${period}&format=${format}`;
            
            // Show loading
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fal fa-spinner fa-spin"></i> Mengunduh...';
            btn.disabled = true;
            
            // Create download link
            const link = document.createElement('a');
            link.href = url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Reset button after delay
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                $('#exportModal').modal('hide');
            }, 2000);
        }
    </script>
@endsection
