<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kanata Salon Expert - Salon Kecantikan Terpercaya')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --pink-primary: #EC008C;
            --pink-dark: #D4006F;
            --gold-primary: #C9A961;
            --gold-light: #E6D5A8;
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('front.partials.navbar')
    
    <main>
        @yield('content')
    </main>
    
    @include('front.partials.footer')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
    function addToCart(layananId) {
        fetch(`/cart/add/${layananId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification(data.message, 'success');
                
                // Update cart count
                updateCartCount(data.cart_count);
            }
        })
        .catch(error => {
            showNotification('Terjadi kesalahan', 'error');
        });
    }
    
    function updateCartCount(count) {
        const badges = document.querySelectorAll('.cart-count');
        badges.forEach(badge => {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        });
    }
    
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
</script>

@stack('scripts')
</body>
</html>