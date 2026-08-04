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
        // Since units belong to courses, we need them for public preview links
        // We only show units that are part of a course
        $units = Unit::whereHas('courseSession.module.course')->get();

        return response()->view('sitemap', [
            'courses' => $courses,
            'units' => $units,
        ])->header('Content-Type', 'text/xml');
    }
}
