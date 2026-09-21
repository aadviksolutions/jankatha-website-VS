<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'area' => 'Dashboard',
            'stats' => [
                'total' => News::count(),
                'published' => News::where('status', 'published')->count(),
                'drafts' => News::where('status', 'draft')->count(),
                'scheduled' => News::where('status', 'scheduled')->count(),
                'breaking' => News::where('is_breaking', true)->count(),
                'categories' => Category::count(),
            ],
        ]);
    }

    public function admin(): View
    {
        return view('dashboard', ['area' => 'Admin']);
    }

    public function editor(): View
    {
        return view('dashboard', ['area' => 'Editor']);
    }
}
