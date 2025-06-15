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
                                    <span class="badge badge-secondary">{{ $kriteria->status }}</span>
                                @elseif ($kriteria->status == 'Ditolak')
                                    <span class="badge badge-danger">{{ $kriteria->status }}</span>
                                @elseif ($kriteria->status == 'Disetujui')
                                    <span class="badge badge-success">{{ $kriteria->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($kriteria->status != 'Disetujui')
                                    <a href="{{ route('kriteria_bobot.edit', $kriteria->id_kriteria) }}"
                                        class="btn btn-warning">Edit</a>
                                    <form action="{{ route('kriteria_bobot.destroy', $kriteria->id_kriteria) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $kriteria->id_kriteria }})">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-panel.show>
    </main>
@endsection
@section('pages-script')
    <script>
        function confirmDelete(id) {
            bootbox.confirm({
                message: "Apakah yakin akan di hapus kompetensi keahlian ini?",
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
