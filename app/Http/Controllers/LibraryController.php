<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index()
    {
        // Fetch Course Sessions (Titles) for the Library
        $sessions = \App\Models\CourseSession::with('module')
            ->withCount('units')
            ->orderBy('sort_order')
            ->get();

        return view('student.library', compact('sessions'));
    }
    
    public function show($id)
    {
        try {
            // Manual lookup using ID
            $module = \App\Models\Module::find($id);

            // Fetch all sessions (client-side pagination will handle UI)
            $sessions = $module->courseSessions()
                ->with(['units' => function($query) {
                    $query->where('is_published', true)->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();

            // Render inside try/catch to capture view errors
            if (view()->exists('student.modules.show')) {
                 $view = view('student.modules.show', compact('module', 'sessions'));
                 return response($view->render()); // Force render to catch blade errors here
            } else {
                 throw new \Exception("View student.modules.show not found.");
            }

        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error("LibraryController Show Error: " . $e->getMessage(), [
                 'id' => $id,
                 'trace' => $e->getTraceAsString()
             ]);
             
             if (config('app.debug')) {
                 throw $e; // Re-throw in debug mode for Whoops page
             }
             
             return response()->make("<h2>Server Error</h2><p>We encountered an issue loading this module. Please try again later.</p>", 500);
        }
    }
}
