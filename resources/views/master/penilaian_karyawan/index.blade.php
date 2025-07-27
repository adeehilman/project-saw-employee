@extends('inc.main')
@section('title', 'Penilainan Karyawan')
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
        <x-panel.show title="Penilaian " subtitle="Karyawan">
            <x-slot name="paneltoolbar">
                {{-- <x-panel.tool-bar>
                    <a href="{{ route('kelola_karyawan.create') }}" class="btn btn-primary btn-sm"></a>
                </x-panel.tool-bar> --}}
            </x-slot>
            <table id="dt-basic-example" class="table table-bordered table-hover table-striped w-100">
                <thead>
                    <tr>

                        <th>Nama Lengkap</th>
                        <th>Jenis Kelamin</th>
                        <th>Jabatan</th>
                        <th>Tanggal masuk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($PenilaianKaryawans as $karyawan)
                        <tr>

                            <td>{{ $karyawan->nama_karyawan }}</td>
                            <td>{{ $karyawan->jenis_kelamin }}</td>
                            <td>{{ $karyawan->jabatan }}</td>
                            <td>{{ $karyawan->tanggal_masuk }}</td>
                            <td>
                                    {{-- <a href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailModal-{{ $karyawan->id }}">
                                        <i class="fas fa-eye"></i>
                                    </a> --}}
                                    <a href="#" class="btn btn-info btn-sm"
                                        data-toggle="modal"
                                        data-target="#detailModal-{{ $karyawan->id }}"
                                        data-id="{{ $karyawan->id }}">
                                        <i class="fas fa-eye"></i>
                                        </a>


                                    <!-- Modal -->
                                    <div class="modal fade" id="detailModal-{{ $karyawan->id }}" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel-{{ $karyawan->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="detailModalLabel-{{ $karyawan->id }}">Beri Nilai</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Add the details you want to show in the modal -->
                                                    <p>Nama: {{ $karyawan->nama_karyawan }}</p>
                                                    <p>Jenis Kelamin: {{ $karyawan->jenis_kelamin }}</p>
                                                    <p>Jabatan: {{ $karyawan->jabatan }}</p>
                                                    <p>Tanggal Masuk: {{ $karyawan->tanggal_masuk }}</p>
                                                    <!-- You can add more fields as needed -->
                                                    <table class="table table-bordered table-hover table-striped w-100">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Kriteria</th>
                                                                <th>Bobot %</th>
                                                                <th>Nilai</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="penilaian-body-{{ $karyawan->id }}">
                                                            <tr>
                                                                <td colspan="4" class="text-center">Loading...</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>


                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
                message: "Apakah yakin akan di hapus karyawan ini?",
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


        $(document).ready(function () {
            $('a[data-toggle="modal"]').on('click', function () {
                var karyawanId = $(this).data('id');
                var targetBody = $('#penilaian-body-' + karyawanId);
                targetBody.html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');

                $.ajax({
                    url: '/karyawan_nilai/getNilaiKaryawan/' + karyawanId,
                    type: 'GET',
                    success: function (data) {
                        if (data.length > 0) {
                            let rows = '';
                            data.forEach((item, index) => {
                                rows += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${item.nama_kriteria}</td>
                                        <td>${item.bobot}%</td>
                                        <td>${item.nilai}</td>
                                    </tr>
                                `;
                            });
                            targetBody.html(rows);
                        } else {
                            targetBody.html('<tr><td colspan="4" class="text-center">Data tidak ditemukan</td></tr>');
                        }
                    },
                    error: function () {
                        targetBody.html('<tr><td colspan="4" class="text-danger text-center">Gagal memuat data</td></tr>');
                    }
                });
            });
        });

    </script>
@endsection
