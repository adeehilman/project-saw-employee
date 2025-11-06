@extends('inc.main')
@section('title', 'Kriteria dan Bobot')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', [
            'category_1' => 'Penilaian dan Kinerja',
        ])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'user',
                'heading1' => 'Kriteria dan ',
                'heading2' => 'Bobot',
            ])
            @endcomponent
        </div>

        <form action="{{ route('kriteria_bobot.update', $dataKriteria) }}" method="POST">
            @csrf
            @method('PUT')
            <x-panel.show title="Edit" subtitle="Kriteria dan Bobot">
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
                <div class="form-group">
                    <label for="kriteria">Kriteria</label>
                    <input type="text" name="kriteria" id="kriteria" class="form-control"
                        value="{{ old('kriteria', $dataKriteria->kriteria) }}" required>
                </div>

                <div class="form-group">
                    <label for="bobot">Bobot</label>
                    <input
                        type="number"
                        name="bobot"
                        id="bobot"
                        class="form-control"
                        min="1"
                        value="{{ old('bobot', $dataKriteria->bobot) }}"
                        required
                        {{-- simpan meta untuk JS --}}
                        data-bobot-awal="{{ (int) $dataKriteria->bobot }}"
                        data-available-pool="{{ (int) $availableBobot }}"
                    >
                    <div class="alert alert-danger mt-2" role="alert" id="alert-remaining">
                        <strong id="remaining-label">
                            Sisa bobot yang tersedia adalah {{ (int) $availableBobot }}.
                        </strong>
                    </div>
                </div>
                <x-slot name="panelcontentfoot">
                    <x-button type="submit" color="primary" :label="__('Update')" class="ml-auto" />
                </x-slot>
            </x-panel.show>
        </form>
    </main>
@endsection
@section('pages-script')
<script>

(function () {
    const input = document.getElementById('bobot');
    const remainingLabel = document.getElementById('remaining-label');

    // Ambil angka dasar dari server
    const bobotAwal = Number(input.dataset.bobotAwal || 0);          // bobot item saat ini (sebelum edit)
    const availablePool = Number(input.dataset.availablePool || 0);   // sisa kuota di luar item ini

    // Max legal = bobotAwal + availablePool
    const maxAllowed = Math.max(1, bobotAwal + availablePool);
    input.setAttribute('max', String(maxAllowed));

    // Helper untuk update label & clamp
    function updateState() {
        let val = Number(input.value);

        // Normalisasi NaN dan batas bawah/atas
        if (!Number.isFinite(val) || val < 1) val = 1;
        if (val > maxAllowed) val = maxAllowed;

        // Hitung sisa pool setelah perubahan terhadap bobotAwal
        const delta = val - bobotAwal;                 // berapa banyak kamu "memakan" pool
        const remaining = Math.max(0, availablePool - Math.max(0, delta));

        // Tampilkan
        input.value = val; // tulis kembali jika ter-clamp
        remainingLabel.textContent = `Sisa bobot yang tersedia adalah ${remaining}.`;
    }

    // Inisialisasi awal (pastikan label konsisten dengan nilai awal/old)
    updateState();

    // Reaktif saat user mengetik / scroll number input
    input.addEventListener('input', updateState);
    input.addEventListener('change', updateState);
})();

 function confirmDelete(id) {
            bootbox.confirm({
                message: "Apakah yakin akan di edit Kriteria dan Bobot ini?",
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
@endsection
