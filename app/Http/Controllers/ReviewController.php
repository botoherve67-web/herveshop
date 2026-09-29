<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $product->reviews()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'note' => $request->note,
                'commentaire' => $request->commentaire,
                'is_approved' => false,
                'report_count' => 0,
                'report_reason' => null,
                'reported_at' => null,
            ]
        );

        return back()->with('success', 'Avis soumis. Il sera visible après validation.');
    }

    public function report(Request $request, Product $product, \App\Models\Review $review)
    {
        abort_unless($review->product_id === $product->id, 404);

        $data = $request->validate([
            'report_reason' => 'required|string|max:500',
        ]);

        $review->increment('report_count');
        $review->update([
            'report_reason' => $data['report_reason'],
            'reported_at' => now(),
        ]);

        return back()->with('success', 'Le signalement a été transmis à notre équipe.');
    }
}
