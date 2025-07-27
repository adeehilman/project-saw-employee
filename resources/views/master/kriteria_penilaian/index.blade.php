@extends('inc.main')
@section('title', 'Kriteria dan Bobot Penilaian')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/datagrid/datatables/datatables.bundle.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
        ])
        <div class="subheader">
            @component('inc._page_heading', [
            ])
            @endcomponent
        </div>
        <x-panel.show title="Daftar" subtitle="Kriteria dan Bobot Penilaian">
            <x-slot name="paneltoolbar">
                <x-panel.tool-bar>
                    <a href="{{ route('kriteria_bobot.create') }}" class="btn btn-primary btn-sm">Tambah Data</a>
                </x-panel.tool-bar>
            </x-slot>
            <table id="dt-basic-example" class="table table-bordered table-hover table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kriteria</th>
                        <th>Bobot %</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($KriteriaBobots as $kriteria)
                        <tr>
                            <td>{{ $kriteria->id_kriteria }}</td>
                            <td>{{ $kriteria->kriteria }}</td>
                            <td>{{ $kriteria->bobot }}</td>
                            <td>
                                @if ($kriteria->status == 'Menunggu')
                                    <span class="badge badge-warning">Menunggu Persetujuan</span>
                                @elseif ($kriteria->status == 'Ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @elseif ($kriteria->status == 'Disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @else
                                    <span class="badge badge-secondary">{{ $kriteria->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('kriteria_bobot.show', $kriteria->id_kriteria) }}" class="btn btn-info btn-sm">
                                    <i class="fal fa-eye"></i> Detail
                                </a>
                                @if ($kriteria->status != 'Disetujui')
                                    <a href="{{ route('kriteria_bobot.edit', $kriteria->id_kriteria) }}" class="btn btn-warning btn-sm">
                                        <i class="fal fa-edit"></i> Edit
                                    </a>
                                    <form id="delete-form-{{ $kriteria->id_kriteria }}" action="{{ route('kriteria_bobot.destroy', $kriteria->id_kriteria) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $kriteria->id_kriteria }}')">
                                            <i class="fal fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">
                                        <i class="fal fa-lock"></i> Sudah disetujui
                                    </span>
                                @endif
                                @if ($kriteria->status == 'Ditolak' && $kriteria->rejection_reason)
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="showRejectionReason('{{ addslashes($kriteria->rejection_reason) }}')">
                                        <i class="fal fa-exclamation-triangle"></i> Alasan Penolakan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-panel.show>
    </main>

    <!-- Rejection Reason Modal -->
    <div class="modal fade" id="rejectionReasonModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alasan Penolakan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6><i class="fal fa-exclamation-triangle"></i> Kriteria Ditolak</h6>
                        <p id="rejectionReasonText" class="mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('pages-script')
    <script>
        function confirmDelete(id) {
            console.log("id", id);
            bootbox.confirm({
                message: "Apakah yakin akan di hapus kriteria ini?",
                buttons: {
                    confirm: {
                        label: 'Yes',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'No',
                        className: 'btn-secondary'
                    }
                },
                callback: function(result) {
                    if (result) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                }
            });
        }

        function showRejectionReason(reason) {
            $('#rejectionReasonText').text(reason);
            $('#rejectionReasonModal').modal('show');
        }
    </script>
    <script src="/admin/js/datagrid/datatables/datatables.bundle.js"></script>
    <script>
        /* demo scripts for change table color */
        /* change background */
        $(document).ready(function() {
            $('#dt-basic-example').dataTable({
                responsive: true
            });

            $('.js-thead-colors a').on('click', function() {
                var theadColor = $(this).attr("data-bg");
                console.log(theadColor);
                $('#dt-basic-example thead').removeClassPrefix('bg-').addClass(theadColor);
            });

            $('.js-tbody-colors a').on('click', function() {
                var theadColor = $(this).attr("data-bg");
                console.log(theadColor);
                $('#dt-basic-example').removeClassPrefix('bg-').addClass(theadColor);
            });

        });
    </script>
@endsection
