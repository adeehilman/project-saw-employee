<li class="{{ Request::is('approval*') ? 'active' : '' }}">
            <a href="{{ route('approval.index') }}" title="Persetujuan Kriteria"
                data-filter-tags="persetujuan approval kriteria">
                <span class="nav-link-text" data-i18n="nav.approval_dashboard">Persetujuan Kriteria</span>
            </a>
</li>