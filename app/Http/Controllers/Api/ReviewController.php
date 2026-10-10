<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $data = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'note' => $data['note'],
            'commentaire' => $data['commentaire'] ?? null,
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merci pour votre avis ! Il a été publié.',
            'review' => [
                'id' => $review->id,
                'author' => Auth::user()->name,
                'note' => $review->note,
                'commentaire' => $review->commentaire,
                'date' => now()->format('d/m/Y'),
            ],
        ], 201);
    }
}
