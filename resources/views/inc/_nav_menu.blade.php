<ul id="js-nav-menu" class="nav-menu">
    <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
        <a href="/dashboard" title="Dashboard" data-filter-tags="application dashboard">
            <i class="fal fa-home"></i>
            <span class="nav-link-text" data-i18n="nav.application_dashboard">Dashboard</span>
        </a>
    </li>

    @if (auth()->user()->role == 'Karyawan')
        
        <li class="{{ Request::is('hasil-penilaian-saya*') ? 'active' : '' }}">
            <a href="{{ route('employee.my_results') }}" title="Lihat Hasil Penilaian Saya"
                data-filter-tags="hasil penilaian saya karyawan">
                <span class="nav-link-text" data-i18n="nav.hasil_penilaian_saya">Lihat Hasil Penilaian</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->role == 'Admin' || auth()->user()->role == 'Pemimpin Perusahaan')
        @include('inc.mainmenu._menu_master')
    @endif
    <div class="m-0 w-100 p-2"></div>
    <li class="{{ Request::is('about') ? 'active' : '' }}">
        <a href="/about" title="About" data-filter-tags="application about">
            <i class="fal fa-info-circle"></i>
            <span class="nav-link-text" data-i18n="nav.application_about">About</span>
        </a>
    </li>
</ul>
