<aside class="page-sidebar">
    <div class="page-logo">
        <a href="#" class="page-logo-link press-scale-down d-flex align-items-center position-relative"
            data-toggle="modal" data-target="#modal-shortcut">
            <img src="/admin/img/logoapp.jpg{{ $profileApp->app_logo ?? '' }}" alt="{{ $profileApp->app_nama ?? '' }} WebApp"
                aria-roledescription="logo">
            <span class="page-logo-text mr-1">Sistem Penilaian Karyawan</span>
            <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
            <i class="fal fa-angle-down d-inline-block ml-1 fs-lg color-primary-300"></i>
        </a>
    </div>
    <!-- BEGIN PRIMARY NAVIGATION -->
    <nav id="js-primary-nav" class="primary-nav" role="navigation">
        <div class="nav-filter">
            <div class="position-relative">
                <input type="text" id="nav_filter_input" placeholder="Filter menu" class="form-control"
                    tabindex="0">
                <a href="#" onclick="return false;" class="btn-primary btn-search-close js-waves-off"
                    data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar">
                    <i class="fal fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="info-card">
            @if (auth()->user()->image)
                <img src="/admin/img/users/{{ auth()->user()->image }}" class="profile-image rounded-circle"
                    alt="{{ auth()->user()->name }}">
            @else
                <img src="/admin/img/users/user.jpg" class="profile-image rounded-circle"
                    alt="{{ auth()->user()->name }}">
            @endif
            <div class="info-card-text">
                <a href="#" class="d-flex align-items-center text-white">
                    <span class="text-truncate text-truncate-sm d-inline-block">
                        {{ auth()->user()->name }}
                    </span>
                </a>
                <span class="d-inline-block text-truncate text-truncate-sm">{{ auth()->user()->role }}</span>
            </div>
            <img src="/admin/img/card-backgrounds/cover-2-lg.png" class="cover" alt="cover">
            <a href="#" onclick="return false;" class="pull-trigger-btn" data-action="toggle"
                data-class="list-filter-active" data-target=".page-sidebar" data-focus="nav_filter_input">
                <i class="fal fa-angle-down"></i>
            </a>
        </div>

        @include('inc._nav_menu')

        <div class="filter-message js-filter-message bg-success-600"></div>
    </nav>
    <!-- END PRIMARY NAVIGATION -->
    <!-- NAV FOOTER -->
    <div class="nav-footer shadow-top d-flex align-items-center justify-content-center">
        <form id="logout-form" action="/logout" method="post">
            @csrf
            <button type="button" id="ya-atau-tidak" class="btn btn-link" data-title="Konfirmasi" data-message="Apakah Anda yakin ingin logout?">
                <i class="fal fa-sign-out" style="font-size: 1.5rem;"></i>
            </button>
        </form>
    </div> <!-- END NAV FOOTER -->
</aside>
