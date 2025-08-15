<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-dark">
            {{ __('Tambah Kursus Baru') }} <i class="bi bi-plus-circle"></i>
        </h2>
    </x-slot>

    <div class="container py-4"> {{-- Menggunakan container Bootstrap untuk padding dan lebar --}}
        <div class="card shadow-sm border-0"> {{-- Card dengan shadow dan tanpa border bawaan --}}
            <div class="card-body p-4"> {{-- Padding dalam card body --}}
                <h3 class="card-title h5 mb-4 text-dark">Form Tambah Kursus Baru</h3>

                {{-- Pastikan enctype="multipart/form-data" ada untuk upload file --}}
                <form action="{{ route('teacher.courses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3"> {{-- Gunakan mb-3 untuk margin bottom pada form group --}}
                        <label for="title" class="form-label">Judul Kursus <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required autofocus>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Thumbnail Kursus (Opsional)</label>
                        <input type="file" name="thumbnail" id="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror">
                        <div class="form-text">Format: JPG, JPEG, PNG. Ukuran maksimal: 2MB.</div>
                        @error('thumbnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="capacity" class="form-label">Kapasitas Pelatihan (Jumlah Peserta)</label>
                        <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity') }}" min="1">
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai Pelatihan</label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai Pelatihan</label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Harga (biarkan kosong jika gratis)</label>
                        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" step="0.01" min="0">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="difficulty_level" class="form-label">Level Kesulitan <span class="text-danger">*</span></label>
                        <select name="difficulty_level" id="difficulty_level" class="form-select @error('difficulty_level') is-invalid @enderror" required>
                            <option value="">Pilih Level Kesulitan</option>
                            @foreach ($difficultyLevels as $level)
                                <option value="{{ $level }}" {{ old('difficulty_level') == $level ? 'selected' : '' }}>
                                    {{ ucfirst($level) }}
                                </option>
                            @endforeach
                        </select>
                        @error('difficulty_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check"> {{-- Checkbox Bootstrap --}}
                        <input type="checkbox" name="is_published" id="is_published" class="form-check-input @error('is_published') is-invalid @enderror" {{ old('is_published') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Publikasikan Kursus</label>
                        @error('is_published')
                            <div class="invalid-feedback d-block">{{ $message }}</div> {{-- d-block agar pesan error tampil --}}
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end mt-4"> {{-- Menggunakan flexbox untuk menata tombol --}}
                        <a href="{{ route('teacher.courses.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Tambah Kursus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>