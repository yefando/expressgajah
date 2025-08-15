<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-dark">
            {{ __('Edit Kursus') }} <i class="bi bi-pencil-square"></i>
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">

                <h3 class="card-title h5 mb-4 text-dark">Form Edit Kursus: {{ $course->title }}</h3>

                <form action="{{ route('teacher.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Judul --}}
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Kursus <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $course->title) }}" required autofocus>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Thumbnail --}}
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Thumbnail Kursus (Opsional)</label>
                        <input type="file" name="thumbnail" id="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror">
                        <div class="form-text">Format: JPG, JPEG, PNG. Ukuran maksimal: 2MB.</div>
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if ($course->thumbnail)
                            <div class="mt-2 d-flex align-items-center">
                                <span class="me-2 text-muted">Thumbnail saat ini:</span>
                                <img src="{{ $course->thumbnail }}" alt="{{ $course->title }}" class="img-thumbnail" style="max-width: 150px; height: auto;">
                                <div class="form-check ms-4">
                                    <input type="checkbox" name="remove_thumbnail" id="remove_thumbnail" class="form-check-input">
                                    <label class="form-check-label text-danger" for="remove_thumbnail">
                                        <i class="bi bi-trash-fill me-1"></i> Hapus Thumbnail
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Kapasitas --}}
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Kapasitas Pelatihan (Jumlah Peserta)</label>
                        <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                               value="{{ old('capacity', $course->capacity) }}" min="1">
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai Pelatihan</label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                               value="{{ old('start_date', date('Y-m-d', strtotime($course->start_date))) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai Pelatihan</label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                               value="{{ old('end_date', date('Y-m-d', strtotime($course->end_date))) }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Harga --}}
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga (biarkan kosong jika gratis)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" 
                                   value="{{ old('price', $course->price) }}" step="0.01" min="0">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Level Kesulitan --}}
                    <div class="mb-3">
                        <label for="difficulty_level" class="form-label">Level Kesulitan <span class="text-danger">*</span></label>
                        <select name="difficulty_level" id="difficulty_level" class="form-select @error('difficulty_level') is-invalid @enderror" required>
                            <option value="">Pilih Level Kesulitan</option>
                            @foreach ($difficultyLevels as $level)
                                <option value="{{ $level }}" {{ old('difficulty_level', $course->difficulty_level) == $level ? 'selected' : '' }}>
                                    {{ ucfirst($level) }}
                                </option>
                            @endforeach
                        </select>
                        @error('difficulty_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kategori --}}
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Publikasi --}}
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_published" id="is_published" class="form-check-input @error('is_published') is-invalid @enderror" 
                               {{ old('is_published', $course->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Publikasikan Kursus</label>
                        @error('is_published')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol --}}
                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('teacher.courses.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-arrow-repeat me-1"></i> Perbarui Kursus
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
