<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
        // If unit is NOT free and user is NOT logged in/subscribed
        if (!$unit->is_free_sample && !auth()->check()) {
            if ($unit->is_registered_only) {
                return redirect()->route('register')->with('info', 'കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി സൗജന്യമായി റജിസ്റ്റർ ചെയ്യൂ (Register for more free classes)');
            }
            return redirect()->route('pricing')->with('warning', 'This premium lesson requires a subscription. Check out our plans below!');
        }

        if (auth()->check()) {
            // Redirect to student panel for better experience (tracking etc)
            return redirect()->route('student.units.show', $unit);
        }

        // Remember last attended free unit for guests (30 days)
        session(['last_attended_unit_id' => $unit->id]);
        Cookie::queue(cookie('amter_last_unit_id', (string) $unit->id, 60 * 24 * 30));

        // It is a free sample, show it in public layout
        return view('public.units.show', [
            'course' => $course,
            'unit' => $unit,
            'libraryId' => config('services.bunny.library_id', '569307')
        ]);
    }
}
