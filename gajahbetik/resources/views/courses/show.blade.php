<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-dark">
            {{ $course->title }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                {{-- Pesan Sukses/Error/Info --}}
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
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Informasi!</strong> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-12 col-md-8">
                        <h1 class="h3 mb-3 text-dark">{{ $course->title }}</h1>
                        <p class="text-muted mb-2">Oleh: {{ $course->teacher->name ?? 'N/A' }}</p>
                        <p class="text-muted mb-4">Kategori: {{ $course->category->name ?? 'N/A' }}</p>

@if ($course->thumbnail)
    {{-- Jika thumbnail berupa URL (Cloudinary), tampilkan langsung --}}
    <img src="{{ $course->thumbnail }}" class="card-img-top" alt="Thumbnail Kursus" style="height: 200px; object-fit: cover;">
@else
    {{-- Placeholder jika tidak ada thumbnail --}}
    <div class="bg-light d-flex align-items-center justify-content-center text-muted card-img-top" style="height: 200px;">
        <p class="mb-0">Tidak ada Thumbnail</p>
    </div>
@endif


                        <div class="mb-4">
                            <h3 class="h5 fw-semibold mb-2">Deskripsi Kursus</h3>
                            <div class="text-secondary lh-base">
                                {!! nl2br(e($course->description)) !!}
                            </div>
                        </div>

                        <div class="mb-4">
                            <h3 class="h5 fw-semibold mb-3">Materi Kursus</h3>
                            @forelse ($course->modules as $module)
                                <div class="card mb-3 p-3 bg-light">
                                    <h4 class="h6 fw-bold mb-2">{{ $module->order}}. {{ $module->title }}</h4>
                                    <p class="text-muted mb-2">{{ $module->description }}</p>
                                    @if ($module->lessons->count())
                                        <ul class="list-group list-group-flush mt-2">
                                            @foreach ($module->lessons as $lesson)
                                                <li class="list-group-item bg-transparent border-0 px-0">
                                                    @if ($isEnrolled && $enrollmentStatus === 'approved' || $lesson->is_free)
                                                        <a href="{{ route('lessons.show', ['course' => $course->slug, 'lesson' => $lesson->slug]) }}" class="text-primary hover-underline">
                                                            {{ $lesson->order }}. {{ $lesson->title }}
                                                        </a>
                                                    @else
                                                        {{ $lesson->order }}. {{ $lesson->title }}
                                                        <span class="text-sm text-muted">(Konten Terkunci)</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted small">Belum ada pelajaran di modul ini.</p>
                                    @endif
                                </div>
                            @empty
                                <div class="alert alert-info text-center py-3">
                                    Belum ada modul untuk kursus ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card shadow-sm border-0 sticky-top" style="top: 1.5rem;">
                            <div class="card-body text-center">
                                <h3 class="h4 fw-bold mb-4">
                                    {{ $course->price ? 'Rp ' . number_format($course->price, 0, ',', '.') : 'Gratis' }}
                                </h3>

                                <div class="mb-3">
                                    <p class="text-sm text-muted fw-medium mb-1">Kapasitas Peserta:</p>
                                    <p class="h6 fw-bold text-dark">{{ $course->capacity ?? 'Tidak Terbatas' }}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="text-sm text-muted fw-medium mb-1">Tanggal Mulai:</p>
                                    <p class="h6 fw-bold text-dark">{{ $course->start_date ? $course->start_date->format('d F Y') : 'Belum Ditentukan' }}</p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-sm text-muted fw-medium mb-1">Tanggal Selesai:</p>
                                    <p class="h6 fw-bold text-dark">{{ $course->end_date ? $course->end_date->format('d F Y') : 'Belum Ditentukan' }}</p>
                                </div>

                                @auth
                                    @if ($isEnrolled)
                                        @if ($enrollmentStatus === 'approved')
                                            <a href="{{ route('student.my-courses') }}" class="btn btn-success w-100 py-2 mb-2">
                                                Lanjutkan Kursus
                                            </a>
                                            <p class="text-center text-sm text-muted">Anda sudah terdaftar dan diterima di kursus ini.</p>
                                        @elseif ($enrollmentStatus === 'pending')
                                            <button class="btn btn-warning w-100 py-2 mb-2" disabled>
                                                Pendaftaran Sedang Ditinjau
                                            </button>
                                            <p class="text-center text-sm text-muted">Pendaftaran Anda sedang menunggu persetujuan dari pengajar.</p>
                                        @elseif ($enrollmentStatus === 'rejected')
                                            <div class="alert alert-danger w-100 py-2 text-center" role="alert">
                                                Pendaftaran Ditolak.
                                            </div>
                                            <p class="text-center text-sm text-muted">Pendaftaran Anda tidak disetujui. Silakan hubungi pengajar.</p>
                                        @else
                                            <button type="button" class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#enrollmentModal">
                                                Daftar Ulang
                                            </button>
                                        @endif
                                    @else
                                        {{-- Tombol untuk memicu modal pendaftaran --}}
                                        <button type="button" class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#enrollmentModal">
                                            {{ $course->price ? 'Beli Sekarang' : 'Daftar Pelatihan' }}
                                        </button>
                                        @if (!$isBiodataComplete)
                                            <p class="text-center text-sm text-danger mt-2">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Anda perlu melengkapi biodata untuk mendaftar.
                                            </p>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2">
                                        Login untuk Mendaftar
                                    </a>
                                    <p class="text-center text-sm text-muted mt-2">Atau <a href="{{ route('register') }}" class="text-primary hover-underline">Daftar Akun Baru</a></p>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pendaftaran Kursus (Bootstrap) --}}
    <div class="modal fade" id="enrollmentModal" tabindex="-1" aria-labelledby="enrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollmentModalLabel">Form Pendaftaran Kursus: {{ $course->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('enroll.course', $course->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        {{-- Formulir Biodata yang diperlukan --}}
                        @if (!$isBiodataComplete)
                            <div class="alert alert-warning" role="alert">
                                <strong>Peringatan!</strong> Anda belum melengkapi biodata. Silakan <a href="{{ route('profile.biodata.edit') }}">lengkapi biodata Anda</a> terlebih dahulu.
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="reason" class="form-label">Alasan Mengikuti Kegiatan:</label>
                                <textarea name="reason" id="reason" rows="4" class="form-control" required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="cv" class="form-label">Lampirkan CV (PDF/DOCX, Max 2MB):</label>
                                <input type="file" name="cv" id="cv" class="form-control" accept=".pdf,.doc,.docx" required>
                                @error('cv')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($course->price > 0)
                                <div class="alert alert-info small mt-3" role="alert">
                                    Biaya kursus ini adalah: <span class="fw-bold">Rp {{ number_format($course->price, 0, ',', '.') }}</span>. Anda akan diarahkan ke halaman pembayaran setelah pendaftaran.
                                </div>
                            @else
                                <div class="alert alert-success small mt-3" role="alert">
                                    Kursus ini <span class="fw-bold">Gratis</span>.
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" {{ !$isBiodataComplete ? 'disabled' : '' }}>Kirim Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Tambahan: Skrip untuk menampilkan modal jika ada error validasi atau biodata belum lengkap --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika untuk menampilkan modal jika ada error validasi
            @if ($errors->any())
                var enrollmentModal = new bootstrap.Modal(document.getElementById('enrollmentModal'));
                enrollmentModal.show();
            @endif
        });
    </script>
    @endpush
</x-app-layout>