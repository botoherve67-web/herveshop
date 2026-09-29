<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();
        if ($request->boolean('stock_faible')) {
            $query->where('stock', '<=', 3)->where('type', 'stock');
        }
        $products = $query->paginate(20)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.products.form', ['categories' => $categories, 'product' => new Product]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);

        $product = Product::create($data);

        $this->keepFeaturedProduct($product);

        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Produit créé.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $product->update($data);

        $this->keepFeaturedProduct($product);

        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Produit supprimé.');
    }

    public function setPrimaryImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Image principale mise à jour.');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;
        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($wasPrimary && ($replacement = $product->images()->first())) {
            $replacement->update(['is_primary' => true]);
        }

        return back()->with('success', 'Image supprimée.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'type' => 'required|in:stock,precommande',
            'stock' => 'nullable|integer|min:1|required_if:type,stock',
            'acompte_pourcent' => 'nullable|integer|min:0|max:100|required_if:type,precommande',
            'date_cloture_precommande' => 'nullable|date',
            'date_expedition_prevue' => 'nullable|date',
            'date_arrivage_estimee' => 'nullable|date',
            'bascule_auto_precommande' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        $data['bascule_auto_precommande'] = $request->boolean('bascule_auto_precommande');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['type'] === 'precommande') {
            $data['stock'] = 0;
            $data['bascule_auto_precommande'] = false;
        } else {
            $data['acompte_pourcent'] = 0;
            $data['date_cloture_precommande'] = null;
            $data['date_expedition_prevue'] = null;
            $data['date_arrivage_estimee'] = null;
        }

        return $data;
    }

    protected function keepFeaturedProduct(Product $product): void
    {
        if (! $product->is_featured) {
            return;
        }

        Product::where('id', '!=', $product->id)->update(['is_featured' => false]);
    }

    protected function storeImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $position = (int) $product->images()->max('position') + 1;
        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        if (! $hasPrimary && ($existingImage = $product->images()->first())) {
            $existingImage->update(['is_primary' => true]);
            $hasPrimary = true;
        }

        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'public');
            $product->images()->create([
                'path' => $path,
                'position' => $position++,
                'is_primary' => ! $hasPrimary,
            ]);
            $hasPrimary = true;
        }
    }
}
