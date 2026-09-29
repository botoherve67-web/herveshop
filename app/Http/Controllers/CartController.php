<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }
            $lineTotal = $product->price * $quantity;
            $total += $lineTotal;
            $items[] = compact('product', 'quantity', 'lineTotal');
        }

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $quantity;
        session(['cart' => $cart]);

        return back()->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request, Product $product)
    {
        $quantity = max(0, (int) $request->input('quantity', 1));
        $cart = session('cart', []);

        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $quantity;
        }

        session(['cart' => $cart]);

        return back();
    }

    public function remove(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back();
    }
}
