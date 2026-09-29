<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class AboutController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::where('is_active', true)->count(),
            'clients' => User::where('is_admin', false)->count(),
            'rating' => number_format((float) Review::where('is_approved', true)->avg('note'), 1, ',', ' '),
        ];
        $showcaseProducts = Product::where('is_active', true)->with('images')->latest()->take(4)->get();

        return view('about.index', compact('stats', 'showcaseProducts'));
    }
}