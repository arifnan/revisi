<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sidebar E-Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<!-- Sidebar -->
<div id="sidebar" class="sidebar-expanded d-flex flex-column vh-100 p-3" style="position: fixed;">

    <!-- Burger Button -->
<div id="burger-container" class="mb-3">
    <button id="burger-menu" onclick="toggleSidebar()" class="btn btn-outline-light p-1 border-0">
        <i class="bi bi-list fs-3"></i>
    </button>
</div>

    <!-- Logo -->
    <div id="sidebar-logo" class="mb-4">
        <h4 class="text-white">E-Form</h4>
    </div>

    <!-- Menu -->
    <ul class="nav nav-pills flex-column mb-auto" id="nav-menu">
    <li class="nav-item mb-1">
        <a href="{{ route('dashboard') }}" class="nav-link text-white d-flex align-items-center">
            <i class="bi bi-house-door me-2"></i><span>Dashboard</span>
        </a>
        <hr class="my-1 border-light">
    </li>
    <li class="nav-item mb-1">
        <a href="{{ route('forms.index') }}" class="nav-link text-white d-flex align-items-center">
            <i class="bi bi-ui-checks me-2"></i><span>Kelola Formulir</span>
        </a>
        <hr class="my-1 border-light">
    </li>
    <li class="nav-item mb-1">
        <a href="{{ route('admin.index') }}" class="nav-link text-white d-flex align-items-center">
            <i class="bi bi-person-gear me-2"></i><span>Kelola Admin</span>
        </a>
        <hr class="my-1 border-light">
    </li>
    <li class="nav-item mb-1">
        <a href="{{ route('teachers.index') }}" class="nav-link text-white d-flex align-items-center">
            <i class="bi bi-person-badge me-2"></i><span>Kelola Guru</span>
        </a>
        <hr class="my-1 border-light">
    </li>
    <li class="nav-item mb-3">
        <a href="{{ route('students.index') }}" class="nav-link text-white d-flex align-items-center">
            <i class="bi bi-person me-2"></i><span>Kelola Siswa</span>
        </a>
        <hr class="my-1 border-light">
    </li>
</ul>

    <!-- Logout -->
   <div id="logout-container" class="mt-auto">
    <a href="{{ route('logout') }}" class="btn btn-danger w-100">Logout</a>
    </div>

</div>

<!-- JS -->
<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
