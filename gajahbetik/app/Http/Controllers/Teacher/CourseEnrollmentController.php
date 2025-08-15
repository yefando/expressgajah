<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CourseEnrollmentController extends Controller
{
    /**
     * Tampilkan daftar pendaftaran untuk kursus yang diajar guru.
     */
    public function index(Course $course): View
    {
        // Otorisasi: Pastikan guru yang login adalah pemilik kursus
        if (Auth::id() !== $course->user_id) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $enrollments = $course->enrollments()
            ->with('user')
            ->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 1
                    WHEN 'approved' THEN 2
                    WHEN 'complete' THEN 3
                    WHEN 'rejected' THEN 4
                    ELSE 5
                END
            ")
            ->latest()
            ->paginate(10);

        return view('teacher.courses.enrollments.index', compact('course', 'enrollments'));
    }

    /**
     * Menyetujui pendaftaran.
     */
    public function approve(Enrollment $enrollment): RedirectResponse
    {
        // Otorisasi: Pastikan guru yang login adalah pemilik kursus terkait
        if (Auth::id() !== $enrollment->course->user_id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        if ($enrollment->status !== 'pending') {
            return back()->with('error', 'Pendaftaran sudah diproses.');
        }

        $enrollment->status = 'approved';
        $enrollment->save();
        
        return back()->with('success', 'Pendaftaran berhasil disetujui! Peserta sekarang terdaftar di kursus.');
    }
    
    /**
     * Menandai pendaftaran sebagai selesai.
     */
    public function complete(Enrollment $enrollment): RedirectResponse
    {
        // Otorisasi
        if (Auth::id() !== $enrollment->course->user_id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        if ($enrollment->status !== 'approved') {
            return back()->with('error', 'Pendaftaran hanya bisa ditandai selesai jika statusnya sudah disetujui.');
        }

        $enrollment->status = 'complete';
        $enrollment->save();
        
        return back()->with('success', 'Pendaftaran berhasil ditandai sebagai Selesai.');
    }

    /**
     * Menolak pendaftaran.
     */
    public function reject(Enrollment $enrollment): RedirectResponse
    {
        // Otorisasi
        if (Auth::id() !== $enrollment->course->user_id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        if ($enrollment->status !== 'pending') {
            return back()->with('error', 'Pendaftaran sudah diproses.');
        }

        $enrollment->status = 'rejected';
        $enrollment->save();

        return back()->with('success', 'Pendaftaran berhasil ditolak.');
    }

    /**
     * Menghapus pendaftaran.
     */
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        // Otorisasi
        if (Auth::id() !== $enrollment->course->user_id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus pendaftaran ini.');
        }

        if ($enrollment->cv_path) {
            Storage::delete($enrollment->cv_path);
        }
        
        $enrollment->delete();

        return back()->with('success', 'Pendaftaran berhasil dihapus.');
    }

    /**
     * Mengambil data biodata peserta kursus melalui AJAX.
     */
    public function getBiodata(Course $course, User $user): JsonResponse
    {
        // Otorisasi
        if (Auth::id() !== $course->user_id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $enrollment = $course->enrollments()->where('user_id', $user->id)->first();
        
        if (!$enrollment) {
            return response()->json(['error' => 'Peserta tidak terdaftar di kursus ini.'], 404);
        }
        
        $age = optional(Carbon::parse($user->date_of_birth))->age;

        $data = [
            'full_name' => $user->full_name ?? 'N/A',
            'email' => $user->email ?? 'N/A',
            'nik' => $user->nik ?? 'N/A',
            'date_of_birth' => optional($user->date_of_birth)->format('d F Y') ?? 'N/A',
            'place_of_birth' => $user->place_of_birth ?? 'N/A',
            'gender' => $user->gender ?? 'N/A',
            'age' => $age ?? 'N/A',
            'full_address' => $user->full_address ?? 'N/A',
            'regency' => $user->regency ?? 'N/A',
            'sub_district' => $user->sub_district ?? 'N/A',
            'phone_number' => $user->phone_number ?? 'N/A',
            'identity_card_path' => $user->identity_card_path ? Storage::url($user->identity_card_path) : null,
            'cv_path' => $enrollment->cv_path ? Storage::url($enrollment->cv_path) : null,
        ];

        return response()->json($data);
    }
  
  

}