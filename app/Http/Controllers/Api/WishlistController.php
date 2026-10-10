<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(): JsonResponse
    {
        $items = WishlistItem::where('user_id', Auth::id())
            ->with(['product.images', 'product.category'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items->map(fn ($it) => [
                'id' => $it->id,
                'product_id' => $it->product_id,
                'product_name' => $it->product?->name,
                'price' => (int) $it->product?->price,
                'price_formatted' => number_format($it->product?->price ?? 0, 0, ',', ' ').' FCFA',
                'is_preorder' => $it->product?->estEnPrecommande() ?? false,
                'stock' => (int) $it->product?->stock,
                'image' => $it->product?->images->first() ? asset('storage/'.$it->product->images->first()->path) : asset('images/logo.png'),
            ]),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $userId = Auth::id();
        $productId = $request->product_id;

        $existing = WishlistItem::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'in_wishlist' => false,
                'message' => 'Produit retiré des favoris.',
            ]);
        }

        WishlistItem::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'success' => true,
            'in_wishlist' => true,
            'message' => 'Produit ajouté à vos favoris.',
        ]);
    }
}
