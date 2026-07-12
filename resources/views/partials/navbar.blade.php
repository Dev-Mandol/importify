<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('upload.index') }}">
            <i class="bi bi-box-arrow-in-up-right fs-5"></i>
            <span>Importify</span>
        </a>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-1">

                <li class="nav-item">
                    <a class="nav-link rounded px-3 d-flex align-items-center gap-2 {{ Route::is('upload.*') ? 'active fw-semibold bg-white bg-opacity-10' : '' }}"
                       href="{{ route('upload.index') }}">
                        <i class="bi bi-upload"></i>
                        Upload CSV
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link rounded px-3 d-flex align-items-center gap-2 {{ Route::is('dashboard.*') ? 'active fw-semibold bg-white bg-opacity-10' : '' }}"
                       href="{{ route('dashboard.index') }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link rounded px-3 d-flex align-items-center gap-2 {{ Route::is('shopify.*') ? 'active fw-semibold bg-white bg-opacity-10' : '' }}"
                       href="{{ route('shopify.collection') }}">
                        <i class="bi bi-shop"></i>
                        Shopify Collection
                    </a>
                </li>

            </ul>
        </div>

    </div>
</nav>