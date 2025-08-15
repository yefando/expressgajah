<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0 text-dark">
            {{ __('Daftar Pendaftar Kursus: ' . $course->title) }}
        </h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="h5 text-dark mb-4">Daftar Pendaftar Kursus: {{ $course->title }}</h3>

                <div class="mb-4">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard Guru
                    </a>
                </div>

                {{-- Pesan Sukses/Error/Info --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($enrollments->isEmpty())
                    <p class="text-muted">Belum ada pendaftaran kursus yang masuk. 😔</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Nama Peserta</th>
                                    <th scope="col">Email Peserta</th>
                                    <th scope="col">Alasan</th>
                                    <th scope="col">CV</th>
                                    <th scope="col">Tanggal Daftar</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" style="min-width: 250px;">Aksi</th> {{-- Lebarkan kolom aksi --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($enrollments as $enrollment)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                {{ $enrollment->user->name ?? 'Pengguna Dihapus' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-secondary">
                                                {{ $enrollment->user->email ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-secondary text-truncate" style="max-width: 200px;" title="{{ $enrollment->reason }}">
                                                {{ Str::limit($enrollment->reason, 100) }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($enrollment->cv_path)
                                                <a href="{{ Storage::url($enrollment->cv_path) }}" target="_blank" class="text-primary text-decoration-none small">
                                                    Lihat CV <i class="bi bi-file-earmark-text"></i>
                                                </a>
                                            @else
                                                <span class="text-muted small">Tidak Ada</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                          {{ $enrollment->created_at ? $enrollment->created_at->format('d M Y H:i') : 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge
                                                @if($enrollment->status === 'pending') bg-warning text-dark
                                                @elseif($enrollment->status === 'approved') bg-primary
                                                @elseif($enrollment->status === 'complete') bg-success
                                                @else bg-danger @endif">
                                                {{ ucfirst($enrollment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- Tombol Lihat Biodata --}}
                                            <a href="#" class="btn btn-sm btn-info text-white me-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#biodataModal"
                                                data-user-id="{{ $enrollment->user->id }}"
                                                data-course-id="{{ $course->id }}">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </a>

                                            @if ($enrollment->status === 'pending')
                                                <form action="{{ route('teacher.enrollments.approve', $enrollment) }}" method="POST" class="d-inline me-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Setujui">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('teacher.enrollments.reject', $enrollment) }}" method="POST" class="d-inline me-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Tolak">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if ($enrollment->status === 'approved')
                                                {{-- Tombol Selesai yang baru ditambahkan --}}
                                                <form action="{{ route('teacher.enrollments.complete', $enrollment) }}" method="POST" class="d-inline me-2" onsubmit="return confirm('Anda yakin ingin menandai pendaftaran ini sebagai selesai?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Tandai Selesai">
                                                        <i class="bi bi-check2-circle"></i> Selesai
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            {{-- Tombol Hapus hanya jika statusnya rejected atau complete --}}
                                            @if ($enrollment->status === 'rejected' || $enrollment->status === 'complete')
                                                <form action="{{ route('teacher.enrollments.destroy', $enrollment) }}" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus pendaftaran ini? Tindakan ini tidak dapat dibatalkan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $enrollments->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Biodata --}}
    <div class="modal fade" id="biodataModal" tabindex="-1" aria-labelledby="biodataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="biodataModalLabel">Biodata Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="biodataContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const biodataModalEl = document.getElementById('biodataModal');
            const biodataContent = document.getElementById('biodataContent');

            biodataModalEl.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const courseId = button.getAttribute('data-course-id');

                const url = `/teacher/courses/${courseId}/enrollments/${userId}/biodata`;

                biodataContent.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw new Error(errorData.error || 'Gagal memuat data');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama Lengkap:</strong> ${data.full_name || 'N/A'}</p>
                                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                                    <p><strong>NIK:</strong> ${data.nik || 'N/A'}</p>
                                    <p><strong>Tanggal Lahir:</strong> ${data.date_of_birth || 'N/A'}</p>
                                    <p><strong>Umur:</strong> ${data.age ? data.age + ' tahun' : 'N/A'}</p>
                                    <p><strong>Tempat Lahir:</strong> ${data.place_of_birth || 'N/A'}</p>
                                    <p><strong>Jenis Kelamin:</strong> ${data.gender || 'N/A'}</p>
                                    <p><strong>Nomor Telepon:</strong> ${data.phone_number || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Alamat Lengkap:</strong> ${data.full_address || 'N/A'}</p>
                                    <p><strong>Kabupaten/Kota:</strong> ${data.regency || 'N/A'}</p>
                                    <p><strong>Kecamatan:</strong> ${data.sub_district || 'N/A'}</p>
                                </div>
                            </div>
                        `;

                        if (data.identity_card_path) {
                            html += `
                                <hr class="my-4">
                                <div class="text-center">
                                    <p><strong>Kartu Identitas (KTP/Sejenis):</strong></p>
                                    <a href="${data.identity_card_path}" target="_blank" class="btn btn-sm btn-primary">
                                        Lihat Kartu Identitas
                                    </a>
                                </div>
                            `;
                        }
                        
                        if (data.cv_path) {
                            html += `
                                <hr class="my-4">
                                <div class="text-center">
                                    <p><strong>Curriculum Vitae (CV):</strong></p>
                                    <a href="${data.cv_path}" target="_blank" class="btn btn-sm btn-info text-white">
                                        Lihat CV
                                    </a>
                                </div>
                            `;
                        }

                        biodataContent.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        biodataContent.innerHTML = `<p class="text-center text-danger">Gagal memuat data. ${error.message}</p>`;
                    });
            });
        });
    </script>
    @endpush
</x-app-layout>