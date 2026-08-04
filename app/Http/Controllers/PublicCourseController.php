<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Unit;
use Illuminate\Http\Request;

class PublicCourseController extends Controller
{
    public function show(Course $course)
    {
        // Load course with modules, sessions, and units
        $course->load(['modules' => function($q) {
            $q->orderBy('sort_order');
        }, 'modules.courseSessions' => function($q) {
            $q->orderBy('sort_order');
        }, 'modules.courseSessions.units' => function($q) {
            $q->where('is_published', true)->orderBy('sort_order');
        }]);

        return view('public.courses.show', compact('course'));
    }

    public function showUnit(Course $course, Unit $unit)
    {
        // Ensure unit belongs to course (optional check, but good for data integrity)
        // For now, assuming direct access is fine for simplicity or check relation if needed.

        // If unit is NOT free and user is NOT logged in/subscribed
        if (!$unit->is_free_sample && !auth()->check()) {
            if ($unit->is_registered_only) {
                return redirect()->route('login')->with('info', 'Please create a free account to access this lesson.');
            }
            return redirect()->route('pricing')->with('warning', 'This premium lesson requires a subscription. Check out our plans below!');
        }

        // If logged in, we should ideally use the Student Panel view, but for "Public Explorer" feel:
        // If they are logged in but don't have subscription? The CheckSubscription middleware handles the student panel.
        // If they access this public route while logged in, maybe we just show the content or redirect to student panel?
        
        if (auth()->check()) {
            // Redirect to student panel for better experience (tracking etc)
            return redirect()->route('student.units.show', $unit);
        }

        // It is a free sample, show it in public layout
        return view('public.units.show', [
            'course' => $course,
            'unit' => $unit,
            'libraryId' => config('services.bunny.library_id', '569307')
        ]);
    }
}
