<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo text-center mt-3">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-text demo text-body fw-bolder">Kanata Salon</span>
        </a>
    </div>

    <ul class="menu-inner py-1 mt-3">
        {{-- Dashboard --}}
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div>Dashboard</div>
            </a>
        </li>

        {{-- Menu Admin Only --}}
        @if(Auth::user()->role === 'admin')
            
            {{-- Data Master --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Data Master</span>
            </li>

            {{-- Jenis Layanan --}}
            <li class="menu-item {{ request()->routeIs('admin.jenis-layanan.*') ? 'active' : '' }}">
                <a href="{{ route('admin.jenis-layanan.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cut"></i>
                    <div>Jenis Layanan</div>
                </a>
            </li>

            {{-- Pegawai --}}
            <li class="menu-item {{ request()->routeIs('admin.pegawai.*') ? 'active' : '' }}">
                <a href="{{ route('admin.pegawai.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-id-card"></i>
                    <div>Pegawai</div>
                </a>
            </li>

            {{-- Shift --}}
            <li class="menu-item {{ request()->routeIs('admin.shift.*') ? 'active' : '' }}">
                <a href="{{ route('admin.shift.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-time-five"></i>
                    <div>Shift</div>
                </a>
            </li>

            {{-- Transaksi --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Transaksi</span>
            </li>

            {{-- Reservasi --}}
            <li class="menu-item {{ request()->routeIs('admin.reservasi.*') ? 'active' : '' }}">
                <a href="{{ route('admin.reservasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div>Reservasi</div>
                </a>
            </li>

            {{-- Keuangan --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Keuangan</span>
            </li>

            {{-- Gaji --}}
            <li class="menu-item {{ request()->routeIs('admin.gaji.*') ? 'active' : '' }}">
                <a href="{{ route('admin.gaji.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div>Gaji Pegawai</div>
                </a>
            </li>

            {{-- Komisi --}}
            <li class="menu-item {{ request()->routeIs('admin.komisi.*') ? 'active' : '' }}">
                <a href="{{ route('admin.komisi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-money"></i>
                    <div>Komisi</div>
                </a>
            </li>


            {{-- Pengaturan --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Pengaturan</span>
            </li>

            {{-- Manajemen Pengguna --}}
            <li class="menu-item {{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                <a href="{{ route('admin.user.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div>Manajemen Pengguna</div>
                </a>
            </li>
        @endif

        {{-- Menu Pegawai Only --}}
        @if(Auth::user()->role === 'pegawai')
            
            {{-- Jadwal & Pekerjaan --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Jadwal & Pekerjaan</span>
            </li>

            {{-- Jadwal Kerja --}}
            <li class="menu-item {{ request()->routeIs('pegawai.shift.index') ? 'active' : '' }}">
                <a href="{{ route('pegawai.shift.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-time-five"></i>
                    <div>Jadwal Kerja</div>
                </a>
            </li>

            {{-- Reservasi Saya --}}
            <li class="menu-item {{ request()->routeIs('pegawai.reservasi.index') ? 'active' : '' }}">
                <a href="{{ route('pegawai.reservasi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div>Reservasi Saya</div>
                </a>
            </li>

            {{-- Pendapatan --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Pendapatan</span>
            </li>

            {{-- Gaji Saya --}}
            <li class="menu-item {{ request()->routeIs('pegawai.gaji.index') ? 'active' : '' }}">
                <a href="{{ route('pegawai.gaji.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-wallet"></i>
                    <div>Gaji Saya</div>
                </a>
            </li>

            {{-- Komisi Saya --}}
            <li class="menu-item {{ request()->routeIs('pegawai.komisi.index') ? 'active' : '' }}">
                <a href="{{ route('pegawai.komisi.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-money"></i>
                    <div>Komisi Saya</div>
                </a>
            </li>

        @endif
    </ul>
</aside>