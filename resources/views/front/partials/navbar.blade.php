<nav class="bg-white shadow-lg sticky top-0 z-50" x-data="{ mobileMenu: false }">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <a href="#" class="text-2xl font-bold text-gray-800">Kanata Salon</a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('landing') }}#home" class="text-gray-700 hover:text-[#EC008C] transition duration-300 font-medium">Home</a>
                <a href="{{ route('landing') }}#layanan" class="text-gray-700 hover:text-[#EC008C] transition duration-300 font-medium">Layanan</a>
                <a href="{{ route('landing') }}#tentang" class="text-gray-700 hover:text-[#EC008C] transition duration-300 font-medium">Tentang Kami</a>
                <a href="{{ route('landing') }}#kontak" class="text-gray-700 hover:text-[#EC008C] transition duration-300 font-medium">Kontak</a>
                
                @if (Route::has('login'))
                    @auth
                        <!-- Cart Icon -->
                        <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-[#EC008C] transition duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            @php
                                $cartCount = \App\Models\Cart::count();
                            @endphp
                            @if($cartCount > 0)
                                <span class="absolute -top-2 -right-2 bg-[#EC008C] text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>

                        <div class="relative" x-data="{ open: false }">
                            <!-- Avatar button -->
                            <button @click="open = !open"
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-[#EC008C] to-[#D4006F] text-white font-semibold flex items-center justify-center focus:outline-none shadow hover:from-[#D4006F] hover:to-[#EC008C] transition">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </button>

                            <!-- Dropdown -->
                            <div x-cloak x-show="open" @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-52 bg-white border rounded-lg shadow-lg py-2 z-50">
                                
                                <div class="px-4 py-2 text-gray-700 border-b">
                                    <p class="font-semibold">{{ Auth::user()->name }}</p>
                                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                                </div>
                            <a href="{{ route('history.index') }}" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 ">
                                
                                Riwayat
                            </a>
                                
                               
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600">
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-[#EC008C] transition duration-300 font-medium">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-[#EC008C] text-white px-6 py-2 rounded-full hover:bg-[#D4006F] transition duration-300 font-medium">
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
            
            <!-- Mobile Menu Button -->
            <button @click="mobileMenu = !mobileMenu" class="md:hidden text-gray-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-cloak class="md:hidden pb-4">
            <div class="flex flex-col space-y-3">
                <a href="{{ route('landing') }}#home" class="text-gray-700 hover:text-[#EC008C] transition duration-300">Home</a>
                <a href="{{ route('landing') }}#layanan" class="text-gray-700 hover:text-[#EC008C] transition duration-300">Layanan</a>
                <a href="{{ route('landing') }}#tentang" class="text-gray-700 hover:text-[#EC008C] transition duration-300">Tentang Kami</a>
                <a href="{{ route('landing') }}#kontak" class="text-gray-700 hover:text-[#EC008C] transition duration-300">Kontak</a>
                
                @if (Route::has('login'))
                    @auth
                    
                        
                        <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-[#EC008C] transition duration-300">
                            Profil
                        </a>
                        <a href="{{ route('history.index') }}" class="text-gray-700 hover:text-[#EC008C] transition duration-300">
                            Riwayat
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left text-gray-700 hover:text-red-600 transition duration-300">
                                Keluar
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-[#EC008C] transition duration-300">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-[#EC008C] text-white px-6 py-2 rounded-full hover:bg-[#D4006F] transition duration-300 text-center">
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('navbar', () => ({
            mobileMenu: false
        }))
    })
</script>
@endpush