@extends('inc.main')
@section('title', 'Dashboard Persetujuan')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/datagrid/datatables/datatables.bundle.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Persetujuan',
            'category_2' => 'Dashboard',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'check-circle',
                'heading1' => 'Dashboard',
                'heading2' => 'Persetujuan',
            ])
            @endcomponent
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-primary-300 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            {{ $stats['Menunggu'] }}
                            <small class="m-0 l-h-n">Menunggu</small>
                        </h3>
                    </div>
                    <i class="fal fa-clock position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-success-200 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            {{ $stats['Disetujui'] }}
                            <small class="m-0 l-h-n">Disetujui</small>
                        </h3>
                    </div>
                    <i class="fal fa-check-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-danger-200 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            {{ $stats['Ditolak'] }}
                            <small class="m-0 l-h-n">Ditolak</small>
                        </h3>
                    </div>
                    <i class="fal fa-times-circle position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="p-3 bg-info-200 rounded overflow-hidden position-relative text-white mb-g">
                    <div class="">
                        <h3 class="display-4 d-block l-h-n m-0 fw-500">
                            {{ $stats['Menunggu'] + $stats['Disetujui'] + $stats['Ditolak'] }}
                            <small class="m-0 l-h-n">Total</small>
                        </h3>
                    </div>
                    <i class="fal fa-list position-absolute pos-right pos-bottom opacity-15 mb-n1 mr-n1" style="font-size: 6rem"></i>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <x-panel.show title="Kriteria Menunggu Persetujuan" subtitle="Daftar kriteria yang memerlukan persetujuan">
            <x-slot name="paneltoolbar">
                <x-panel.tool-bar>
                    @if($pendingApprovals->count() > 0)
                        <button class="btn btn-success btn-sm" onclick="showBulkApproveModal()">
                            <i class="fal fa-check"></i> Setujui Semua
                        </button>
                        <button class="btn btn-danger btn-sm ml-2" onclick="showBulkRejectModal()">
                            <i class="fal fa-times"></i> Tolak Semua
                        </button>
                    @endif
                    <a href="{{ route('approval.history') }}" class="btn btn-info btn-sm ml-2">
                        <i class="fal fa-history"></i> Riwayat
                    </a>
                </x-panel.tool-bar>
            </x-slot>
            
            @if($pendingApprovals->count() > 0)
                <form id="bulkActionForm">
                    @csrf
                    <table id="dt-basic-example" class="table table-bordered table-hover table-striped w-100">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>ID</th>
                                <th>Kriteria</th>
                                <th>Bobot (%)</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal Submit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingApprovals as $criteria)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="criteria_ids[]" value="{{ $criteria->id_kriteria }}" class="form-check-input criteria-checkbox">
                                    </td>
                                    <td>{{ $criteria->id_kriteria }}</td>
                                    <td>{{ $criteria->kriteria }}</td>
                                    <td>{{ $criteria->bobot }}%</td>
                                    <td>{{ $criteria->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $criteria->submitted_at ? $criteria->submitted_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('approval.show', $criteria->id_kriteria) }}" class="btn btn-info btn-sm">
                                            <i class="fal fa-eye"></i> Detail
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm" onclick="approveItem('{{ $criteria->id_kriteria }}')">
                                            <i class="fal fa-check"></i> Setujui
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="rejectItem('{{ $criteria->id_kriteria }}')">
                                            <i class="fal fa-times"></i> Tolak
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            @else
                <div class="text-center py-4">
                    <i class="fal fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Tidak ada kriteria yang menunggu persetujuan</h4>
                    <p class="text-muted">Semua kriteria telah diproses.</p>
                </div>
            @endif
        </x-panel.show>

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
                        <p>Apakah Anda yakin ingin menyetujui kriteria ini?</p>
                        <div class="form-group">
                            <label for="approval_reason">Catatan (Opsional)</label>
                            <textarea name="reason" id="approval_reason" class="form-control" rows="3" placeholder="Tambahkan catatan persetujuan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui</button>
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
                        <p>Apakah Anda yakin ingin menolak kriteria ini?</p>
                        <div class="form-group">
                            <label for="rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="reason" id="rejection_reason" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Approve Modal -->
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Kriteria Terpilih</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="bulkApproveForm" method="POST" action="{{ route('approval.bulk-approve') }}">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menyetujui semua kriteria yang dipilih?</p>
                        <div class="form-group">
                            <label for="bulk_approval_reason">Catatan (Opsional)</label>
                            <textarea name="reason" id="bulk_approval_reason" class="form-control" rows="3" placeholder="Tambahkan catatan persetujuan..."></textarea>
                        </div>
                        <div id="selectedCriteriaList"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Setujui Semua</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Reject Modal -->
    <div class="modal fade" id="bulkRejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Kriteria Terpilih</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="bulkRejectForm" method="POST" action="{{ route('approval.bulk-reject') }}">
                    @csrf
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menolak semua kriteria yang dipilih?</p>
                        <div class="form-group">
                            <label for="bulk_rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="reason" id="bulk_rejection_reason" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                        </div>
                        <div id="selectedCriteriaListReject"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Semua</button>
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
            $('#dt-basic-example').dataTable({
                responsive: true,
                order: [[5, 'desc']] // Sort by submission date
            });

            // Select all checkbox functionality
            $('#selectAll').change(function() {
                $('.criteria-checkbox').prop('checked', this.checked);
            });

            $('.criteria-checkbox').change(function() {
                if (!this.checked) {
                    $('#selectAll').prop('checked', false);
                } else if ($('.criteria-checkbox:checked').length === $('.criteria-checkbox').length) {
                    $('#selectAll').prop('checked', true);
                }
            });
        });

        function approveItem(id) {
            $('#approvalForm').attr('action', `/approval/${id}/approve`);
            $('#approvalModal').modal('show');
        }

        function rejectItem(id) {
            $('#rejectionForm').attr('action', `/approval/${id}/reject`);
            $('#rejectionModal').modal('show');
        }

        function showBulkApproveModal() {
            const selected = $('.criteria-checkbox:checked');
            if (selected.length === 0) {
                alert('Pilih minimal satu kriteria untuk disetujui.');
                return;
            }

            // Copy selected checkboxes to bulk form
            $('#selectedCriteriaList').empty();
            selected.each(function() {
                $('#selectedCriteriaList').append(`<input type="hidden" name="criteria_ids[]" value="${this.value}">`);
            });

            $('#bulkApproveModal').modal('show');
        }

        function showBulkRejectModal() {
            const selected = $('.criteria-checkbox:checked');
            if (selected.length === 0) {
                alert('Pilih minimal satu kriteria untuk ditolak.');
                return;
            }

            // Copy selected checkboxes to bulk form
            $('#selectedCriteriaListReject').empty();
            selected.each(function() {
                $('#selectedCriteriaListReject').append(`<input type="hidden" name="criteria_ids[]" value="${this.value}">`);
            });

            $('#bulkRejectModal').modal('show');
        }
    </script>
@endsection
