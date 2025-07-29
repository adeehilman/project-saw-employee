@extends('inc.main')
@section('title', 'Penilaian Karyawan - ' . $employee->nama_karyawan)
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <style>
        .criteria-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .criteria-card:hover {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .criteria-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.35rem 0.35rem 0 0;
        }

        .score-input {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
        }

        .score-slider {
            margin: 1rem 0;
        }

        .score-display {
            font-size: 2rem;
            font-weight: bold;
            color: #5a5c69;
        }
    </style>
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Penilaian',
            'category_2' => 'Karyawan',
            'category_3' => $employee->nama_karyawan,
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'star',
                'heading1' => 'Penilaian Karyawan',
                'heading2' => $employee->nama_karyawan,
            ])
            @endcomponent
        </div>

        <form action="{{ route('penilaian_karyawan.store') }}" method="POST" id="scoringForm">
            @csrf
            <input type="hidden" name="id_karyawan" value="{{ $employee->id_karyawan }}">
            <input type="hidden" name="waktu_penilaian" value="{{ now()->format('Y-m-d') }}">

            <!-- Employee Info -->
            <x-panel.show title="Informasi Karyawan" subtitle="Data karyawan yang akan dinilai">
                <x-slot name="paneltoolbar">
                    <x-panel.tool-bar>
                        <a href="{{ route('penilaian_karyawan.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary btn-sm">
                            <i class="fal fa-arrow-left"></i> Kembali
                        </a>
                    </x-panel.tool-bar>
                </x-slot>

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
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Jenis Kelamin:</strong></td>
                                <td>{{ $employee->jenis_kelamin }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Masuk:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($employee->tanggal_masuk)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Periode Penilaian:</strong></td>
                                <td><strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </x-panel.show>

            <!-- Scoring Criteria -->
            <x-panel.show title="Kriteria Penilaian" subtitle="Berikan nilai untuk setiap kriteria (0-100)">
                <div class="row">
                    @foreach ($approvedCriteria as $index => $criteria)
                        @php
                            $existingScore = $existingAssessments->get($criteria->id_kriteria);
                            $currentScore = $existingScore ? $existingScore->nilai : 0;
                            $currentNote = $existingScore ? $existingScore->catatan : '';
                        @endphp
                        <div class="col-lg-6 col-xl-4">
                            <div class="criteria-card">
                                <div class="criteria-header">
                                    <h6 class="mb-1">{{ $criteria->kriteria }}</h6>
                                    <small>Bobot: {{ $criteria->bobot }}%</small>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="score-display" id="score-display-{{ $index }}">
                                            {{ $currentScore }}</div>
                                        <small class="text-muted">Nilai (0-100)</small>
                                    </div>

                                    <div class="score-slider">
                                        <input type="range" class="form-control-range"
                                            id="score-slider-{{ $index }}" min="0" max="100"
                                            step="1" value="{{ $currentScore }}"
                                            oninput="updateScore({{ $index }}, this.value)">
                                    </div>

                                    <div class="form-group">
                                        <input type="number" name="scores[{{ $criteria->id_kriteria }}]"
                                            id="score-input-{{ $index }}" class="form-control score-input"
                                            min="0" max="100" step="0.1" value="{{ $currentScore }}"
                                            placeholder="0" oninput="updateSlider({{ $index }}, this.value)"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="note-{{ $index }}">Catatan (Opsional):</label>
                                        <textarea name="catatan[{{ $criteria->id_kriteria }}]" id="note-{{ $index }}" class="form-control"
                                            rows="2" placeholder="Tambahkan catatan penilaian...">{{ $currentNote }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($approvedCriteria->isEmpty())
                    <div class="text-center py-5">
                        <i class="fal fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Tidak Ada Kriteria yang Disetujui</h4>
                        <p class="text-muted">Silakan tunggu persetujuan kriteria dari Pemimpin Perusahaan.</p>
                    </div>
                @endif
            </x-panel.show>

            @if ($approvedCriteria->isNotEmpty())
                <!-- Summary & Submit -->
                <x-panel.show title="Informasi Penilaian" subtitle="Catatan tentang perhitungan skor">
                    <div class="alert alert-info">
                        <i class="fal fa-info-circle"></i>
                        <strong>Catatan:</strong> Skor akhir akan dihitung menggunakan metode SAW (Simple Additive Weighting)
                        setelah semua penilaian disimpan. Metode SAW memberikan hasil yang lebih akurat dengan normalisasi
                        nilai berdasarkan nilai maksimum setiap kriteria.
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Bobot</th>
                                            <th>Nilai Saat Ini</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approvedCriteria as $index => $criteria)
                                            <tr>
                                                <td>{{ $criteria->kriteria }}</td>
                                                <td>{{ $criteria->bobot }}%</td>
                                                <td><span id="summary-score-{{ $index }}">{{ $existingAssessments->get($criteria->id_kriteria)?->nilai ?? 0 }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <x-slot name="panelcontentfoot">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('penilaian_karyawan.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-secondary">
                                <i class="fal fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fal fa-save"></i> Simpan Penilaian
                            </button>
                        </div>
                    </x-slot>
                </x-panel.show>
            @endif
        </form>
    </main>
@endsection

@section('pages-script')
    <script>

        $(document).ready(function() {
            // Initialize form
            console.log('Scoring form initialized');
        });

        function updateScore(index, value) {
            // Update display and input
            document.getElementById(`score-display-${index}`).textContent = value;
            document.getElementById(`score-input-${index}`).value = value;
            document.getElementById(`summary-score-${index}`).textContent = value;
        }

        function updateSlider(index, value) {
            // Ensure value is within bounds
            value = Math.max(0, Math.min(100, parseFloat(value) || 0));

            // Update slider and display
            document.getElementById(`score-slider-${index}`).value = value;
            document.getElementById(`score-display-${index}`).textContent = value;
            document.getElementById(`summary-score-${index}`).textContent = value;
        }

        // Form validation
        $('#scoringForm').on('submit', function(e) {
            let hasEmptyScore = false;

            $('input[name^="scores"]').each(function() {
                if (!$(this).val() || parseFloat($(this).val()) < 0) {
                    hasEmptyScore = true;
                    return false;
                }
            });

            if (hasEmptyScore) {
                e.preventDefault();
                alert('Mohon isi semua nilai kriteria dengan benar (0-100).');
                return false;
            }

            return confirm('Apakah Anda yakin ingin menyimpan penilaian ini?');
        });
    </script>
@endsection
