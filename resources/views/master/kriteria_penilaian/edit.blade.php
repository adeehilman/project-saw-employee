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

        <form action="{{ route('kriteria_bobot.update', $dataGuru) }}" method="POST">
            @csrf
            @method('PUT')
            <x-panel.show title="Default" subtitle="Example">
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
                        value="{{ old('kriteria', $dataGuru->kriteria) }}" required>
                </div>

                <div class="form-group">
                    <label for="bobot">Bobot</label>
                    <input type="number" name="bobot" id="bobot" class="form-control" min="1" max="100"
                        value="{{ old('bobot', $dataGuru->bobot) }}" required>
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
