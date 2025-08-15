<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * Memeriksa kelengkapan biodata pengguna sebelum menampilkan modal pendaftaran.
     */
    public function enrollCheck(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();

        if (!$this->isBiodataComplete($user)) {
            return redirect()->back()->with('biodata_check_status', 'incomplete');
        }

        return redirect()->back()->with('showEnrollmentModal', true);
    }
    
    /**
     * Helper method untuk memeriksa kelengkapan biodata.
     */
    private function isBiodataComplete($user): bool
    {
        return $user->full_name &&
               $user->nik &&
               $user->date_of_birth &&
               $user->place_of_birth &&
               $user->sub_district &&
               $user->regency &&
               $user->full_address &&
               $user->gender &&
               $user->identity_card_path;
    }

    /**
     * Memproses pendaftaran pelatihan.
     */
public function enroll(Request $request, Course $course): RedirectResponse
{
    $user = $request->user();

    // 1. Validasi data input
    $request->validate([
        'reason' => 'required|string|max:1000',
        'cv' => 'required|file|mimes:pdf,doc,docx|max:2048',
    ], [
        'reason.required' => 'Alasan mengikuti kegiatan wajib diisi.',
        'reason.string' => 'Alasan harus berupa teks.',
        'reason.max' => 'Alasan tidak boleh lebih dari 1000 karakter.',
        'cv.required' => 'File CV wajib dilampirkan.',
        'cv.file' => 'CV harus berupa file.',
        'cv.mimes' => 'Format CV harus PDF, DOC, atau DOCX.',
        'cv.max' => 'Ukuran CV tidak boleh melebihi 2MB.',
    ]);

    // 2. Periksa kelengkapan biodata
    if (!$this->isBiodataComplete($user)) {
        return redirect()->route('profile.biodata.edit')
            ->with('error', 'Pendaftaran gagal. Mohon lengkapi biodata Anda terlebih dahulu.');
    }

    // 3. Cek apakah pengguna sudah terdaftar di kursus ini
    $existingEnrollment = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->first();

    if ($existingEnrollment) {
        if ($existingEnrollment->status == 'pending') {
            return redirect()->back()->with('info', 'Anda sudah mendaftar untuk pelatihan ini dan pendaftaran Anda masih dalam peninjauan.');
        } elseif (in_array($existingEnrollment->status, ['approved', 'complete'])) {
            return redirect()->back()->with('info', 'Anda sudah terdaftar di pelatihan ini.');
        }
    }

    // 4. Upload file CV
    $cvPath = null;
    if ($request->hasFile('cv')) {
        $cvFile = $request->file('cv');
        $cvPath = $cvFile->store('cvs', 'public'); // Pastikan php artisan storage:link sudah dijalankan
    }

    // 5. Simpan data pendaftaran dengan UUID
    try {
        Enrollment::create([
            'uuid' => Str::uuid(),          // UUID otomatis
            'user_id' => $user->id,
            'course_id' => $course->id,
            'reason' => $request->reason,
            'cv_path' => $cvPath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Pendaftaran Anda telah berhasil dikirim! Mohon tunggu konfirmasi dari admin.');
    } catch (\Exception $e) {
        // Hapus file CV jika gagal menyimpan data
        if ($cvPath) {
            Storage::disk('public')->delete($cvPath);
        }

        // Debug: tampilkan pesan error asli (bisa dihapus di production)
        return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pendaftaran Anda: ' . $e->getMessage());
    }
}

}