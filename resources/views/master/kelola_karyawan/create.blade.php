@extends('inc.main')
@section('title', 'Tambah data karyawan')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'user',
                'heading1' => 'Karyawan',
                'heading2' => 'Baru',
            ])
            @endcomponent
        </div>
        <form action="{{ route('kelola-karyawan.store') }}" method="POST">
            @csrf
            <x-panel.show title="Tambah Data Karyawan">
                <x-slot name="paneltoolbar">
                    <x-panel.tool-bar class="ml-2">
                        <button class="btn btn-toolbar-master" type="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="fal fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-animated dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('kelola-karyawan.index') }}">Kembali</a>
                            {{--
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider m-0"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                        --}}
                        </div>
                    </x-panel.tool-bar>
                </x-slot>
                <div class="form-group">
                    <label for="nama_karyawan">Nama Lengkap</label>
                    <input type="text" name="nama_karyawan" id="nama_karyawan" class="form-control"
                        value="{{ old('nama_karyawan') }}" required>
                </div>

                <div class="form-group">
                    <label for="jeniskelamin">Jenis Kelamin</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jeniskelamin" id="laki_laki_create" value="Laki-laki"
                            {{ old('jeniskelamin') == 'Laki-laki' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="laki_laki_create">
                            Laki-laki
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jeniskelamin" id="perempuan_create" value="Perempuan"
                            {{ old('jeniskelamin') == 'Perempuan' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="perempuan_create">
                            Perempuan
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" class="form-control" required
                        value="{{ old('jabatan') }}">
                </div>
                <div class="form-group">
                    <label for="tanggal_masuk">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" required
                        value="{{ old('tanggal_masuk') }}">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <x-slot name="panelcontentfoot">
                    <x-button type="submit" color="primary" :label="__('Save')" class="ml-auto" />
                </x-slot>
            </x-panel.show>
        </form>
    </main>
@endsection
