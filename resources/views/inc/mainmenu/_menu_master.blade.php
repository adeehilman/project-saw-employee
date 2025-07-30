<li class="{{ Request::is('admin/profil_admin*') ? 'active' : '' }}">
    <a href="{{ route('profil_admin') }}" title="Profil Admin" data-filter-tags="admin profil">
        <i class="fal fa-user-circle"></i>
        <span class="nav-link-text" data-i18n="nav.admin_profil">Profil Admin</span>
    </a>
</li>
<li class="nav-title">Master</li>
<li class="{{ Request::is('tools/*') ? 'active open' : '' }}">
    <a href="#" title="Tools" data-filter-tags="tools">
        <i class="fal fa-cogs"></i>
        <span class="nav-link-text" data-i18n="nav.tools">Tools</span>
    </a>
    <ul>
        <li class="{{ Request::is('tools/app_profiles*') ? 'active' : '' }}">
            <a href="/tools/app_profiles" title="App Profiles" data-filter-tags="tools app profiles">
                <span class="nav-link-text" data-i18n="nav.tools_app_profiles">Profil Aplikasi</span>
            </a>
        </li>
    </ul>
</li>

{{-- after --}}
{{-- TODO: Delete the comment if not needed --}}
@if (auth()->user()->role == 'Admin')
    <li class="{{ Request::is('karyawan/*') ? 'active open' : '' }}">
        <a href="#" title="Karyawan" data-filter-tags="Karyawan">
            <i class="fal fa-briefcase"></i>
            <span class="nav-link-text" data-i18n="nav.Karyawan">Karyawan</span>
        </a>
        <ul>
            <li class="{{ Request::is('karyawan/kelola-karyawan*') ? 'active' : '' }}">
                <a href="{{ route('kelola-karyawan.index') }}" title="Kelola Karyawan"
                    data-filter-tags="karyawan kelola karyawan">
                    <span class="nav-link-text" data-i18n="nav.karyawan_kelola_karyawan">Kelola Karyawan</span>
                </a>
            </li>
        </ul>
    </li>
@endif

<li class="{{ Request::is('penilaian/*') ? 'active open' : '' }}">
    <a href="#" title="Penilaian" data-filter-tags="Penilaian">
        <i class="fal fa-balance-scale"></i>
        <span class="nav-link-text" data-i18n="nav.Penilaian">Penilaian dan Kinerja</span>
    </a>
    <ul>
        @if (auth()->user()->role == 'Admin')
            <li class="{{ Request::is('penilaian/kriteria-bobot*') ? 'active' : '' }}">
                <a href="{{ route('kriteria_bobot.index') }}" title="Kriteria dan Bobot Penilaian"
                    data-filter-tags="kriteria dan bobot penilaian">
                    <span class="nav-link-text" data-i18n="nav.kriteria_dan_penilaian">Kriteria dan Bobot
                        Penilaian</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->role == 'Pemimpin Perusahaan')
            <li class="{{ Request::is('penilaian-karyawan/hasil-penilaian*') ? 'active' : '' }}">
                <a href="{{ route('results.index') }}" title="Hasil Penilaian"
                    data-filter-tags="hasil penilaian ranking saw">
                    <span class="nav-link-text" data-i18n="nav.hasil_penilaian">Hasil Penilaian</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->role == 'Admin')
            <li class="{{ Request::is('penilaian-karyawan*') ? 'active' : '' }}">
                <a href="{{ route('penilaian_karyawan.index') }}" title="Penilaian Karyawan"
                    data-filter-tags="penilaian karyawan nilai">
                    <span class="nav-link-text" data-i18n="nav.penilaian_karyawan_scoring">Penilaian Karyawan</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->role == 'Pemimpin Perusahaan')
            @include('inc.mainmenu._menu_pemimpinperusahaan')
        @endif
    </ul>
</li>
