<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Menampilkan daftar semua kursus yang telah dipublikasi.
     */
    public function index(): View
    {
        $courses = Course::where('is_published', true)
                         ->with('teacher', 'category')
                         ->latest()
                         ->paginate(10);
                         
        $categories = Category::all();
        
        $isBiodataComplete = false;
        $completedCourseIds = collect(); // Inisialisasi koleksi kosong untuk ID kursus yang selesai

        if (Auth::check()) {
            $user = Auth::user();
            $isBiodataComplete = $this->isBiodataComplete($user);

            // Ambil semua ID kursus yang statusnya 'completed' untuk pengguna ini
            $completedCourseIds = Enrollment::where('user_id', $user->id)
                                            ->where('status', 'complete')
                                            ->pluck('course_id');
        }

        // Tampilkan ID kursus yang sudah selesai di konsol (debugging)
        // dump($completedCourseIds);
        // atau jika ingin menghentikan eksekusi dan melihat hasilnya:
        // dd($completedCourseIds);

        // Kirim $completedCourseIds ke view
        return view('courses.index', compact('courses', 'categories', 'isBiodataComplete', 'completedCourseIds'));
    }

    /**
     * Menampilkan detail dari satu kursus.
     */
    public function show(Course $course): View
    {
        if (!$course->is_published) {
            abort(404);
        }

        $course->load('teacher', 'category', 'modules.lessons');

        $isEnrolled = false;
        $enrollmentStatus = null;
        $isBiodataComplete = false;

        if (Auth::check()) {
            $user = Auth::user();

            $enrollment = Enrollment::where('user_id', $user->id)
                                    ->where('course_id', $course->id)
                                    ->first();

            if ($enrollment) {
                $isEnrolled = true;
                $enrollmentStatus = $enrollment->status;
            }
                                        
            $isBiodataComplete = $this->isBiodataComplete($user);
        }

        return view('courses.show', compact('course', 'isEnrolled', 'enrollmentStatus', 'isBiodataComplete'));
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
}