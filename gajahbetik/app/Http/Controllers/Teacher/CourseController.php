<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Auth::user()->courses()->with('category')->latest()->paginate(10);
        return view('teacher.courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        // Pastikan hanya pemilik course yang bisa lihat
        if (Auth::id() !== $course->user_id) {
            abort(403, 'Tidak diizinkan.');
        }

        return view('courses.show', compact('course'));
    }

    public function create()
    {
        $categories = Category::all();
        $difficultyLevels = ['beginner', 'intermediate', 'advanced'];
        return view('teacher.courses.create', compact('categories', 'difficultyLevels'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'is_published' => $request->has('is_published'),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'nullable|numeric|min:0',
            'is_published' => 'boolean',
            'difficulty_level' => ['required', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'category_id' => 'required|exists:categories,id',
            'capacity' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Generate slug unik
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (Course::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        $validated['slug'] = $slug;

        // Upload ke Cloudinary jika ada thumbnail
        if ($request->hasFile('thumbnail')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $upload = $cloudinary->uploadApi()->upload($request->file('thumbnail')->getRealPath(), [
                'folder' => 'thumbnails',
            ]);
            $validated['thumbnail'] = $upload['secure_url'];
        }

        $validated['user_id'] = Auth::id();
        Course::create($validated);

        return redirect()->route('teacher.courses.index')->with('success', 'Kursus berhasil ditambahkan!');
    }

    public function edit(Course $course)
    {
        if (Auth::id() !== $course->user_id) {
            abort(403, 'Tidak diizinkan.');
        }

        $categories = Category::all();
        $difficultyLevels = ['beginner', 'intermediate', 'advanced'];

        return view('teacher.courses.edit', compact('course', 'categories', 'difficultyLevels'));
    }

    public function update(Request $request, Course $course)
    {
        if (Auth::id() !== $course->user_id) {
            abort(403, 'Tidak diizinkan.');
        }

        $request->merge([
            'is_published' => $request->has('is_published'),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'nullable|numeric|min:0',
            'is_published' => 'boolean',
            'difficulty_level' => ['required', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'category_id' => 'required|exists:categories,id',
            'capacity' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Slug unik saat update
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (Course::where('slug', $slug)->where('id', '!=', $course->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        $validated['slug'] = $slug;

        // Jika ada file baru, upload dan hapus yang lama
        if ($request->hasFile('thumbnail')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            if ($course->thumbnail) {
                $urlPath = parse_url($course->thumbnail, PHP_URL_PATH);
                $publicId = 'thumbnails/' . pathinfo($urlPath, PATHINFO_FILENAME);
                $cloudinary->uploadApi()->destroy($publicId);
            }

            $upload = $cloudinary->uploadApi()->upload($request->file('thumbnail')->getRealPath(), [
                'folder' => 'thumbnails',
            ]);
            $validated['thumbnail'] = $upload['secure_url'];
        } else {
            unset($validated['thumbnail']);
        }

        $course->update($validated);

        return redirect()->route('teacher.courses.index')->with('success', 'Kursus berhasil diperbarui!');
    }

    public function destroy(Course $course)
    {
        if (Auth::id() !== $course->user_id) {
            abort(403, 'Tidak diizinkan.');
        }

        if ($course->thumbnail) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $urlPath = parse_url($course->thumbnail, PHP_URL_PATH);
            $publicId = 'thumbnails/' . pathinfo($urlPath, PATHINFO_FILENAME);
            $cloudinary->uploadApi()->destroy($publicId);
        }

        $course->delete();
        return redirect()->route('teacher.courses.index')->with('success', 'Kursus berhasil dihapus!');
    }
}
