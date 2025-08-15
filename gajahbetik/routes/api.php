<?php

// File: routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\BiodataController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| Public Routes (Without Authentication)
|--------------------------------------------------------------------------
|
| Rute-rute ini dapat diakses oleh siapa saja.
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);

// Rute proxy gambar dipindahkan ke sini agar menjadi rute publik
Route::get('/storage/thumbnails/{filename}', function ($filename) {
    $path = "thumbnails/$filename";
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);

    return response($file, 200)->header('Content-Type', $type);
})->middleware('web');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (With Sanctum Middleware)
|--------------------------------------------------------------------------
|
| Rute-rute ini memerlukan token Sanctum untuk diakses.
|
*/
Route::middleware('auth:sanctum')->group(function () {
    // Rute untuk mendapatkan informasi user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Rute-rute khusus untuk siswa
    Route::get('/my-courses', [StudentController::class, 'myCoursesApi']);
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'enroll']);

    // Rute-rute untuk biodata (profile)
    Route::post('/profile/biodata', [BiodataController::class, 'store'])->name('api.profile.biodata.store');
    Route::put('/profile/biodata', [BiodataController::class, 'update'])->name('api.profile.biodata.update');

    // Rute-rute untuk manajemen user (jika diperlukan)
    // Catatan: Pastikan UserController ada dan di-import jika Anda membutuhkannya.
    // Jika tidak, rute ini harus dihapus atau diubah.
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
});