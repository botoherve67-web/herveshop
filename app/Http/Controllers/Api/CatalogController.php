<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home(): JsonResponse
    {
        $categories = Category::whereHas('products', fn ($query) => $query->where('is_active', true))
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->get();

        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['category', 'images'])
            ->latest()
            ->take(6)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::where('is_active', true)
                ->with(['category', 'images'])
                ->latest()
                ->take(6)
                ->get();
        }

        $preorders = Product::where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'precommande')
                  ->orWhere(fn ($sq) => $sq->where('stock', '<=', 0)->where('bascule_auto_precommande', true));
            })
            ->with(['category', 'images'])
            ->latest()
            ->take(6)
            ->get();

        $popularProducts = Product::where('is_active', true)
            ->with(['category', 'images'])
            ->latest()
            ->take(8)
            ->get();

        $recentReviews = Review::where('is_approved', true)
            ->with(['user', 'product'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'author' => $r->user?->name ?? 'Client HerveShop',
                'rating' => $r->note,
                'comment' => $r->commentaire,
                'product_name' => $r->product?->name,
                'date' => $r->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'success' => true,
            'categories' => $categories->map(fn ($c) => $this->formatCategory($c)),
            'featured_products' => $featuredProducts->map(fn ($p) => $this->formatProduct($p)),
            'preorder_products' => $preorders->map(fn ($p) => $this->formatProduct($p)),
            'popular_products' => $popularProducts->map(fn ($p) => $this->formatProduct($p)),
            'recent_reviews' => $recentReviews,
            'banners' => [
                [
                    'id' => 'b1',
                    'title' => 'Bienvenue sur HerveShop',
                    'subtitle' => 'Les meilleurs produits au meilleur prix !',
                    'description' => 'Électronique, mode, beauté, maison avec livraison rapide à Lomé et dans tout le Togo.',
                    'action_text' => 'Découvrir',
                    'action_type' => 'catalog',
                    'color' => '#0969ed',
                ],
                [
                    'id' => 'b2',
                    'title' => 'Système de Précommande',
                    'subtitle' => 'Réservez dès 70% d\'acompte',
                    'description' => 'Payez l\'acompte maintenant, le solde sous 48h à l\'arrivage de vos articles.',
                    'action_text' => 'Voir les précommandes',
                    'action_type' => 'preorder',
                    'color' => '#062b52',
                ],
                [
                    'id' => 'b3',
                    'title' => 'Paiement Flooz & T-Money',
                    'subtitle' => '100% Mobile Money local',
                    'description' => 'Validez vos commandes en toute simplicité via Moov Flooz ou Togocom T-Money.',
                    'action_text' => 'En savoir plus',
                    'action_type' => 'payment_info',
                    'color' => '#0f766e',
                ],
            ],
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])->get();

        return response()->json([
            'success' => true,
            'categories' => $categories->map(fn ($c) => $this->formatCategory($c)),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true)->with(['category', 'images']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('category_slug')) {
            $cat = Category::where('slug', $request->category_slug)->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        if ($request->filled('type')) {
            if ($request->type === 'precommande') {
                $query->where(function ($q) {
                    $q->where('type', 'precommande')
                      ->orWhere(fn ($sq) => $sq->where('stock', '<=', 0)->where('bascule_auto_precommande', true));
                });
            } elseif ($request->type === 'stock') {
                $query->where('type', 'stock')->where('stock', '>', 0);
            }
        }

        if ($request->filled('q')) {
            $search = '%'.$request->q.'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        switch ($request->get('sort')) {
            case 'prix_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'prix_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'recent':
            default:
                $query->latest();
                break;
        }

        $perPage = min((int) ($request->get('per_page', 20)), 50);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'products' => collect($paginator->items())->map(fn ($p) => $this->formatProduct($p)),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function product($idOrSlug): JsonResponse
    {
        $product = Product::where('is_active', true)
            ->where(function ($q) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $q->where('id', $idOrSlug);
                } else {
                    $q->where('slug', $idOrSlug);
                }
            })
            ->with(['category', 'images', 'reviews.user'])
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit introuvable.',
            ], 404);
        }

        $relatedProducts = Product::where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'images'])
            ->take(4)
            ->get();

        return response()->json([
            'success' => true,
            'product' => $this->formatProduct($product, true),
            'related' => $relatedProducts->map(fn ($p) => $this->formatProduct($p)),
        ]);
    }

    private function formatCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description ?? null,
            'products_count' => $category->products_count ?? 0,
        ];
    }

    private function formatProduct(Product $product, bool $includeDetails = false): array
    {
        $isPreorder = $product->estEnPrecommande();
        $images = $product->images->map(function ($img) {
            $path = $img->path;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            return asset('storage/'.$path);
        })->values()->all();

        if (empty($images)) {
            $images = [asset('images/logo.png')];
        }

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'price' => (int) $product->price,
            'price_formatted' => number_format($product->price, 0, ',', ' ').' FCFA',
            'stock' => (int) $product->stock,
            'is_preorder' => $isPreorder,
            'type' => $product->type,
            'acompte_pourcent' => (int) ($product->acompte_pourcent ?: 70),
            'montant_acompte' => $isPreorder ? $product->montantAcompte() : (int) $product->price,
            'montant_solde' => $isPreorder ? $product->montantSolde() : 0,
            'primary_image' => $images[0],
            'images' => $images,
            'rating' => $product->noteMoyenne(),
            'reviews_count' => $product->reviews()->count(),
            'is_featured' => (bool) $product->is_featured,
            'date_cloture_precommande' => $product->date_cloture_precommande?->format('d/m/Y'),
            'date_arrivage_estimee' => $product->date_arrivage_estimee?->format('d/m/Y'),
            'date_expedition_prevue' => $product->date_expedition_prevue?->format('d/m/Y'),
        ];

        if ($includeDetails) {
            $data['description'] = $product->description ?: 'Aucune description détaillée disponible.';
            $data['reviews'] = $product->reviews->map(fn ($r) => [
                'id' => $r->id,
                'author' => $r->user?->name ?? 'Client vérifié',
                'rating' => $r->note,
                'comment' => $r->commentaire,
                'created_at' => $r->created_at?->format('d/m/Y'),
            ]);
        }

        return $data;
    }
}
