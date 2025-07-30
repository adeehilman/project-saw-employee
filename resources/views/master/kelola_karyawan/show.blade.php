@extends('inc.main')
@section('title', 'Karyawan')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Master',
            'category_2' => 'Karyawan',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'user',
                'heading1' => 'Karyawan',
            ])
            @endcomponent
        </div>

        <x-panel.show title="Default" subtitle="Example">
            <x-slot name="paneltoolbar">
                <x-panel.tool-bar>
                    <button class="btn btn-toolbar-master" type="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="fal fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-animated dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('kelola-karyawan.index') }}">Kembali</a>
                    </div>
                </x-panel.tool-bar>
            </x-slot>
            <x-slot name="tagpanel">
                isi-tag-panel
            </x-slot>
            <div class="card">
                <div class="card-body">
                    <p><strong>ID Karyawan:</strong> {{ $dataKaryawan->id_karyawan }}</p>
                    <p><strong>Nama Lengkap:</strong> {{ $dataKaryawan->nama_karyawan }}</p>
                    <p><strong>Jenis Kelamin:</strong> {{ $dataKaryawan->jenis_kelamin }}</p>
                    <p><strong>Jabatan:</strong> {{ $dataKaryawan->jabatan }}</p>
                    <p><strong>Tanggal Masuk:</strong> {{ $dataKaryawan->tanggal_masuk }}</p>
                    <p><strong>Status Aktif:</strong> {{ $dataKaryawan->is_active }}</p>
                    <a href="{{ route('kelola-karyawan.edit', $dataKaryawan) }}" class="btn btn-primary">Edit</a>
                    <form action="{{ route('kelola-karyawan.destroy', $dataKaryawan) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    <a href="{{ route('kelola-karyawan.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </x-panel.show>
    </main>
@endsection
