<div class="height-10 w-100 shadow-lg px-4 bg-brand-gradient">
    <div class="d-flex align-items-center container p-0">
        <div
            class="page-logo m-0 d-flex align-items-center p-0 shadow-0">
            <a href="/" class="page-logo-link d-flex align-items-center">
                <img src="/admin/img/logokuadran.png{{ $profileApp->app_logo ?? '' }}" 
                    alt="Logo WebApp"
                    aria-roledescription="logo">
            </a>
        </div>
        {{ $slot }}
    </div>
</div>
