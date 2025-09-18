@extends('inc.main')
@section('title', 'Penilaian Karyawan')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/datagrid/datatables/datatables.bundle.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Penilaian',
            'category_2' => 'Karyawan',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'star',
                'heading1' => 'Penilaian',
                'heading2' => 'Karyawan',
            ])
            @endcomponent
        </div>

        <!-- Date Range Selection -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="waktu-penilaian-range">Waktu Penilaian:</label>
                    <div class="input-group">
                        <input type="text" id="waktu-penilaian-range" class="form-control" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fal fa-calendar-alt"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button class="btn btn-info" onclick="loadSummary()">
                            <i class="fal fa-chart-bar"></i> Lihat Statistik
                        </button>
                        <div class="btn-group">
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

        <!-- Employee List -->
        <x-panel.show title="Daftar Karyawan" subtitle="Kelola penilaian karyawan berdasarkan kriteria yang telah disetujui">
            <x-slot name="paneltoolbar">
                <x-panel.tool-bar>
                    @if($approvedCriteria->count() > 0)
                        <span class="badge badge-success">{{ $approvedCriteria->count() }} Kriteria Disetujui</span>
                        {{-- @if($sawValidation['can_calculate'])
                            <span class="badge badge-info ml-2">SAW Ready</span>
                            <a href="{{ route('penilaian_karyawanranking', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-primary btn-sm ml-2">
                                <i class="fal fa-trophy"></i> Lihat Ranking SAW
                            </a>
                        @else
                            <span class="badge badge-warning ml-2">{{ $sawValidation['message'] }}</span>
                        @endif --}}
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
                                    <a href="{{ route('penilaian_karyawan.show', ['employee' => $employee->id_karyawan, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                       class="btn btn-info btn-sm">
                                        <i class="fal fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('penilaian_karyawan.create', ['employee' => $employee->id_karyawan, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="fal fa-star"></i> {{ $assessmentCount > 0 ? 'Edit' : 'Nilai' }}
                                    </a>
                                    @if($assessmentCount > 0)
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDeleteAssessments('{{ $employee->id_karyawan }}', '{{ $employee->nama_karyawan }}')">
                                            <i class="fal fa-trash"></i> Hapus
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-5">
                    <i class="fal fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Belum Ada Kriteria yang Disetujui</h4>
                    <p class="text-muted">Silakan tunggu persetujuan kriteria dari Pemimpin Perusahaan terlebih dahulu.</p>
                    <a href="{{ route('kriteria_bobot.index') }}" class="btn btn-primary">
                        <i class="fal fa-plus"></i> Kelola Kriteria
                    </a>
                </div>
            @endif
        </x-panel.show>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus Penilaian</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <h6><i class="fal fa-exclamation-triangle"></i> Peringatan</h6>
                            <p id="deleteMessage" class="mb-0"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            <label for="export-start-date">Tanggal Mulai:</label>
                            <input type="date" id="export-start-date" class="form-control" value="{{ $startDate }}">
                        </div>

                        <div class="form-group">
                            <label for="export-end-date">Tanggal Akhir:</label>
                            <input type="date" id="export-end-date" class="form-control" value="{{ $endDate }}">
                        </div>

                        <div class="form-group">
                            <label>Data yang Disertakan:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include-ranking" checked>
                                <label class="form-check-label" for="include-ranking">
                                    Ranking dan Skor SAW
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include-breakdown" checked>
                                <label class="form-check-label" for="include-breakdown">
                                    Breakdown per Kriteria
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include-stats" checked>
                                <label class="form-check-label" for="include-stats">
                                    Statistik Kriteria
                                </label>
                            </div>
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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#employees-table').dataTable({
                responsive: true,
                order: [[1, 'asc']] // Sort by employee name
            });

            // Initialize daterangepicker
            var startDate = '{{ $startDate }}';
            var endDate = '{{ $endDate }}';

            // Set default dates if not provided
            if (!startDate || !endDate) {
                startDate = moment().startOf('month').format('YYYY-MM-DD');
                endDate = moment().endOf('month').format('YYYY-MM-DD');
            }

            $('#waktu-penilaian-range').daterangepicker({
                startDate: moment(startDate),
                endDate: moment(endDate),
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Terapkan',
                    cancelLabel: 'Batal',
                    fromLabel: 'Dari',
                    toLabel: 'Sampai',
                    customRangeLabel: 'Kustom',
                    weekLabel: 'M',
                    daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    firstDay: 1
                },
                ranges: {
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    '3 Bulan Terakhir': [moment().subtract(3, 'month').startOf('month'), moment().endOf('month')],
                    '6 Bulan Terakhir': [moment().subtract(6, 'month').startOf('month'), moment().endOf('month')],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')]
                }
            }, function(start, end, label) {
                // When date range changes, reload the page with new parameters
                const startDate = start.format('YYYY-MM-DD');
                const endDate = end.format('YYYY-MM-DD');
                window.location.href = `{{ route('penilaian_karyawan.index') }}?start_date=${startDate}&end_date=${endDate}`;
            });

            // Load summary on page load
            loadSummary();
        });

        // Load summary statistics
        function loadSummary() {
            const dateRange = $('#waktu-penilaian-range').data('daterangepicker');
            let startDate = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '{{ $startDate }}';
            let endDate = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '{{ $endDate }}';

            // Build URL with parameters only if dates are provided
            let url = '{{ route('penilaian_karyawan.summary') }}';
            if (startDate && endDate) {
                url += `?start_date=${startDate}&end_date=${endDate}`;
            }

            $.get(url)
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
            const startDate = '{{ $startDate }}';
            const endDate = '{{ $endDate }}';

            let url = `{{ route('penilaian_karyawan.export') }}?format=${format}`;
            if (startDate && endDate) {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }

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
            const startDate = '{{ $startDate }}';
            const endDate = '{{ $endDate }}';

            if (startDate && endDate) {
                $('#export-start-date').val(startDate);
                $('#export-end-date').val(endDate);
            } else {
                // Set current month as default
                $('#export-start-date').val(moment().startOf('month').format('YYYY-MM-DD'));
                $('#export-end-date').val(moment().endOf('month').format('YYYY-MM-DD'));
            }
            $('#exportModal').modal('show');
        }

        // Process export from modal
        function processExport() {
            // Get form values
            const format = $('#export-format').val();
            const startDate = $('#export-start-date').val();
            const endDate = $('#export-end-date').val();
            const includeRanking = $('#include-ranking').is(':checked');
            const includeBreakdown = $('#include-breakdown').is(':checked');
            const includeStats = $('#include-stats').is(':checked');

            // Build URL
            let url = `{{ route('penilaian_karyawan.export') }}?format=${format}`;

            if (startDate && endDate) {
                url += `&start_date=${startDate}&end_date=${endDate}`;
            }

            if (!includeRanking) {
                url += '&exclude_ranking=1';
            }
            if (!includeBreakdown) {
                url += '&exclude_breakdown=1';
            }
            if (!includeStats) {
                url += '&exclude_stats=1';
            }

            btn.innerHTML = '<i class="fal fa-spinner fa-spin"></i> Mengunduh...';
            btn.disabled = true;
        }

        // Confirm delete assessments
        function confirmDeleteAssessments(employeeId, employeeName) {
            const startDate = '{{ $startDate }}';
            const endDate = '{{ $endDate }}';

            let message = `Anda akan menghapus semua penilaian untuk karyawan "${employeeName}"`;
            if (startDate && endDate) {
                message += ` pada rentang tanggal ini`;
            }
            message += `. Tindakan ini tidak dapat dibatalkan.`;

            $('#deleteMessage').text(message);
            $('#deleteForm').attr('action', `{{ route('penilaian_karyawan.bulk-delete', ${employeeId}) }}`);

            // Add hidden inputs
            $('#deleteForm').find('input[name="start_date"]').remove();
            $('#deleteForm').find('input[name="end_date"]').remove();
            $('#deleteForm').find('input[name="employee_ids[]"]').remove();

            if (startDate && endDate) {
                $('#deleteForm').append(`<input type="hidden" name="start_date" value="${startDate}">`);
                $('#deleteForm').append(`<input type="hidden" name="end_date" value="${endDate}">`);
            }
            $('#deleteForm').append(`<input type="hidden" name="employee_ids[]" value="${employeeId}">`);

            $('#deleteModal').modal('show');
        }
    </script>
@endsection
