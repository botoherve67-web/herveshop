<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user'])->latest();

        if ($request->status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($request->status === 'reported') {
            $query->where('report_count', '>', 0);
        } elseif ($request->status === 'approved') {
            $query->where('is_approved', true);
        }

        $reviews = $query->paginate(20)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->update([
            'is_approved' => true,
            'report_count' => 0,
            'report_reason' => null,
            'reported_at' => null,
        ]);

        return back()->with('success', 'Avis approuvé.');
    }

    public function dismissReport(Review $review)
    {
        $review->update([
            'report_count' => 0,
            'report_reason' => null,
            'reported_at' => null,
        ]);

        return back()->with('success', 'Signalement classé.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Avis supprimé.');
    }
}