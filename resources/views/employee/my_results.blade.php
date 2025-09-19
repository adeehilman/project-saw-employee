@extends('inc.main')
@section('title', 'Hasil Penilaian Saya')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <style>
        .score-card {
            border-left: 4px solid #4e73df;
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
        }
        .score-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
        }
        .total-score {
            font-size: 3rem;
            font-weight: bold;
            color: #5a5c69;
        }
        .criteria-item {
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 0;
        }
        .criteria-item:last-child {
            border-bottom: none;
        }
        .my-rank-badge {
            font-size: 1.5rem;
            padding: 0.75rem 1.5rem;
        }
    </style>
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Penilaian',
            'category_2' => 'Hasil Saya',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'star',
                'heading1' => 'Hasil Penilaian Saya',
                'heading2' => 'Lihat hasil penilaian kinerja Anda',
            ])
            @endcomponent
        </div>

        <!-- Filter Date Range -->
        <x-panel.show title="Filter Periode Penilaian" subtitle="Pilih rentang tanggal untuk melihat hasil penilaian">
            <form method="GET" action="{{ route('employee.my_results') }}" class="row">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fal fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('employee.my_results') }}" class="btn btn-secondary">
                        <i class="fal fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </x-panel.show>

        <!-- Employee Info & Score Summary -->
        <div class="row">
            <div class="col-lg-8">
                <x-panel.show title="Informasi Pribadi" subtitle="Data diri dan periode penilaian">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Karyawan:</strong></td>
                                    <td>{{ $employee->id_karyawan }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td>{{ $employee->nama_karyawan }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan:</strong></td>
                                    <td>{{ $employee->jabatan }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Kelamin:</strong></td>
                                    <td>{{ $employee->jenis_kelamin }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Tanggal Masuk:</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($employee->tanggal_masuk)->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Periode Penilaian:</strong></td>
                                    <td>
                                        @if($startDate && $endDate)
                                            <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                                        @else
                                            <strong>Semua Periode</strong>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status Penilaian:</strong></td>
                                    <td>
                                        @php
                                            $approvedCriteriaCount = $approvedCriteria->count();
                                            $isComplete = $assessments->count() >= $approvedCriteriaCount;
                                        @endphp
                                        @if($isComplete)
                                            <span class="badge badge-success">
                                                <i class="fal fa-check-circle"></i> Lengkap
                                            </span>
                                        @elseif($assessments->count() > 0)
                                            <span class="badge badge-warning">
                                                <i class="fal fa-clock"></i> Sebagian
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fal fa-minus-circle"></i> Belum Dinilai
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Kriteria Dinilai:</strong></td>
                                    <td>{{ $assessments->count() }} dari {{ $approvedCriteriaCount }} kriteria</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </x-panel.show>
            </div>

            <div class="col-lg-4">
                <!-- SAW Score Card -->
                @if($sawDetails && $sawValidation['can_calculate'])
                    <div class="card score-card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">Skor SAW Saya</h5>
                            <div class="total-score text-primary">{{ number_format($sawDetails['saw_score_percentage'], 2) }}</div>
                            <div class="progress mt-3">
                                <div class="progress-bar {{ $sawDetails['saw_score_percentage'] >= 80 ? 'bg-success' : ($sawDetails['saw_score_percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                    role="progressbar" 
                                    style="width: {{ min(100, $sawDetails['saw_score_percentage']) }}%">
                                </div>
                            </div>
                            <small class="text-muted">Metode SAW</small>
                            <div class="mt-3">
                                <span class="badge badge-primary my-rank-badge">Peringkat #{{ $sawDetails['rank'] }}</span>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">dari {{ $allSawResults->count() }} karyawan</small>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card score-card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title">Skor SAW</h5>
                            <div class="text-muted">
                                <i class="fal fa-info-circle" style="font-size: 3rem;"></i>
                                <p class="mt-2">{{ $sawValidation['message'] ?? 'Belum ada data untuk perhitungan SAW' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- SAW Calculation Details -->
        @if($sawDetails && $sawValidation['can_calculate'])
            <x-panel.show title="Detail Perhitungan SAW" subtitle="Breakdown normalisasi dan perhitungan SAW untuk penilaian Anda">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Kriteria</th>
                                <th>Bobot</th>
                                <th>Nilai Saya</th>
                                <th>Nilai Tertinggi</th>
                                <th>Normalisasi</th>
                                <th>Skor Tertimbang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sawDetails['weighted_scores'] as $criteriaId => $scoreData)
                                @php
                                    $criteria = $assessments->firstWhere('id_kriteria_bobot', $criteriaId)?->kriteriaBobot;
                                    $maxValue = $sawDetails['max_values'][$criteriaId] ?? 0;
                                @endphp
                                @if($criteria)
                                    <tr>
                                        <td><strong>{{ $criteria->kriteria }}</strong></td>
                                        <td>
                                            <span class="badge badge-info">{{ number_format($scoreData['weight'] * 100, 1) }}%</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $scoreData['raw_value'] }}</span>
                                        </td>
                                        <td>{{ $maxValue }}</td>
                                        <td>{{ number_format($scoreData['normalized_value'], 4) }}</td>
                                        <td><strong>{{ number_format($scoreData['weighted_score'], 4) }}</strong></td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-success">
                            <tr>
                                <th colspan="5">Total Skor SAW Saya:</th>
                                <th><strong>{{ number_format($sawDetails['saw_score_percentage'], 2) }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="mt-3">
                    <h6>Keterangan Perhitungan:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Nilai Saya:</strong> Nilai yang diberikan untuk Anda (0-100)</li>
                        <li><strong>Nilai Tertinggi:</strong> Nilai tertinggi untuk kriteria ini di periode yang sama</li>
                        <li><strong>Normalisasi:</strong> Nilai Saya ÷ Nilai Tertinggi</li>
                        <li><strong>Skor Tertimbang:</strong> Normalisasi × Bobot</li>
                        <li><strong>Total Skor SAW:</strong> Jumlah semua skor tertimbang</li>
                    </ul>
                </div>
            </x-panel.show>
        @endif

        <!-- Assessment Details -->
        <x-panel.show title="Detail Penilaian per Kriteria" subtitle="Breakdown nilai untuk setiap kriteria penilaian">
            @if($assessments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kriteria</th>
                                <th>Bobot</th>
                                <th>Nilai Saya</th>
                                <th>Skor Tertimbang</th>
                                <th>Catatan</th>
                                <th>Dinilai Oleh</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments as $assessment)
                                @php
                                    $weightedScore = ($assessment->nilai * $assessment->kriteriaBobot->bobot) / 100;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $assessment->kriteriaBobot->kriteria }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $assessment->kriteriaBobot->bobot }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary score-badge">{{ $assessment->nilai }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($weightedScore, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($assessment->catatan)
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $assessment->catatan }}">
                                                {{ $assessment->catatan }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $assessment->penilai->name }}</td>
                                    <td>{{ $assessment->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Assessment History Chart -->
                <div class="mt-4">
                    <h6>Grafik Penilaian Saya per Kriteria</h6>
                    <canvas id="myAssessmentChart" width="400" height="200"></canvas>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fal fa-star text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Belum Ada Penilaian</h4>
                    <p class="text-muted">
                        Anda belum dinilai untuk periode 
                        @if($startDate && $endDate)
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        @else
                            yang dipilih
                        @endif
                    </p>
                    <p class="text-muted">Silakan hubungi atasan Anda untuk informasi lebih lanjut.</p>
                </div>
            @endif
        </x-panel.show>

        <!-- Assessment Notes -->
        @if($assessments->where('catatan', '!=', null)->count() > 0)
            <x-panel.show title="Catatan Penilaian" subtitle="Catatan tambahan dari penilai untuk setiap kriteria">
                @foreach($assessments->where('catatan', '!=', null) as $assessment)
                    <div class="criteria-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $assessment->kriteriaBobot->kriteria }}</h6>
                                <p class="mb-1">{{ $assessment->catatan }}</p>
                                <small class="text-muted">
                                    Oleh: {{ $assessment->penilai->name }} • 
                                    {{ $assessment->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <span class="badge badge-primary score-badge">{{ $assessment->nilai }}</span>
                        </div>
                    </div>
                @endforeach
            </x-panel.show>
        @endif
    </main>
@endsection

@section('pages-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @if($assessments->count() > 0)
        // Create assessment chart
        const ctx = document.getElementById('myAssessmentChart').getContext('2d');
        const assessmentData = @json($assessments->map(function($assessment) {
            return [
                'criteria' => $assessment->kriteriaBobot->kriteria,
                'score' => $assessment->nilai,
                'weight' => $assessment->kriteriaBobot->bobot
            ];
        }));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: assessmentData.map(item => item.criteria),
                datasets: [{
                    label: 'Nilai Saya',
                    data: assessmentData.map(item => item.score),
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
                }, {
                    label: 'Bobot (%)',
                    data: assessmentData.map(item => item.weight),
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Nilai'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Bobot (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    },
                    title: {
                        display: true,
                        text: 'Penilaian Saya per Kriteria'
                    }
                }
            }
        });
        @endif
    </script>
@endsection
