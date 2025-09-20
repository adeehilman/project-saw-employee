@extends('inc.main')
@section('title', 'Detail Kriteria dan Bobot')
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
                'heading1' => 'Kriteria',
                'heading2' => 'dan Bobot',
            ])
            @endcomponent
        </div>

        <x-panel.show title="Detail" subtitle="Kriteria dan Bobot">
            <x-slot name="paneltoolbar">
                <x-panel.tool-bar>
                    <button class="btn btn-toolbar-master" type="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="fal fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-animated dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('kriteria_bobot.index') }}">Kembali</a>
                    </div>
                </x-panel.tool-bar>
            </x-slot>
            @if ($dataKriteria->status == 'Disetujui')
                <x-slot name="tagpanel">
                Kriteria dan bobot yang telah disetujui tidak dapat diubah.
            </x-slot>
            @endif
            <div class="card">
                <div class="card-body">
                    <p><strong>Kriteria:</strong> {{ $dataKriteria->kriteria }}</p>
                    <p><strong>Bobot:</strong> {{ $dataKriteria->bobot }}</p>
                    <a href="{{ route('kriteria_bobot.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </x-panel.show>
    </main>
@endsection

