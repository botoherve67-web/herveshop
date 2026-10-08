<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::where('is_active', true)
            ->select(['id', 'name', 'slug', 'updated_at'])
            ->with('images')
            ->latest('updated_at')
            ->get();

        return response()
            ->view('seo.sitemap', compact('products'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
