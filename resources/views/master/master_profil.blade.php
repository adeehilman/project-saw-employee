@extends('inc.main')
@section('title', 'Profil Master')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-solid.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/theme-demo.css">
    <link rel="stylesheet" media="screen, print" href="/admin/css/notifications/toastr/toastr.css">
@endsection
@section('pages-content')
    <main id="js-page-content" role="main" class="page-content">
        @include('inc._page_breadcrumb', ['category_1' => 'Master'])
        <div class="subheader">
            @component('inc._page_heading', [
                'icon' => 'user-circle',
                'heading1' => 'Profil',
                'heading2' => 'Admin',
            ])
            @endcomponent
        </div>
        <x-row-column :column="1">
            <x-slot name='column1'>
                <x-panel.show title="Profil" subtitle="{{ auth()->user()->role }}">
                    <x-slot name="paneltoolbar">
                        <x-panel.tool-bar class="ml-2">
                            <button class="btn btn-toolbar-master" type="button" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fal fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-animated dropdown-menu-right">
                                <a class="dropdown-item" href="/dashboard">Kembali</a>
                            </div>
                        </x-panel.tool-bar>
                    </x-slot>
                    <div class="row no-gutters row-grid">
                        <div class="col-12">
                            <div class="d-flex flex-column align-items-center justify-content-center p-4">
                                <img src="/admin/img/user.jpg" class="rounded-circle shadow-2 img-thumbnail" alt="">
                                <h5 class="mb-0 fw-700 text-center mt-3">
                                    {{ auth()->user()->name }}
                                    <small class="text-muted mb-0">{{ auth()->user()->email }}</small>
                                </h5>

                            </div>
                        </div>
                    </div>
                </x-panel.show>
            </x-slot>
        </x-row-column>
    </main>
@endsection
