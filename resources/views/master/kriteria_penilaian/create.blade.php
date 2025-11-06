@extends('inc.main')
@section('title', 'Tambah Kriteria ')
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
        <form action="{{ route('kriteria_bobot.store') }}" method="POST">
            @csrf
            <x-panel.show title="Tambah Data Kriteria dan Bobot">
                <x-slot name="paneltoolbar">
                    <x-panel.tool-bar class="ml-2">
                        <button class="btn btn-toolbar-master" type="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="fal fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-animated dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('kriteria_bobot.index') }}">Kembali</a>
                            {{--
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider m-0"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                        --}}
                        </div>
                    </x-panel.tool-bar>
                </x-slot>
                <div class="form-group">
                    <label for="kriteria">Kriteria</label>
                    <input type="text" name="kriteria" id="kriteria" class="form-control"
                        value="{{ old('kriteria') }}" required>
                </div>

                <div class="form-group">
                    <label for="bobot">Bobot</label>
                    <input type="number" name="bobot" id="bobot" class="form-control" min="1" max="100" value="{{ old('bobot') }}" required
                        data-bobot-awal="{{ (int) $availableBobot }}"
                    >
                    <div class="alert alert-danger mt-2" role="alert" id="alert-remaining">
                        <strong id="remaining-label">
                            Sisa bobot yang tersedia adalah {{ (int) $availableBobot }}.
                        </strong>
                    </div>
                </div>
                <script>
                    const input = document.getElementById('bobot');
                    const remainingLabel = document.getElementById('remaining-label');

                    function updateState() {
                        let val = Number(input.value);

                        if (!Number.isFinite(val) || val < 1) val = 1;
                        if (val > {{ (int) $availableBobot }}) val = {{ (int) $availableBobot }};

                        // Hitung sisa pool setelah perubahan terhadap bobotAwal
                        const delta = val - {{ (int) $availableBobot }};
                        const remaining = Math.max(0, {{ (int) $availableBobot }} - Math.max(0, delta));

                        // Tampilkan
                        input.value = val; // tulis kembali jika ter-clamp
                        remainingLabel.textContent = `Sisa bobot yang tersedia adalah ${remaining}.`;
                    }

                    // Inisialisasi awal (pastikan label konsisten dengan nilai awal/old)
                    updateState();

                    // Reaktif saat user mengetik / scroll number input
                    input.addEventListener('input', updateState);
                </script>
                <x-slot name="panelcontentfoot">
                    <x-button type="submit" color="primary" :label="__('Save')" class="ml-auto" />
                </x-slot>
            </x-panel.show>
        </form>
    </main>
@endsection
