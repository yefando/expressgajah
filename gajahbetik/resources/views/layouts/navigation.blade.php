@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Str;
@endphp
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm"> {{-- Navbar berwarna utama, teks putih --}}
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            {{-- Menggunakan ikon Bootstrap atau SVG sederhana sebagai logo --}}
            <i class="bi bi-mortarboard-fill me-2 fs-4"></i> {{-- Contoh ikon topi kelulusan --}}
            <span class="fw-bold">{{ config('app.name', 'Gajah Betik') }}</span> {{-- Teks nama aplikasi --}}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-grid-fill me-1"></i> Dashboard
                    </a>
                </li>

                {{-- Dinamiskan tautan Kursus berdasarkan peran pengguna --}}
                @auth
                    @if (Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active fw-bold' : '' }}" href="{{ route('admin.courses.index') }}">
                                <i class="bi bi-stack me-1"></i> Kelola Pelatihan
                            </a>
                        </li>
                    @elseif (Auth::user()->isTeacher())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('teacher.courses.*') ? 'active fw-bold' : '' }}" href="{{ route('teacher.courses.index') }}">
                                <i class="bi bi-book-fill me-1"></i> Pelatihan
                            </a>
                        </li>
                    @else {{-- Siswa --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.my-courses') ? 'active fw-bold' : '' }}" href="{{ route('student.my-courses') }}">
                                <i class="bi bi-collection-fill me-1"></i> Pelatihan Saya
                            </a>
                        </li>
                        {{-- NEW: Tambahkan tautan untuk melihat semua pelatihan yang tersedia bagi siswa --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('courses.index') ? 'active fw-bold' : '' }}" href="{{ route('courses.index') }}">
                                <i class="bi bi-search me-1"></i> Jelajahi Pelatihan
                            </a>
                        </li>
                    @endif
                @else {{-- Guest --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('courses.index') ? 'active fw-bold' : '' }}" href="{{ route('courses.index') }}">
                            <i class="bi bi-globe me-1"></i> Daftar Pelatihan
                        </a>
                    </li>
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear-fill me-1"></i> Profil</a></li>

                            {{-- NEW: Tambahkan tautan untuk biodata --}}
                            <li><a class="dropdown-item" href="{{ route('profile.biodata.edit') }}"><i class="bi bi-person-lines-fill me-1"></i> Lengkapi Biodata</a></li>

                            {{-- Tambahkan tautan tambahan berdasarkan peran --}}
                            @if (Auth::user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-fill me-1"></i> Admin Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.courses.index') }}"><i class="bi bi-stack me-1"></i> Kelola Kursus (Admin)</a></li>
                            @elseif (Auth::user()->isTeacher())
                                <li><a class="dropdown-item" href="{{ route('teacher.dashboard') }}"><i class="bi bi-person-video2 me-1"></i> Teacher Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('teacher.courses.index') }}"><i class="bi bi-book-fill me-1"></i> Kursus Saya (Guru)</a></li>
                            @else {{-- Siswa --}}
                                <li><a class="dropdown-item" href="{{ route('student.my-courses') }}"><i class="bi bi-collection-fill me-1"></i> Kursus Saya</a></li>
                                {{-- NEW: Tambahkan tautan untuk melihat semua pelatihan di dropdown profil siswa --}}
                                <li><a class="dropdown-item" href="{{ route('courses.index') }}"><i class="bi bi-search me-1"></i> Jelajahi Pelatihan</a></li>
                            @endif

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"> {{-- Menggunakan button untuk submit form --}}
                                        <i class="bi bi-box-arrow-right me-1"></i> Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus-fill me-1"></i> Register</a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>