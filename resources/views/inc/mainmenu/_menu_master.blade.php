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
        <li class="{{ Request::is('tools/app_fiturs*') ? 'active' : '' }}">
            <a href="/tools/app_fiturs" title="App Fiturs" data-filter-tags="tools app fiturs">
                <span class="nav-link-text" data-i18n="nav.tools_app_fiturs">Fitur Aplikasi</span>
            </a>
        </li>
        <li class="{{ Request::is('tools/impor_data_master*') ? 'active' : '' }}">
            <a href="{{ route('impor_data_master') }}" title="Impor Data Master"
                data-filter-tags="tools impor data master">
                <span class="nav-link-text" data-i18n="nav.tools_impor_data_master">Impor Data Master</span>
            </a>
        </li>
        <li class="{{ Request::is('tools/ekspor_data_master*') ? 'active' : '' }}">
            <a href="{{ route('ekspor_data_master') }}" title="Ekspor Data Master"
                data-filter-tags="tools ekspor data master">
                <span class="nav-link-text" data-i18n="nav.tools_ekspor_data_master">Ekspor Data Master</span>
            </a>
        </li>
        <li class="{{ Request::is('tools/backup_database*') ? 'active' : '' }}">
            <a href="{{ route('backup_database') }}" title="Backup Database" data-filter-tags="tools backup database">
                <span class="nav-link-text" data-i18n="nav.tools_backup_database">Backup Database</span>
            </a>
        </li>
        <li class="{{ Request::is('tools/data_login*') ? 'active' : '' }}">
            <a href="{{ route('data_login') }}" title="Data Login" data-filter-tags="tools data login">
                <span class="nav-link-text" data-i18n="nav.tools_data_login">Data Login</span>
            </a>
        </li>
    </ul>
</li>

{{-- after --}}
{{-- TODO: Delete the comment if not needed --}}
<li class="{{ Request::is('karyawan/*') ? 'active open' : '' }}">
    <a href="#" title="Karyawan" data-filter-tags="Karyawan">
        <i class="fal fa-briefcase"></i>
        <span class="nav-link-text" data-i18n="nav.Karyawan">Karyawan</span>
    </a>
    <ul>
        <li class="{{ Request::is('karyawan/kelola_karyawan*') ? 'active' : '' }}">
            <a href="{{ route('kelola_karyawan.index') }}" title="Kelola Karyawan"
                data-filter-tags="karyawan kelola karyawan">
                <span class="nav-link-text" data-i18n="nav.karyawan_kelola_karyawan">Kelola Karyawan</span>
            </a>
        </li>
         <li class="{{ Request::is('karyawan/karyawan_nilai*') ? 'active' : '' }}">
            <a href="{{ route('karyawan_nilai.index') }}" title="Kelola Karyawan"
                data-filter-tags="Penilaian Karyawan">
                <span class="nav-link-text" data-i18n="nav.penilaian_karyawan">Penilaian Karyawan</span>
            </a>
        </li>
    </ul>
</li>

<li class="{{ Request::is('penilaian/*') ? 'active open' : '' }}">
    <a href="#" title="Penilaian" data-filter-tags="Penilaian">
        <i class="fal fa-balance-scale"></i>
        <span class="nav-link-text" data-i18n="nav.Penilaian">Penilaian dan Kinerja</span>
    </a>
    <ul>
        <li class="{{ Request::is('penilaian/kriteria_bobot*') ? 'active' : '' }}">
            <a href="{{ route('kriteria_bobot.index') }}" title="Kriteria dan Bobot Penilaian"
                data-filter-tags="kriteria dan bobot penilaian">
                <span class="nav-link-text" data-i18n="nav.kriteria_dan_penilaian">Kriteria dan Bobot Penilaian</span>
            </a>
        </li>
        @if(auth()->user()->role == 'Admin')
        <li class="{{ Request::is('scoring*') ? 'active' : '' }}">
            <a href="{{ route('scoring.index') }}" title="Penilaian Karyawan"
                data-filter-tags="penilaian karyawan nilai">
                <span class="nav-link-text" data-i18n="nav.penilaian_karyawan_scoring">Penilaian Karyawan</span>
            </a>
        </li>
        @endif
        @if(auth()->user()->role == 'Pemimpin Perusahaan' || auth()->user()->role == 'Admin')
        <li class="{{ Request::is('results*') ? 'active' : '' }}">
            <a href="{{ route('results.index') }}" title="Hasil Penilaian"
                data-filter-tags="hasil penilaian ranking saw">
                <span class="nav-link-text" data-i18n="nav.hasil_penilaian">Hasil Penilaian</span>
            </a>
        </li>
        @endif
        @if(auth()->user()->role == 'Pemimpin Perusahaan' || auth()->user()->role == 'Admin')
        <li class="{{ Request::is('approval*') ? 'active' : '' }}">
            <a href="{{ route('approval.index') }}" title="Persetujuan Kriteria"
                data-filter-tags="persetujuan approval kriteria">
                <span class="nav-link-text" data-i18n="nav.approval_dashboard">Persetujuan Kriteria</span>
            </a>
        </li>
        @endif
    </ul>
</li>
