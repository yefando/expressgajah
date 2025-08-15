<x-app-layout>
    {{-- Header Halaman --}}
    <x-slot name="header">
        <h2 class="h5 mb-0 text-dark">
            {{ __('Dashboard Guru') }} 🧑‍🏫
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="card-title h5 mb-4 text-dark">Kursus yang Anda Ajar</h3>

                {{-- Tombol Tambah Kursus Baru --}}
                <div class="mb-4">
                    <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2"></i> Tambah Kursus Baru
                    </a>
                </div>

                {{-- Pesan Sukses/Error --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Berhasil!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Gagal!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($courses->count())
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($courses as $course)
                            <div class="col">
                                <div class="card h-100 shadow-sm border-0">
                                    {{-- Thumbnail --}}
                                    @if ($course->thumbnail)
                                        {{-- Jika URL Cloudinary atau path lokal --}}
                                        @php
                                            $thumbnailUrl = filter_var($course->thumbnail, FILTER_VALIDATE_URL) 
                                                ? $course->thumbnail 
                                                : Storage::url($course->thumbnail);
                                        @endphp
                                        <img src="{{ $thumbnailUrl }}" class="card-img-top object-fit-cover" alt="{{ $course->title }}" style="height: 180px;">
                                    @else
                                        {{-- Placeholder jika tidak ada thumbnail --}}
                                        <div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 180px;">
                                            <i class="bi bi-image fs-1"></i>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <h4 class="card-title h5 mb-2">{{ $course->title }}</h4>
                                     <p class="card-text text-muted small mb-3">{{ $course->description }}</p>

                                        <p class="card-text text-secondary small mb-1">
                                            <i class="bi bi-tag-fill me-1"></i> Kategori: {{ $course->category->name ?? 'N/A' }}
                                        </p>

                                        {{-- Informasi Kapasitas dan Jumlah Peserta --}}
                                        <div class="mb-3">
                                            <span class="badge bg-info text-dark">
                                                <i class="bi bi-people-fill me-1"></i> Peserta Terdaftar:
                                                <strong>{{ $course->enrollments()->where('status', 'approved')->count() }}</strong>
                                                @if ($course->capacity)
                                                    / <strong>{{ $course->capacity }}</strong>
                                                @else
                                                    (Tak Terbatas)
                                                @endif
                                            </span>
                                        </div>

                                        {{-- Tanggal Pelaksanaan --}}
                                        @if ($course->start_date && $course->end_date)
                                            <p class="card-text text-secondary small mt-1">
                                                <i class="bi bi-calendar-range-fill me-1"></i> Pelaksanaan: {{ \Carbon\Carbon::parse($course->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($course->end_date)->format('d M Y') }}
                                            </p>
                                        @elseif ($course->start_date)
                                            <p class="card-text text-secondary small mt-1">
                                                <i class="bi bi-calendar-check-fill me-1"></i> Mulai: {{ \Carbon\Carbon::parse($course->start_date)->format('d M Y') }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Footer: tombol modul dan peserta --}}
                                    <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center">
                                        <a href="{{ route('teacher.courses.modules.index', $course->id) }}" class="btn btn-success btn-sm flex-grow-1 me-2">
                                            <i class="bi bi-book-half me-1"></i> Modul
                                        </a>
                                        <a href="{{ route('teacher.courses.enrollments.index', $course->id) }}" class="btn btn-info btn-sm flex-grow-1">
                                            <i class="bi bi-eye-fill me-1"></i> Peserta
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $courses->links('pagination::bootstrap-5') }}
                    </div>
                @else
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Anda belum membuat kursus apapun. Klik "Tambah Kursus Baru" untuk memulai!
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
