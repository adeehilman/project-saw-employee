@extends('inc.main')
@section('title', 'Ranking SAW - Penilaian Karyawan')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/datagrid/datatables/datatables.bundle.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .rank-badge {
            font-size: 1.2rem;
            padding: 0.5rem 0.8rem;
        }
        .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #333; }
        .rank-3 { background: linear-gradient(135deg, #CD7F32, #B8860B); color: white; }
        .saw-score {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .criteria-breakdown {
            font-size: 0.85rem;
        }
        .normalization-info {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Penilaian',
            'category_2' => 'Ranking SAW',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'trophy',
                'heading1' => 'Ranking SAW',
                'heading2' => 'Simple Additive Weighting',
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
                        <a href="{{ route('penilaian_karyawan.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary">
                            <i class="fal fa-arrow-left"></i> Kembali ke Penilaian
                        </a>
                        <button class="btn btn-info" onclick="showSAWMethodology()">
                            <i class="fal fa-info-circle"></i> Metodologi SAW
                        </button>
                        <div class="btn-group ml-2">
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                <i class="fal fa-download"></i> Unduh Ranking
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="exportRanking('excel')">
                                    <i class="fal fa-file-excel"></i> Export Excel
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportRanking('csv')">
                                    <i class="fal fa-file-csv"></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($sawValidation['can_calculate'])
            <!-- SAW Ranking Results -->
            <x-panel.show title="Hasil Ranking SAW" subtitle="Peringkat karyawan berdasarkan metode Simple Additive Weighting">
                <x-slot name="paneltoolbar">
                    <x-panel.tool-bar>
                        <span class="badge badge-success">{{ $sawResults->count() }} Karyawan Dinilai</span>
                        <span class="badge badge-info ml-2">{{ $approvedCriteria->count() }} Kriteria</span>
                    </x-panel.tool-bar>
                </x-slot>

                <div class="table-responsive">
                    <table id="ranking-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">Rank</th>
                                <th>Karyawan</th>
                                <th>Jabatan</th>
                                <th>Skor SAW</th>
                                <th>Breakdown Kriteria</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sawResults as $result)
                                <tr>
                                    <td>
                                        <span class="badge rank-badge {{ $result['rank'] <= 3 ? 'rank-' . $result['rank'] : 'badge-primary' }}">
                                            #{{ $result['rank'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $result['employee']->nama_karyawan }}</strong>
                                            <br><small class="text-muted">{{ $result['employee']->id_karyawan }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $result['employee']->jabatan }}</td>
                                    <td>
                                        <div class="saw-score text-primary">{{ number_format($result['saw_score_percentage'], 2) }}</div>
                                        <small class="text-muted">dari 100</small>
                                    </td>
                                    <td>
                                        <div class="criteria-breakdown">
                                            @foreach($result['weighted_scores'] as $criteriaId => $scoreData)
                                                @php
                                                    $criteria = $approvedCriteria->firstWhere('id_kriteria', $criteriaId);
                                                @endphp
                                                @if($criteria)
                                                    <div class="mb-1">
                                                        <span class="badge badge-light">{{ $criteria->kriteria }}</span>
                                                        <span class="text-muted">
                                                            {{ $scoreData['raw_value'] }} → 
                                                            {{ number_format($scoreData['normalized_value'], 3) }} × 
                                                            {{ number_format($scoreData['weight'], 2) }} = 
                                                            <strong>{{ number_format($scoreData['weighted_score'], 3) }}</strong>
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('penilaian_karyawan.show', ['employee' => $result['employee']->id_karyawan, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                                           class="btn btn-info btn-sm">
                                            <i class="fal fa-eye"></i> Detail
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="showSAWDetails('{{ $result['employee']->id_karyawan }}')">
                                            <i class="fal fa-calculator"></i> SAW Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel.show>

            <!-- Criteria Statistics -->
            <x-panel.show title="Statistik Kriteria" subtitle="Informasi normalisasi untuk setiap kriteria">
                <div class="row">
                    @foreach($criteriaStats as $stat)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ $stat['criterion']->kriteria }}</h6>
                                    <small class="text-muted">Bobot: {{ $stat['criterion']->bobot }}%</small>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="text-primary font-weight-bold">{{ $stat['min'] }}</div>
                                            <small class="text-muted">Min</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-success font-weight-bold">{{ $stat['max'] }}</div>
                                            <small class="text-muted">Max</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-info font-weight-bold">{{ $stat['avg'] }}</div>
                                            <small class="text-muted">Avg</small>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <small class="text-muted">{{ $stat['count'] }} penilaian</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-panel.show>
        @else
            <!-- No Data Available -->
            <x-panel.show title="Ranking SAW Tidak Tersedia" subtitle="Persyaratan untuk perhitungan SAW belum terpenuhi">
                <div class="text-center py-5">
                    <i class="fal fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">{{ $sawValidation['message'] }}</h4>
                    <div class="mt-3">
                        <p class="text-muted">Persyaratan SAW:</p>
                        <ul class="list-unstyled">
                            <li>
                                @if($sawValidation['approved_criteria'] > 0)
                                    <i class="fal fa-check text-success"></i>
                                @else
                                    <i class="fal fa-times text-danger"></i>
                                @endif
                                Kriteria yang disetujui: {{ $sawValidation['approved_criteria'] }}
                            </li>
                            <li>
                                @if($sawValidation['assessments_count'] > 0)
                                    <i class="fal fa-check text-success"></i>
                                @else
                                    <i class="fal fa-times text-danger"></i>
                                @endif
                                Penilaian karyawan: {{ $sawValidation['assessments_count'] }}
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('penilaian_karyawan.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-primary">
                        <i class="fal fa-arrow-left"></i> Kembali ke Penilaian
                    </a>
                </div>
            </x-panel.show>
        @endif
    </main>

    <!-- SAW Methodology Modal -->
    <div class="modal fade" id="sawMethodologyModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Metodologi SAW (Simple Additive Weighting)</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="normalization-info">
                        <h6><i class="fal fa-info-circle"></i> Langkah-langkah Perhitungan SAW:</h6>
                        <ol>
                            <li><strong>Menentukan bobot untuk setiap kriteria</strong><br>
                                <small class="text-muted">Bobot ditentukan oleh Pemimpin Perusahaan saat menyetujui kriteria</small>
                            </li>
                            <li><strong>Penilaian karyawan</strong><br>
                                <small class="text-muted">Penilaian diberikan pada skala 0 hingga 100</small>
                            </li>
                            <li><strong>Normalisasi Matriks</strong><br>
                                <small class="text-muted">r<sub>ij</sub> = x<sub>ij</sub> / max(x<sub>ij</sub>) untuk kriteria benefit</small>
                            </li>
                            <li><strong>Menghitung skor akhir</strong><br>
                                <small class="text-muted">V<sub>i</sub> = Σ(w<sub>j</sub> × r<sub>ij</sub>)</small>
                            </li>
                            <li><strong>Hasil Perangkingan</strong><br>
                                <small class="text-muted">Karyawan diurutkan berdasarkan skor SAW tertinggi</small>
                            </li>
                        </ol>
                    </div>
                    
                    <h6>Keterangan:</h6>
                    <ul class="list-unstyled">
                        <li><strong>x<sub>ij</sub></strong> = Nilai karyawan i pada kriteria j</li>
                        <li><strong>r<sub>ij</sub></strong> = Nilai ternormalisasi</li>
                        <li><strong>w<sub>j</sub></strong> = Bobot kriteria j</li>
                        <li><strong>V<sub>i</sub></strong> = Skor akhir karyawan i</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SAW Details Modal -->
    <div class="modal fade" id="sawDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Perhitungan SAW</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="sawDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
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
            $('#ranking-table').dataTable({
                responsive: true,
                order: [[0, 'asc']], // Sort by rank
                pageLength: 25
            });

            // Initialize daterangepicker
            $('#waktu-penilaian-range').daterangepicker({
                startDate: moment('{{ $startDate }}'),
                endDate: moment('{{ $endDate }}'),
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
                window.location.href = `{{ route('penilaian_karyawan.ranking') }}?start_date=${startDate}&end_date=${endDate}`;
            });
        });

        function showSAWMethodology() {
            $('#sawMethodologyModal').modal('show');
        }

        function showSAWDetails(employeeId) {
            const startDate = '{{ $startDate }}';
            const endDate = '{{ $endDate }}';

            // Show loading
            $('#sawDetailsContent').html('<div class="text-center"><i class="fal fa-spinner fa-spin"></i> Loading...</div>');
            $('#sawDetailsModal').modal('show');

            // Fetch SAW details
            $.get(`{{ route('penilaian_karyawan.saw-details') }}?employee_id=${employeeId}&start_date={{ $startDate }}&end_date={{ $endDate }}`)
                .done(function(data) {
                    if (data) {
                        let content = `
                            <h6>Karyawan: ${data.employee.nama_karyawan}</h6>
                            <p class="text-muted">Skor SAW: <strong>${data.saw_score_percentage.toFixed(2)}</strong> | Rank: <strong>#${data.rank}</strong></p>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Bobot</th>
                                            <th>Nilai Asli</th>
                                            <th>Nilai Max</th>
                                            <th>Normalisasi</th>
                                            <th>Skor Tertimbang</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                        
                        Object.keys(data.weighted_scores).forEach(criteriaId => {
                            const score = data.weighted_scores[criteriaId];
                            const maxValue = data.max_values[criteriaId];
                            
                            content += `
                                <tr>
                                    <td>${criteriaId}</td>
                                    <td>${(score.weight * 100).toFixed(1)}%</td>
                                    <td>${score.raw_value}</td>
                                    <td>${maxValue}</td>
                                    <td>${score.normalized_value.toFixed(4)}</td>
                                    <td><strong>${score.weighted_score.toFixed(4)}</strong></td>
                                </tr>`;
                        });
                        
                        content += `
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-primary">
                                            <th colspan="5">Total Skor SAW:</th>
                                            <th><strong>${data.saw_score_percentage.toFixed(2)}</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>`;
                        
                        $('#sawDetailsContent').html(content);
                    } else {
                        $('#sawDetailsContent').html('<div class="alert alert-warning">Data tidak ditemukan.</div>');
                    }
                })
                .fail(function() {
                    $('#sawDetailsContent').html('<div class="alert alert-danger">Gagal memuat data.</div>');
                });
        }

        function exportRanking(format = 'excel') {
            const startDate = '{{ $startDate }}';
            const endDate = '{{ $endDate }}';
            const url = `{{ route('penilaian_karyawan.export') }}?start_date=${startDate}&end_date=${endDate}&format=${format}`;

            // Create temporary link and trigger download
            const link = document.createElement('a');
            link.href = url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endsection
