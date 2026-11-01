<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\LearningTrack;
use App\Models\Unit;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application landing page.
     */
    public function index()
    {
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

    /**
     * Movie-platform style Course Sessions Library for both First-Comers (Guests) and Logged-in Students.
     */
    public function library(Request $request)
    {
        $libraryData = self::buildMovieLibraryData($request);

        return view('public.library', $libraryData);
    }

    /**
     * Shared builder for the Movie-Platform style Course Sessions data (used by /library and Student Dashboard).
     */
    public static function buildMovieLibraryData(?Request $request = null): array
    {
        $request = $request ?? request();
        $user = auth()->user();

        $sessions = CourseSession::with(['module', 'units' => function ($query) {
            $query->where('is_published', true)->orderBy('sort_order', 'asc');
        }])
            ->withCount(['units' => function ($q) {
                $q->where('is_published', true);
            }])
            ->whereHas('units', function ($q) {
                $q->where('is_published', true);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $completedUnitIds = $user
            ? $user->completedUnits()->pluck('units.id')->flip()->toArray()
            : [];

        // 1. Identify the last attended unit (from session/cookie or most recent completion)
        $cookieOrSessionUnitId = session('last_attended_unit_id') ?: $request->cookie('amter_last_unit_id');
        $lastAttendedUnit = null;
        $hasHistory = false;

        if ($cookieOrSessionUnitId) {
            $candidate = Unit::with('courseSession.module')
                ->where('is_published', true)
                ->find((int) $cookieOrSessionUnitId);

            if ($candidate && $candidate->courseSession) {
                $hasHistory = true;
                // If the user already completed this candidate unit, advance to the next incomplete unit in that session
                if ($user && isset($completedUnitIds[$candidate->id])) {
                    $nextInSession = Unit::with('courseSession.module')
                        ->where('course_session_id', $candidate->course_session_id)
                        ->where('is_published', true)
                        ->where('sort_order', '>', $candidate->sort_order)
                        ->whereNotIn('id', array_keys($completedUnitIds))
                        ->orderBy('sort_order', 'asc')
                        ->first();
                    $lastAttendedUnit = $nextInSession ?: $candidate;
                } else {
                    $lastAttendedUnit = $candidate;
                }
            }
        }

        if (!$lastAttendedUnit && $user) {
            $lastCompleted = $user->completedUnits()
                ->with('courseSession.module')
                ->where('is_published', true)
                ->orderBy('completed_units.completed_at', 'desc')
                ->first();

            if ($lastCompleted && $lastCompleted->courseSession) {
                $hasHistory = true;
                $nextInSession = Unit::with('courseSession.module')
                    ->where('course_session_id', $lastCompleted->course_session_id)
                    ->where('is_published', true)
                    ->where('sort_order', '>', $lastCompleted->sort_order)
                    ->whereNotIn('id', array_keys($completedUnitIds))
                    ->orderBy('sort_order', 'asc')
                    ->first();

                $lastAttendedUnit = $nextInSession ?: $lastCompleted;
            }
        }

        $lastAttendedSessionId = $lastAttendedUnit?->course_session_id;

        // 2. Enrich each session with progress, smart per-session resume unit, and episode drawer list
        $sessionsPayloadForModal = [];

        foreach ($sessions as $session) {
            $units = $session->units;
            $totalUnits = $units->count();
            $completedCount = $units->filter(fn ($u) => isset($completedUnitIds[$u->id]))->count();
            $progressPercent = $totalUnits > 0 ? (int) min(100, round(($completedCount / $totalUnits) * 100)) : 0;

            // Determine smart resume unit for THIS session
            $resumeUnit = null;
            if ($lastAttendedUnit && (int) $lastAttendedUnit->course_session_id === (int) $session->id) {
                $resumeUnit = $units->firstWhere('id', $lastAttendedUnit->id);
            }
            if (!$resumeUnit && $completedCount > 0) {
                $resumeUnit = $units->first(fn ($u) => !isset($completedUnitIds[$u->id])) ?: $units->last();
            }
            if (!$resumeUnit) {
                $resumeUnit = $units->first();
            }

            $session->completed_count = $completedCount;
            $session->progress_percent = $progressPercent;
            $session->resume_unit = $resumeUnit;
            $session->is_last_attended = $hasHistory && $lastAttendedSessionId && ((int) $session->id === (int) $lastAttendedSessionId);
            $session->is_started = $completedCount > 0 || $session->is_last_attended;

            // Build target URL & action for the session's smart resume unit
            [$targetUrl, $onclick, $ctaLabel] = self::resolveUnitAction($session, $resumeUnit, $user, $session->is_started, $completedCount, $totalUnits);
            $session->target_url = $targetUrl;
            $session->target_onclick = $onclick;
            $session->cta_label = $ctaLabel;

            // Build episodes list for the Netflix-style Episodes Drawer
            $episodes = [];
            foreach ($units as $u) {
                $rawTitle = trim((string) $u->title);
                $displayTitle = ctype_digit($rawTitle) ? "Lesson {$rawTitle}" : $rawTitle;
                $isCompleted = isset($completedUnitIds[$u->id]);
                $isCurrent = $resumeUnit && (int) $resumeUnit->id === (int) $u->id;
                $isAccessible = $u->isAccessibleBy($user);

                $epUrl = '#';
                $epAction = 'link'; // 'link' | 'register' | 'pricing'
                if ($user) {
                    if ($isAccessible) {
                        $epUrl = route('student.units.show', $u);
                    } else {
                        $epUrl = route('pricing');
                        $epAction = 'pricing';
                    }
                } else {
                    if ($u->is_free_sample) {
                        $epUrl = route('public.unit.show', ['course' => $session->module->course_id ?? 1, 'unit' => $u]);
                    } elseif ($u->is_registered_only) {
                        $epAction = 'register';
                    } else {
                        $epUrl = route('pricing');
                        $epAction = 'pricing';
                    }
                }

                $episodes[] = [
                    'id' => $u->id,
                    'order' => (int) $u->sort_order,
                    'title' => $displayTitle,
                    'completed' => $isCompleted,
                    'current' => $isCurrent,
                    'free_guest' => (bool) $u->is_free_sample,
                    'free_registered' => (bool) $u->is_registered_only,
                    'accessible' => $isAccessible,
                    'url' => $epUrl,
                    'action' => $epAction,
                    'edit_url' => ($user && $user->isAdmin())
                        ? (\App\Filament\Resources\UnitResource::getUrl('edit', ['record' => $u->id]) . '?session_id=' . $session->id)
                        : null,
                ];
            }

            $sessionsPayloadForModal[$session->id] = [
                'id' => $session->id,
                'title' => $session->title,
                'module' => $session->module->name ?? 'Course Session',
                'description' => $session->description ?? '',
                'total' => $totalUnits,
                'completed' => $completedCount,
                'percent' => $progressPercent,
                'cta_url' => $targetUrl,
                'cta_onclick' => $onclick,
                'cta_label' => $ctaLabel,
                'episodes' => $episodes,
            ];
        }

        // 3. Strictly preserve the Admin's CourseSession Order (sort_order) in the Library Grid
        $sortedSessions = $sessions->sortBy(function ($session) {
            return (int) $session->sort_order;
        })->values();

        // 4. Determine Featured / Top Hero Session (Last Attended Session if any, otherwise first session in Admin order)
        $heroSession = $sortedSessions->firstWhere('is_last_attended', true)
            ?: $sortedSessions->first(fn ($s) => $s->units->contains('is_free_sample', true))
            ?: $sortedSessions->first();

        $freeClassesCount = Unit::where('is_published', true)
            ->where(function ($query) {
                $query->where('is_free_sample', true)
                    ->orWhere('is_registered_only', true);
            })->count();

        $primaryTrack = LearningTrack::getPrimaryTrack();
        if ($primaryTrack) {
            $primaryTrack->loadCount('trackUnits');
        }

        return [
            'sessions' => $sortedSessions,
            'heroSession' => $heroSession,
            'hasHistory' => $hasHistory,
            'freeClassesCount' => $freeClassesCount,
            'primaryTrack' => $primaryTrack,
            'sessionsModalData' => $sessionsPayloadForModal,
        ];
    }

    /**
     * Resolve the URL, onclick handler, and button label for a session's target unit.
     */
    protected static function resolveUnitAction($session, ?Unit $unit, $user, bool $isStarted, int $completedCount, int $totalUnits): array
    {
        if (!$unit) {
            return ['#', '', 'Coming Soon'];
        }

        $rawTitle = trim((string) $unit->title);
        $lessonLabel = ctype_digit($rawTitle) ? "Lesson {$rawTitle}" : $rawTitle;

        if ($user) {
            if ($unit->isAccessibleBy($user)) {
                $url = route('student.units.show', $unit);
                if ($completedCount >= $totalUnits && $totalUnits > 0) {
                    return [$url, '', "Replay ({$lessonLabel})"];
                }
                if ($isStarted) {
                    return [$url, '', "Resume {$lessonLabel}"];
                }
                return [$url, '', "Start {$lessonLabel}"];
            }

            return [
                route('pricing'),
                "if(!confirm('Select a plan and get unlimited access to all classes.')) return false;",
                'Unlock Premium',
            ];
        }

        // Guest visitor
        if ($unit->is_free_sample) {
            $url = route('public.unit.show', ['course' => $session->module->course_id ?? 1, 'unit' => $unit]);
            return [
                $url,
                '',
                $isStarted ? "Resume {$lessonLabel} (Free)" : "Watch Free ({$lessonLabel})",
            ];
        }

        // Check if session has ANY guest-free unit we can start with for a first-time guest
        $firstGuestFree = $session->units->firstWhere('is_free_sample', true);
        if ($firstGuestFree) {
            $freeTitle = trim((string) $firstGuestFree->title);
            $freeLabel = ctype_digit($freeTitle) ? "Lesson {$freeTitle}" : $freeTitle;
            $url = route('public.unit.show', ['course' => $session->module->course_id ?? 1, 'unit' => $firstGuestFree]);
            return [$url, '', "Try Free ({$freeLabel})"];
        }

        if ($unit->is_registered_only || $session->units->contains('is_registered_only', true)) {
            return ['javascript:void(0)', 'openRegisterModal(); return false;', 'Free Sign-Up to Watch'];
        }

        return [
            route('pricing'),
            "if(!confirm('Select a plan and get unlimited access to the classes.')) return false;",
            'Premium Unlock',
        ];
    }
}
