<x-app-layout>
  <h4 class="fw-bold py-3 mb-4">Dashboard</h4>

  <div class="row">
    <div class="col-lg-4 col-md-12 mb-4">
      <div class="card">
        <div class="card-body text-center">
          <h5 class="card-title">Selamat Datang, {{ Auth::user()->name }}!</h5>
          <p class="card-text">Anda login sebagai {{ Auth::user()->role }}.</p>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
