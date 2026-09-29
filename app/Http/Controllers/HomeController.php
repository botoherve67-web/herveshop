<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $nouveautes = Product::where('is_active', true)->with(['images', 'category'])->latest()->take(8)->get();
        $avis = Review::with('user')->where('is_approved', true)->latest()->take(3)->get();
        $heroProducts = $nouveautes->take(4);
        $popularProducts = $nouveautes->take(5);
        $promoCategories = $categories->filter(function (Category $category) {
            return str_contains(strtolower($category->name), 'électronique')
                || str_contains(strtolower($category->name), 'mode');
        })->values();

        return view('home', compact('categories', 'heroProducts', 'popularProducts', 'avis', 'promoCategories'));
    }
}
