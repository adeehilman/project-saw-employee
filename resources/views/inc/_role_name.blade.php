<div
    class="subheader-block d-lg-flex align-items-center border-faded border-right-0 border-top-0 border-bottom-0 ml-3 pl-3">
    <div class="d-inline-flex flex-column justify-content-center mr-3">
        <span class="fw-300 fs-xs d-block opacity-50">
            <small>Selamat Datang</small>
        </span>
        <span class="fw-500 fs-xl d-block color-primary-500">
            {{ auth()->user()->name }}
        </span>
    </div>
</div>
<div
    class="subheader-block d-lg-flex align-items-center border-faded border-right-0 border-top-0 border-bottom-0 ml-3 pl-3">
    <div class="d-inline-flex flex-column justify-content-center mr-3">
        <span class="fw-300 fs-xs d-block opacity-50">
            <small>Status</small>
        </span>
        <span class="fw-500 fs-xl d-block color-danger-500">
            {{ auth()->user()->role }}
        </span>
    </div>
</div>
