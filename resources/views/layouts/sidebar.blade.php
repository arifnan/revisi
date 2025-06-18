<!-- Sidebar -->
<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; height: 100vh; position: fixed;">
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 text-decoration-none text-dark">
        <span class="fs-4">E-Form</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-dark">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('forms.index') }}" class="nav-link text-dark">
                <i class="bi bi-ui-checks"></i> Kelola Formulir
            </a>
        </li>
        <li>
            <a href="{{ route('questions.index') }}" class="nav-link text-dark">
                <i class="bi bi-list-task"></i> Kelola Pertanyaan
            </a>
        </li>
        <li>
            <a href="{{ route('responses.index') }}" class="nav-link text-dark">
                <i class="bi bi-clipboard-check"></i> Lihat Jawaban
            </a>
        </li>
        <li>
            <a href="{{ route('admin.index') }}" class="nav-link text-dark">
                <i class="bi bi-person-gear"></i> Kelola Admin
            </a>
        </li>
        <li>
            <a href="{{ route('teachers.index') }}" class="nav-link text-dark">
                <i class="bi bi-person-badge"></i> Kelola Guru
            </a>
        </li>
        <li>
            <a href="{{ route('students.index') }}" class="nav-link text-dark">
                <i class="bi bi-person"></i> Kelola Siswa
            </a>
        </li>
    </ul>
    <hr>
    <a href="{{ route('logout') }}" class="btn btn-danger w-100">Logout</a>
</div>
