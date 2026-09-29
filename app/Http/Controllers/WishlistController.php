<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Auth::user()->wishlistItems()->with(['product.images'])->latest()->get();
        $favorites = $items->where('list_type', 'favorite');
        $later = $items->where('list_type', 'later');

        return view('account.wishlist', compact('favorites', 'later'));
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate(['list_type' => 'required|in:favorite,later']);
        $item = WishlistItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($item?->list_type === $data['list_type']) {
            $item->delete();
        } else {
            WishlistItem::updateOrCreate(
                ['user_id' => Auth::id(), 'product_id' => $product->id],
                ['list_type' => $data['list_type']]
            );
        }

        return back()->with('success', $data['list_type'] === 'favorite'
            ? 'Produit ajouté aux favoris.'
            : 'Produit enregistré pour plus tard.');
    }

    public function update(Request $request, WishlistItem $wishlistItem)
    {
        abort_unless($wishlistItem->user_id === Auth::id(), 403);
        $data = $request->validate(['list_type' => 'required|in:favorite,later']);
        $wishlistItem->update($data);

        return back()->with('success', 'Liste mise à jour.');
    }

    public function destroy(WishlistItem $wishlistItem)
    {
        abort_unless($wishlistItem->user_id === Auth::id(), 403);
        $wishlistItem->delete();

        return back()->with('success', 'Produit retiré de votre liste.');
    }
}