<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::with('categories')->latest()->paginate(20);
        $categories = Category::orderBy('name')->get();

        return view('admin.promo-codes.index', compact('promoCodes', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:promo_codes,code',
            'type' => 'required|in:pourcentage,montant',
            'valeur' => 'required|integer|min:1',
            'usage_max' => 'nullable|integer|min:1',
            'commence_le' => 'nullable|date|before_or_equal:expire_le',
            'expire_le' => 'nullable|date',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);
        $promoCode = PromoCode::create($data);
        $promoCode->categories()->sync($categoryIds);

        return back()->with('success', 'Code promo créé.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return back()->with('success', 'Code promo supprimé.');
    }
}
