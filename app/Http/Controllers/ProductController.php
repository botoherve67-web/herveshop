<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'images')->where('is_active', true);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->categorie));
        }

        $availability = $request->input('disponibilite', $request->input('type'));
        if ($availability) {
            if ($availability === 'precommande') {
                $query->where(function ($q) {
                    $q->where('type', 'precommande')
                        ->orWhere(function ($q2) {
                            $q2->where('stock', '<=', 0)->where('bascule_auto_precommande', true);
                        });
                });
            } elseif ($availability === 'stock') {
                $query->where('type', 'stock')->where('stock', '>', 0);
            } elseif ($availability === 'rupture') {
                $query->where('stock', '<=', 0)->where('type', 'stock');
            }
        }

        if ($request->filled('prix_min')) {
            $query->where('price', '>=', (int) $request->prix_min);
        }
        if ($request->filled('prix_max')) {
            $query->where('price', '<=', (int) $request->prix_max);
        }
        if ($request->filled('stock_min')) {
            $query->where('stock', '>=', (int) $request->stock_min);
        }

        $sort = $request->get('tri', 'recent');
        match ($sort) {
            'prix_asc' => $query->orderBy('price', 'asc'),
            'prix_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();
        $featuredProducts = Product::where('is_active', true)
            ->with('images')
            ->latest()
            ->take(4)
            ->get();

        return view('products.index', compact('products', 'categories', 'featuredProducts'));
    }

    public function show(string $slug)
    {
        $product = Product::with(['category', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $similaires = Product::where('category_id', $product->category_id)
            ->with(['images', 'category'])
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderByRaw('ABS(price - ?) ASC', [$product->price])
            ->take(4)
            ->get();
        $savedList = Auth::check()
            ? $product->wishlistItems()->where('user_id', Auth::id())->value('list_type')
            : null;

        return view('products.show', compact('product', 'similaires', 'savedList'));
    }
}
