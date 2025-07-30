@extends('inc.main')
@section('title', 'Detail Persetujuan Kriteria')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <style>
        .timeline-sm .timeline-item {
            padding-bottom: 1rem;
        }
        .timeline-sm .timeline-marker {
            width: 20px;
            height: 20px;
            font-size: 0.8rem;
        }
        .timeline-sm .timeline-content {
            margin-left: 30px;
        }
    </style>
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Persetujuan',
            'category_2' => 'Detail',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'eye',
                'heading1' => 'Detail',
                'heading2' => 'Kriteria',
            ])
            @endcomponent
        </div>

        <div class="row">
            <!-- Criteria Details -->
            <div class="col-lg-8">
                <x-panel.show title="Informasi Kriteria" subtitle="Detail kriteria yang akan disetujui">
                    <x-slot name="paneltoolbar">
                        <x-panel.tool-bar>
                            <a href="{{ route('approval.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fal fa-arrow-left"></i> Kembali
                            </a>
                        </x-panel.tool-bar>
                    </x-slot>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Kriteria:</strong></td>
                                    <td>{{ $criteria->id_kriteria }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Kriteria:</strong></td>
                                    <td>{{ $criteria->kriteria }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Bobot:</strong></td>
                                    <td>{{ $criteria->bobot }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($criteria->status == 'Menunggu')
                                            <span class="badge badge-warning">Menunggu Persetujuan</span>
                                        @elseif($criteria->status == 'Disetujui')
                                            <span class="badge badge-success">Disetujui</span>
                                        @elseif($criteria->status == 'Ditolak')
                                            <span class="badge badge-danger">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Dibuat Oleh:</strong></td>
                                    <td>{{ $criteria->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Submit:</strong></td>
                                    <td>{{ $criteria->submitted_at ? $criteria->submitted_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                                @if($criteria->approved_by)
                                <tr>
                                    <td><strong>Disetujui/Ditolak Oleh:</strong></td>
                                    <td>{{ $criteria->approver->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Persetujuan:</strong></td>
                                    <td>{{ $criteria->approved_at ? $criteria->approved_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($criteria->rejection_reason)
                        <div class="alert alert-danger mt-3">
                            <h6><i class="fal fa-exclamation-triangle"></i> Alasan Penolakan:</h6>
                            <p class="mb-0">{{ $criteria->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($criteria->isPending())
                        <div class="mt-4 text-center">
                            <button type="button" class="btn btn-success btn-lg mr-3" onclick="approveItem('{{ $criteria->id_kriteria }}')">
                                <i class="fal fa-check"></i> Setujui Kriteria
                            </button>
                            <button type="button" class="btn btn-danger btn-lg" onclick="rejectItem('{{ $criteria->id_kriteria }}')">
                                <i class="fal fa-times"></i> Tolak Kriteria
                            </button>
                        </div>
                    @endif
                </x-panel.show>
            </div>

        </div>
    </main>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Persetujuan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approvalForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h6><i class="fal fa-info-circle"></i> Konfirmasi Persetujuan</h6>
                            <p class="mb-0">Anda akan menyetujui kriteria: <strong>{{ $criteria->kriteria }}</strong></p>
                        </div>
                        <div class="form-group">
                            <label for="approval_reason">Catatan Persetujuan (Opsional)</label>
                            <textarea name="reason" id="approval_reason" class="form-control" rows="3" placeholder="Tambahkan catatan persetujuan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fal fa-check"></i> Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Penolakan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectionForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <h6><i class="fal fa-exclamation-triangle"></i> Konfirmasi Penolakan</h6>
                            <p class="mb-0">Anda akan menolak kriteria: <strong>{{ $criteria->kriteria }}</strong></p>
                        </div>
                        <div class="form-group">
                            <label for="rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="reason" id="rejection_reason" class="form-control" rows="4" placeholder="Jelaskan alasan penolakan secara detail..." required></textarea>
                            <small class="form-text text-muted">Alasan penolakan akan dikirimkan kepada pembuat kriteria.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fal fa-times"></i> Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('pages-script')
    <script>
        function approveItem(id) {
            $('#approvalForm').attr('action', `/approval/${id}/approve`);
            $('#approvalModal').modal('show');
        }

        function rejectItem(id) {
            $('#rejectionForm').attr('action', `/approval/${id}/reject`);
            $('#rejectionModal').modal('show');
        }
    </script>
@endsection
