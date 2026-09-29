<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryPageController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->with(['products' => fn ($query) => $query->where('is_active', true)->with('images')->latest()->take(1)])
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }
}