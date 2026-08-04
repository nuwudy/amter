<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Simple fetch for landing page (needs units_count)
        $courses = Course::where('is_published', true)
                         ->withCount('units')
                         ->get();

        $reviews = \App\Models\Review::published()
                        ->with('user')
                        ->latest()
                        ->take(12)
                        ->get();

        return view('home', compact('courses', 'reviews'));
    }

    public function library()
    {
        // Fetch sessions (modules) matches Student Library view
        $sessions = \App\Models\CourseSession::with(['module', 'units' => function ($query) {
                        $query->orderBy('sort_order', 'asc');
                    }])
                        ->where('is_hidden', false)
                        ->withCount('units')
                        ->whereHas('units', function($q) {
                            $q->where('is_published', true);
                        })
                        ->orderBy('sort_order')
                        ->get();

        $freeClassesCount = \App\Models\Unit::where('is_published', true)
            ->where(function($query) {
                $query->where('is_free_sample', true)
                      ->orWhere('is_registered_only', true);
            })->count();

        return view('public.library', compact('sessions', 'freeClassesCount'));
    }
}
