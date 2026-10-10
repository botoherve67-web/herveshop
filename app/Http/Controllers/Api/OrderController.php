<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use App\Notifications\AdminOrderNotification;
use App\Notifications\OrderUpdateNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    protected array $fraisParZone = [
        'lome_centre' => 1000,
        'lome_peripherie' => 1500,
        'interieur_togo' => 3000,
        'hors_togo' => 5000,
    ];

    public function index(): JsonResponse
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product.images'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'orders' => collect($orders->items())->map(fn ($o) => $this->formatOrderSummary($o)),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $order = Order::where('user_id', Auth::id())
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id);
                } else {
                    $q->where('reference', $id);
                }
            })
            ->with(['items.product.images', 'statusHistory.user'])
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $this->formatOrderDetail($order),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
            'mode_livraison' => 'required|in:domicile,point_retrait',
            'zone_livraison' => 'required_if:mode_livraison,domicile|nullable|string',
            'adresse_livraison' => 'required_if:mode_livraison,domicile|nullable|string',
            'point_retrait' => 'required_if:mode_livraison,point_retrait|nullable|string',
            'moyen_paiement' => 'required|in:flooz,tmoney',
            'code_promo' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $order = DB::transaction(function () use ($data) {
                $orderItemsData = [];
                $sousTotal = 0;
                $estPrecommande = false;
                $acompteTotal = 0;
                $soldeTotal = 0;

                foreach ($data['items'] as $itemInput) {
                    $product = Product::whereKey($itemInput['product_id'])->lockForUpdate()->first();
                    if (! $product || ! $product->is_active) {
                        throw new \RuntimeException('Le produit sélectionné n\'est plus disponible.');
                    }

                    $quantity = (int) $itemInput['quantity'];

                    if ($product->type === 'stock' && ! $product->estEnPrecommande() && $quantity > $product->stock) {
                        throw new \RuntimeException("Stock insuffisant pour {$product->name} (restant: {$product->stock}).");
                    }

                    $lineTotal = $product->price * $quantity;
                    $sousTotal += $lineTotal;

                    if ($product->estEnPrecommande()) {
                        $estPrecommande = true;
                        $acompteTotal += $product->montantAcompte() * $quantity;
                        $soldeTotal += $product->montantSolde() * $quantity;
                    }

                    $orderItemsData[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                    ];
                }

                $reduction = 0;
                $codePromo = null;
                if (! empty($data['code_promo'])) {
                    $promo = PromoCode::where('code', trim($data['code_promo']))->first();
                    if ($promo && $promo->estValide()) {
                        $promoCategoryIds = $promo->categories()->pluck('categories.id');
                        $eligibleTotal = $promoCategoryIds->isEmpty()
                            ? $sousTotal
                            : collect($orderItemsData)
                                ->filter(fn ($it) => $promoCategoryIds->contains($it['product']->category_id))
                                ->sum(fn ($it) => $it['unit_price'] * $it['quantity']);
                        $reduction = $promo->calculerReduction($eligibleTotal);
                        $codePromo = $promo->code;
                        $promo->increment('usage_actuel');
                    }
                }

                $fraisLivraison = ($data['mode_livraison'] === 'domicile')
                    ? ($this->fraisParZone[$data['zone_livraison'] ?? 'lome_centre'] ?? 1000)
                    : 0;

                $total = max(0, $sousTotal - $reduction) + $fraisLivraison;
                $montantAcompte = $estPrecommande ? ($acompteTotal + $fraisLivraison) : $total;
                $montantSolde = $estPrecommande ? max(0, $soldeTotal - $reduction) : 0;

                $order = Order::create([
                    'reference' => 'HS-'.strtoupper(Str::random(8)),
                    'user_id' => Auth::id(),
                    'type' => $estPrecommande ? 'precommande' : 'stock',
                    'mode_livraison' => $data['mode_livraison'],
                    'zone_livraison' => $data['zone_livraison'] ?? null,
                    'frais_livraison' => $fraisLivraison,
                    'adresse_livraison' => $data['adresse_livraison'] ?? null,
                    'point_retrait' => $data['point_retrait'] ?? null,
                    'sous_total' => $sousTotal,
                    'reduction' => $reduction,
                    'code_promo' => $codePromo,
                    'total' => $total,
                    'montant_acompte' => $montantAcompte,
                    'montant_solde' => $montantSolde,
                    'solde_echeance_at' => $estPrecommande ? now()->addHours(48) : null,
                    'moyen_paiement' => $data['moyen_paiement'],
                    'statut_paiement' => 'en_attente',
                    'statut' => 'en_attente',
                ]);

                foreach ($orderItemsData as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                    ]);

                    if ($item['product']->type === 'stock' && ! $item['product']->estEnPrecommande()) {
                        $item['product']->decrement('stock', $item['quantity']);
                    }
                }

                return $order;
            });

            // Notifications
            try {
                $user = Auth::user();
                $user?->notify(new OrderUpdateNotification($order, 'commande'));
            } catch (\Throwable $e) {
                Log::warning('Email client order notification skipped in API: '.$e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Commande validée avec succès. Veuillez procéder au paiement.',
                'order' => $this->formatOrderDetail($order),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Une erreur est survenue lors de la création de la commande.',
            ], 422);
        }
    }

    public function submitPaymentProof(Request $request, $id): JsonResponse
    {
        $order = Order::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'transaction_id' => 'required|string|max:100',
            'preuve_paiement' => 'required|image|max:10240', // 10MB max
        ]);

        if ($order->preuve_paiement_path) {
            try {
                Storage::disk('private')->delete($order->preuve_paiement_path);
            } catch (\Throwable) {}
        }

        $path = $request->file('preuve_paiement')->store('payment-proofs', 'private');

        $order->update([
            'transaction_id' => $data['transaction_id'],
            'preuve_paiement_path' => $path,
            'preuve_paiement_envoyee_at' => now(),
        ]);

        try {
            $user = Auth::user();
            $user?->notify(new OrderUpdateNotification($order, 'preuve_paiement'));
        } catch (\Throwable) {}

        return response()->json([
            'success' => true,
            'message' => 'Preuve de paiement enregistrée avec succès. Elle sera vérifiée sous peu.',
            'order' => $this->formatOrderDetail($order),
        ]);
    }

    public function verifyPromo(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'montant' => 'required|numeric|min:0',
        ]);

        $promo = PromoCode::where('code', trim($request->code))->first();

        if (! $promo || ! $promo->estValide()) {
            return response()->json([
                'success' => false,
                'message' => 'Code promo invalide ou expiré.',
            ], 422);
        }

        $reduction = $promo->calculerReduction((float) $request->montant);

        return response()->json([
            'success' => true,
            'code' => $promo->code,
            'reduction' => $reduction,
            'reduction_formatted' => number_format($reduction, 0, ',', ' ').' FCFA',
            'nouveau_total' => max(0, $request->montant - $reduction),
        ]);
    }

    private function formatOrderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'type' => $order->type,
            'items_count' => $order->items->sum('quantity'),
            'total' => (int) $order->total,
            'total_formatted' => number_format($order->total, 0, ',', ' ').' FCFA',
            'montant_acompte' => (int) $order->montant_acompte,
            'montant_solde' => (int) $order->montant_solde,
            'statut' => $order->statut,
            'statut_paiement' => $order->statut_paiement,
            'moyen_paiement' => $order->moyen_paiement,
            'has_proof' => ! empty($order->preuve_paiement_path),
            'created_at' => $order->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function formatOrderDetail(Order $order): array
    {
        $items = $order->items->map(function ($item) {
            $img = $item->product?->images->first();
            $imgPath = $img ? (str_starts_with($img->path, 'http') ? $img->path : asset('storage/'.$img->path)) : asset('images/logo.png');

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'unit_price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'total_price' => (int) ($item->unit_price * $item->quantity),
                'image' => $imgPath,
            ];
        });

        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'type' => $order->type,
            'statut' => $order->statut,
            'statut_paiement' => $order->statut_paiement,
            'moyen_paiement' => $order->moyen_paiement,
            'transaction_id' => $order->transaction_id,
            'has_proof' => ! empty($order->preuve_paiement_path),
            'preuve_paiement_envoyee_at' => $order->preuve_paiement_envoyee_at?->format('d/m/Y H:i'),
            'mode_livraison' => $order->mode_livraison,
            'zone_livraison' => $order->zone_livraison,
            'adresse_livraison' => $order->adresse_livraison,
            'point_retrait' => $order->point_retrait,
            'sous_total' => (int) $order->sous_total,
            'frais_livraison' => (int) $order->frais_livraison,
            'reduction' => (int) $order->reduction,
            'code_promo' => $order->code_promo,
            'total' => (int) $order->total,
            'total_formatted' => number_format($order->total, 0, ',', ' ').' FCFA',
            'montant_acompte' => (int) $order->montant_acompte,
            'montant_solde' => (int) $order->montant_solde,
            'solde_echeance_at' => $order->solde_echeance_at?->format('d/m/Y H:i'),
            'items' => $items,
            'created_at' => $order->created_at?->format('d/m/Y H:i'),
        ];
    }
}
