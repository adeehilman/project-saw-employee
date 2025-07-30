@extends('inc.main_auth')
@section('title', 'Sistem Penilaian Kinerja Karyawan - SAW Method')
@section('pages-css')
    <link rel="stylesheet" media="screen, print" href="/admin/css/fa-brands.css">
@endsection
@section('pages-content')
    @component('inc._auth_header')
        <a href="/login" class="btn btn-primary text-white ml-auto">
            Login
        </a>
    @endcomponent

    <!-- Logout Success Message -->
    @if(session('logout_success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fal fa-check-circle mr-2"></i>
                <strong>Logout Berhasil!</strong> {{ session('logout_success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif
    <div class="flex-1"
        style="background: url(/admin/img/svg/pattern-1.svg) no-repeat center bottom fixed; background-size: cover;">
        <div class="container py-4 py-lg-5 my-lg-5 px-4 px-sm-0">
            <div class="row">
                <div class="col col-md-6 col-lg-7">
                    <h2 class="fs-xxl fw-500 mt-4 text-white">
                        SISTEM PENILAIAN KINERJA KARYAWAN
                        <small class="h3 fw-300 mt-3 mb-5 text-white opacity-60">
                            Sistem penilaian kinerja menggunakan metode SAW (Simple Additive Weighting) untuk evaluasi objektif dan transparan <br>
                            <a href="https://en.wikipedia.org/wiki/Multi-criteria_decision_analysis"
                                class="fs-lg fw-500 text-white opacity-70" target="_blank">pelajari lebih lanjut &gt;&gt;</a>
                        </small>
                    </h2>
                    <p class="text-white opacity-50">Aplikasi Penilaian Kinerja dengan <a
                            href="https://en.wikipedia.org/wiki/Simple_additive_weighting" class="text-white opacity-70">Metode SAW</a> <br>
                        Untuk evaluasi kinerja karyawan yang objektif dan terukur dengan sistem ranking otomatis.
                    </p>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-5 col-xl-4 ml-auto hidden-sm-down">
                    <div class="py-4">
                        <img src="/admin/img/siswakumpul.png" class="display-3 img-responsive" height="400"
                            alt="Employee Assessment System">
                    </div>
                </div>
            </div>
            <div class="position-absolute pos-bottom pos-left pos-right p-3 text-center text-white">
                {{ $profileApp->app_tahun ?? '' }} - @php echo date('Y'); @endphp © {{ $profileApp->app_pengembang ?? '' }}
                by&nbsp;<a href="https://laravel.com/docs/10.x" title='laravel.com' class="btn-link font-weight-bold"
                    target="_blank">Laravel
                    v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</a>
            </div>
        </div>
    </div>
@endsection

@section('pages-script')
    <script>
        // Auto-hide logout success message after 5 seconds
        $(document).ready(function() {
            @if(session('logout_success'))
                setTimeout(function() {
                    $('.alert-success').fadeOut('slow');
                }, 5000);
            @endif
        });
    </script>
@endsection
