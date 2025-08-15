<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-dark">
            {{ __('Jelajahi Pelatihan') }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                {{-- Bagian lain dari halaman --}}

                @if ($courses->count())
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @foreach ($courses as $course)
                            <div class="col">
                                <div class="card h-100 shadow-sm border-0">
@if ($course->thumbnail)
    {{-- Jika thumbnail berupa URL (Cloudinary), tampilkan langsung --}}
    <img src="{{ $course->thumbnail }}" class="card-img-top" alt="Thumbnail Kursus" style="height: 200px; object-fit: cover;">
@else
    {{-- Placeholder jika tidak ada thumbnail --}}
    <div class="bg-light d-flex align-items-center justify-content-center text-muted card-img-top" style="height: 200px;">
        <p class="mb-0">Tidak ada Thumbnail</p>
    </div>
@endif

                                    
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title text-dark mb-2">{{ $course->title }}</h5>
                                        <p class="card-text text-muted small mb-1">Oleh: <span class="fw-medium">{{ $course->teacher->name ?? 'N/A' }}</span></p>
                                        <p class="card-text text-muted small mb-3">Kategori: <span class="fw-medium">{{ $course->category->name ?? 'N/A' }}</span></p>
                                        <p class="card-text text-truncate-3-lines mb-3">{{ $course->description }}</p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="h6 mb-0 text-success">
                                                    {{ $course->price ? 'Rp ' . number_format($course->price, 0, ',', '.') : 'Gratis' }}
                                                </span>
                                                @auth
                                                    {{-- Cek apakah kursus sudah selesai untuk menampilkan tombol download --}}
                                                    @if ($completedCourseIds->contains($course->id))
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                                                            <a href="{{ route('certificate.download', $course->slug) }}" class="btn btn-success btn-sm">Download Sertifikat</a>
                                                        </div>
                                                    @elseif ($isBiodataComplete)
                                                        <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                                                    @else
                                                        <a href="#" class="btn btn-primary btn-sm" onclick="return showBiodataAlert();">Lihat Detail</a>
                                                    @endif
                                                @else
                                                    <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Tampilkan pagination --}}
                    <div class="mt-4">{{ $courses->links('pagination::bootstrap-5') }}</div>
                @else
                    <div class="alert alert-info text-center py-4">
                        <p class="mb-0">Maaf, belum ada kursus yang tersedia saat ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Script dan Style --}}
    @push('styles')
    <style>
        .text-truncate-3-lines {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function showBiodataAlert() {
            if (confirm('Anda belum mengisi biodata. Mohon isi biodata dahulu.')) {
                window.location.href = "{{ route('profile.biodata.edit') }}";
            }
            return false;
        }
    </script>
    @endpush
</x-app-layout>