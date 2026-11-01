<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Unit;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $courses = Course::all();

        // Only include publicly accessible 200-OK free sample units in sitemap.xml
        // (Excluding locked/redirecting units prevents Google Search Console 302 penalties)
        $units = Unit::with('courseSession.module.course')
            ->where('is_published', true)
            ->where('is_free_sample', true)
            ->whereHas('courseSession.module.course')
            ->orderBy('id', 'asc')
            ->get();

        return response()->view('sitemap', [
            'courses' => $courses,
            'units' => $units,
        ])->header('Content-Type', 'text/xml');
    }
}
