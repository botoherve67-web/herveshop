<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $ids = collect($request->session()->get('compare', []))->map(fn ($id) => (int) $id)->unique()->values();
        $products = Product::with(['category', 'images'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (Product $product) => $ids->search($product->id))
            ->values();

        return view('products.compare', compact('products'));
    }

    public function store(Request $request, Product $product)
    {
        $ids = collect($request->session()->get('compare', []))->map(fn ($id) => (int) $id)->unique();

        if (! $ids->contains($product->id)) {
            abort_if($ids->count() >= 4, 422, 'Vous pouvez comparer au maximum 4 produits.');
            $ids->push($product->id);
        }

        $request->session()->put('compare', $ids->values()->all());

        return back()->with('success', 'Produit ajouté à la comparaison.');
    }

    public function destroy(Request $request, Product $product)
    {
        $ids = collect($request->session()->get('compare', []))
            ->reject(fn ($id) => (int) $id === $product->id)
            ->values()
            ->all();
        $request->session()->put('compare', $ids);

        return back()->with('success', 'Produit retiré de la comparaison.');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('compare');

        return back()->with('success', 'Comparaison vidée.');
    }
}