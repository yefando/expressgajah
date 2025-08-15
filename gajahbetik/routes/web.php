
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\BiodataController;
use App\Http\Controllers\CertificateController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\EnrollmentManagementController as AdminEnrollmentController;

use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\ModuleController as TeacherModuleController;
use App\Http\Controllers\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Teacher\CourseEnrollmentController as TeacherCourseEnrollmentController;

use Cloudinary\Cloudinary;
Route::get('/upload-form', function () {
    return '
        <form action="/upload-form" method="POST" enctype="multipart/form-data">
            '.csrf_field().'
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>
    ';
});

Route::post('/upload-form', function (Request $request) {
    $request->validate([
        'file' => 'required|file|mimes:jpg,jpeg,png,gif|max:2048'
    ]);

    $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

    $result = $cloudinary->uploadApi()->upload(
        $request->file('file')->getRealPath()
    );

    return '<p>Upload berhasil!</p>
            <p>URL: <a href="'.$result['secure_url'].'" target="_blank">'.$result['secure_url'].'</a></p>
            <img src="'.$result['secure_url'].'" style="max-width:300px;">';
});

Route::get('/check-env', function () {
    return env('CLOUDINARY_URL');
});


Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/kursus', [CourseController::class, 'index'])->name('courses.index');
Route::get('/kursus/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/pelajaran/{lesson:slug}', [LessonController::class, 'show'])->name('lessons.show');
Route::get('/certificate/{uuid}', [CertificateController::class, 'show'])->name('certificate.show');

Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = "public/$folder/$filename";
    if (!Storage::exists($path)) {
        abort(404);
    }
    
    return Storage::response($path, null, [
        'Access-Control-Allow-Origin' => '*'
    ]);
})->whereIn('folder', ['thumbnails', 'videos', 'attachments'])->name('storage');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isTeacher()) {
            return redirect()->route('teacher.dashboard');
        } else {
            return redirect()->route('student.my-courses');
        }
    })->middleware('verified')->name('dashboard');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/biodata', [BiodataController::class, 'edit'])->name('profile.biodata.edit');
    Route::put('/biodata', [BiodataController::class, 'update'])->name('profile.biodata.update');
    
    Route::post('/pelajaran/{lesson:slug}/complete', [LessonController::class, 'markAsCompleted'])->name('lessons.complete');
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll'])->name('enroll.course');
    Route::post('/courses/{course}/enroll-check', [EnrollmentController::class, 'enrollCheck'])->name('enroll.check');
    Route::get('/my-courses', [StudentController::class, 'myCourses'])->name('student.my-courses');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('courses', AdminCourseController::class);
        Route::resource('courses.modules', AdminModuleController::class);
        Route::resource('lessons', AdminLessonController::class);

        Route::get('/enrollments', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::post('/enrollments/{enrollment}/approve', [AdminEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [AdminEnrollmentController::class, 'reject'])->name('enrollments.reject');
        Route::delete('/enrollments/{enrollment}', [AdminEnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    });

    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::resource('courses', TeacherCourseController::class);
        Route::resource('courses.modules', TeacherModuleController::class)->except(['show']);
        Route::resource('courses.modules.lessons', TeacherLessonController::class)->except(['show']);

        Route::get('/courses/{course}/enrollments', [TeacherCourseEnrollmentController::class, 'index'])->name('courses.enrollments.index');
        Route::post('/enrollments/{enrollment}/approve', [TeacherCourseEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('/enrollments/{enrollment}/reject', [TeacherCourseEnrollmentController::class, 'reject'])->name('enrollments.reject');
        Route::post('/enrollments/{enrollment}/complete', [TeacherCourseEnrollmentController::class, 'complete'])->name('enrollments.complete');
        Route::delete('/enrollments/{enrollment}', [TeacherCourseEnrollmentController::class, 'destroy'])->name('enrollments.destroy');

        Route::get('/courses/{course}/enrollments/{user}/biodata', [TeacherCourseEnrollmentController::class, 'getBiodata'])->name('enrollments.user.biodata');
    });
});

require __DIR__.'/auth.php';