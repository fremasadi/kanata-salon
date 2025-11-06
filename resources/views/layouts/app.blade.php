<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kanata Salon') }}</title>

    {{-- ===== Sneat CSS ===== --}}
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/boxicons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/custom.css') }}">

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    {{-- Stack untuk CSS tambahan dari page --}}
    @stack('styles')

    <script src="{{ asset('sneat/assets/vendor/js/helpers.js') }}"></script>
</head>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        {{-- ===== Sidebar ===== --}}
        @include('layouts.sidebar')

        {{-- ===== Main Layout ===== --}}
        <div class="layout-page">

            {{-- ===== Navbar (dengan hamburger) ===== --}}
            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                id="layout-navbar">

                <!-- Tombol Hamburger (Mobile) -->
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                        <i class="bx bx-menu bx-sm"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                                <i class="bx bx-user-circle bx-sm"></i>
                                <span class="ms-2">{{ Auth::user()->name ?? 'User' }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Profil</a></li>
                                <li><div class="dropdown-divider"></div></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger">
                                            <i class="bx bx-log-out"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            {{-- ===== Content ===== --}}
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    {{ $slot }}
                </div>
            </div>

            {{-- ===== Footer ===== --}}
            <footer class="content-footer footer bg-footer-theme text-center py-3">
                <strong>&copy; {{ date('Y') }} Kanata Salon.</strong> All rights reserved.
            </footer>
        </div>
    </div>
</div>

{{-- ===== Sneat JS Core (URUTAN PENTING!) ===== --}}
<script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('sneat/assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('sneat/assets/js/main.js') }}"></script>

{{-- Select2 JS (SETELAH jQuery!) --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Stack untuk JS tambahan dari page --}}
@stack('scripts')

</body>
</html>