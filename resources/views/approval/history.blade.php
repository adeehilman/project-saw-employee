@extends('inc.main')
@section('title', 'Riwayat Persetujuan')
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
            'category_2' => 'Riwayat',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'history',
                'heading1' => 'Riwayat',
                'heading2' => 'Persetujuan',
            ])
            @endcomponent
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs" id="historyTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="approved-tab" data-toggle="tab" href="#approved" role="tab">
                    <i class="fal fa-check-circle text-success"></i> Disetujui ({{ $approvedCriteria->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">
                    <i class="fal fa-times-circle text-danger"></i> Ditolak ({{ $rejectedCriteria->count() }})
                </a>
            </li>
        </ul>

        <div class="tab-content" id="historyTabsContent">
            <!-- Approved Criteria Tab -->
            <div class="tab-pane fade show active" id="approved" role="tabpanel">
                <x-panel.show title="Kriteria yang Disetujui" subtitle="Daftar kriteria yang telah mendapat persetujuan">
                    <x-slot name="paneltoolbar">
                        <x-panel.tool-bar>
                            <a href="{{ route('approval.index') }}" class="btn btn-primary btn-sm">
                                <i class="fal fa-arrow-left"></i> Kembali ke Dashboard
                            </a>
                        </x-panel.tool-bar>
                    </x-slot>
                    
                    @if($approvedCriteria->count() > 0)
                        <table id="approved-table" class="table table-bordered table-hover table-striped w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kriteria</th>
                                    <th>Bobot (%)</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Disetujui Oleh</th>
                                    <th>Tanggal Persetujuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($approvedCriteria as $criteria)
                                    <tr>
                                        <td>{{ $criteria->id_kriteria }}</td>
                                        <td>{{ $criteria->kriteria }}</td>
                                        <td>{{ $criteria->bobot }}%</td>
                                        <td>{{ $criteria->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $criteria->approver->name ?? 'N/A' }}</td>
                                        <td>{{ $criteria->approved_at ? $criteria->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('approval.show', $criteria->id_kriteria) }}" class="btn btn-info btn-sm">
                                                <i class="fal fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-5">
                            <i class="fal fa-check-circle text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Belum ada kriteria yang disetujui</h4>
                            <p class="text-muted">Kriteria yang disetujui akan muncul di sini.</p>
                        </div>
                    @endif
                </x-panel.show>
            </div>

            <!-- Rejected Criteria Tab -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <x-panel.show title="Kriteria yang Ditolak" subtitle="Daftar kriteria yang telah ditolak">
                    @if($rejectedCriteria->count() > 0)
                        <table id="rejected-table" class="table table-bordered table-hover table-striped w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kriteria</th>
                                    <th>Bobot (%)</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Ditolak Oleh</th>
                                    <th>Tanggal Penolakan</th>
                                    <th>Alasan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rejectedCriteria as $criteria)
                                    <tr>
                                        <td>{{ $criteria->id_kriteria }}</td>
                                        <td>{{ $criteria->kriteria }}</td>
                                        <td>{{ $criteria->bobot }}%</td>
                                        <td>{{ $criteria->creator->name ?? 'N/A' }}</td>
                                        <td>{{ $criteria->approver->name ?? 'N/A' }}</td>
                                        <td>{{ $criteria->approved_at ? $criteria->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            @if($criteria->rejection_reason)
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $criteria->rejection_reason }}">
                                                    {{ $criteria->rejection_reason }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('approval.show', $criteria->id_kriteria) }}" class="btn btn-info btn-sm">
                                                <i class="fal fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-5">
                            <i class="fal fa-times-circle text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Belum ada kriteria yang ditolak</h4>
                            <p class="text-muted">Kriteria yang ditolak akan muncul di sini.</p>
                        </div>
                    @endif
                </x-panel.show>
            </div>
        </div>
    </main>
@endsection

@section('pages-script')
    <script src="/admin/js/datagrid/datatables/datatables.bundle.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables for each tab
            $('#approved-table').dataTable({
                responsive: true,
                order: [[5, 'desc']] // Sort by approval date
            });

            $('#rejected-table').dataTable({
                responsive: true,
                order: [[5, 'desc']] // Sort by rejection date
            });

            // Handle tab switching
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Redraw DataTables when tab is shown to fix column widths
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });
        });
    </script>
@endsection
