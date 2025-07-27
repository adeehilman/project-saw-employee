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