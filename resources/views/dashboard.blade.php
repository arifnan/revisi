@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<!-- Custom CSS -->
<style>
    .dashboard-card {
        background: linear-gradient(135deg, #3D1860 0%, #5f2c82 100%);
        color: white;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .dashboard-card .icon {
        font-size: 2.5rem;
        color: #ffffffcc;
    }

    .dashboard-card .text {
        font-size: 1.1rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .dashboard-card .count {
        font-size: 1.8rem;
        font-weight: bold;
    }

    .welcome-card {
        background: #ffffff;
        border-left: 6px solid #3D1860;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .welcome-card h6 {
        color: #3D1860;
        font-weight: bold;
    }

    .welcome-card p {
        color: #333;
    }
</style>

<!-- Content -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <img src="{{ asset('images/logo.png') }}" alt="Dashboard Logo" style="width: 40px; height: 40px; margin-right: 10px;">
        <h1 class="h3 text-dark mb-0">Dashboard</h1>
    </div>
</div>

<div class="row">
    @php
        $cards = [
            ['label' => 'Total Admin', 'count' => $adminCount, 'icon' => 'fas fa-user-shield'],
            ['label' => 'Total Guru', 'count' => $teacherCount, 'icon' => 'fas fa-chalkboard-teacher'],
            ['label' => 'Total Siswa', 'count' => $studentCount, 'icon' => 'fas fa-user-graduate'],
            ['label' => 'Total Formulir', 'count' => $formCount, 'icon' => 'fas fa-clipboard-list'],
        ];
    @endphp

    @foreach($cards as $card)
    <div class="col-xl-3 col-md-6 mb-4" data-aos="fade-up">
        <div class="card dashboard-card h-100 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text">{{ $card['label'] }}</div>
                    <div class="count">{{ $card['count'] }}</div>
                </div>
                <div class="icon">
                    <i class="{{ $card['icon'] }}"></i>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row" data-aos="fade-up" data-aos-delay="100">
    <div class="col-lg-12">
        <div class="card welcome-card p-4">
            <div class="card-header bg-transparent border-0">
                <h6>🎉 Selamat Datang!</h6>
            </div>
            <div class="card-body">
                <p>Selamat datang di dashboard aplikasi Anda. Gunakan menu di samping untuk mengelola data dan fitur yang tersedia.</p>
            </div>
        </div>
    </div>
</div>

<!-- AOS Animation -->
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>

@endsection
