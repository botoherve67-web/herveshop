<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use App\Notifications\AdminOrderNotification;
use App\Notifications\OrderUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Frais de livraison fixes par zone (à ajuster depuis l'admin plus tard)
    protected array $fraisParZone = [
        'lome_centre' => 1000,
        'lome_peripherie' => 1500,
        'interieur_togo' => 3000,
        'hors_togo' => 5000,
    ];

    public function checkout()
    {
        $cart = $this->cleanCart();
        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $items = [];
        $total = 0;
        $estPrecommande = false;

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);
            if (! $product) {
                continue;
            }
            if ($product->estEnPrecommande()) {
                $estPrecommande = true;
            }
            $lineTotal = $product->price * $quantity;
            $total += $lineTotal;
            $items[] = compact('product', 'quantity', 'lineTotal');
        }

        return view('checkout.index', [
            'items' => $items,
            'total' => $total,
            'estPrecommande' => $estPrecommande,
            'zones' => $this->fraisParZone,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mode_livraison' => 'required|in:domicile,point_retrait',
            'zone_livraison' => 'required_if:mode_livraison,domicile|nullable|string',
            'adresse_livraison' => 'required_if:mode_livraison,domicile|nullable|string',
            'point_retrait' => 'required_if:mode_livraison,point_retrait|nullable|string',
            'moyen_paiement' => 'required|in:flooz,tmoney',
            'code_promo' => 'nullable|string',
        ]);

        $cart = $this->cleanCart();
        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $order = DB::transaction(function () use ($request, $cart) {
            $items = [];
            $sousTotal = 0;
            $estPrecommande = false;
            $acompteTotal = 0;
            $soldeTotal = 0;

            foreach ($cart as $productId => $quantity) {
                $product = Product::find($productId);
                if (! $product) {
                    continue;
                }
                $lineTotal = $product->price * $quantity;
                $sousTotal += $lineTotal;

                if ($product->estEnPrecommande()) {
                    $estPrecommande = true;
                    $acompteTotal += $product->montantAcompte() * $quantity;
                    $soldeTotal += $product->montantSolde() * $quantity;
                }

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ];
            }

            $reduction = 0;
            $codePromo = null;
            if ($request->filled('code_promo')) {
                $promo = PromoCode::where('code', $request->code_promo)->first();
                if ($promo && $promo->estValide()) {
                    $promoCategoryIds = $promo->categories()->pluck('categories.id');
                    $eligibleTotal = $promoCategoryIds->isEmpty()
                        ? $sousTotal
                        : collect($items)
                            ->filter(fn (array $item) => $promoCategoryIds->contains($item['product']->category_id))
                            ->sum(fn (array $item) => $item['unit_price'] * $item['quantity']);
                    $reduction = $promo->calculerReduction($eligibleTotal);
                    $codePromo = $promo->code;
                    $promo->increment('usage_actuel');
                }
            }

            $fraisLivraison = $request->mode_livraison === 'domicile'
                ? ($this->fraisParZone[$request->zone_livraison] ?? 1000)
                : 0;

            $total = $sousTotal - $reduction + $fraisLivraison;
            $montantAcompte = $estPrecommande ? $acompteTotal : $total;
            $montantSolde = $estPrecommande ? ($soldeTotal + $fraisLivraison - $reduction) : 0;

            $order = Order::create([
                'reference' => 'HS-'.strtoupper(Str::random(8)),
                'user_id' => Auth::id(),
                'type' => $estPrecommande ? 'precommande' : 'stock',
                'mode_livraison' => $request->mode_livraison,
                'zone_livraison' => $request->zone_livraison,
                'frais_livraison' => $fraisLivraison,
                'adresse_livraison' => $request->adresse_livraison,
                'point_retrait' => $request->point_retrait,
                'sous_total' => $sousTotal,
                'reduction' => $reduction,
                'code_promo' => $codePromo,
                'total' => $total,
                'montant_acompte' => $montantAcompte,
                'montant_solde' => $montantSolde,
                'solde_echeance_at' => $estPrecommande ? now()->addHours(48) : null,
                'moyen_paiement' => $request->moyen_paiement,
                'statut_paiement' => 'en_attente',
                'statut' => 'en_attente',
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                ]);

                if ($item['product']->type === 'stock' && $item['product']->stock > 0) {
                    $item['product']->decrement('stock', $item['quantity']);
                }
            }

            return $order;
        });

        session()->forget('cart');
        $order->load('user');
        $order->user->notify(new OrderUpdateNotification($order, 'commande'));
        $this->notifyAdmins($order, 'nouvelle_commande');

        return redirect()->route('orders.show', $order)->with('success', 'Commande enregistrée. Confirmez le paiement '.strtoupper($order->moyen_paiement).' pour valider.');
    }

    public function show(Order $order)
    {
        $this->authorizeOwner($order);

        $order->load(['items.product.images', 'statusHistory.user']);

        return view('orders.show', compact('order'));
    }

    public function submitPaymentProof(Request $request, Order $order)
    {
        $this->authorizeOwner($order);

        $data = $request->validate([
            'transaction_id' => 'required|string|max:100',
            'preuve_paiement' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($order->preuve_paiement_path) {
            Storage::disk('private')->delete($order->preuve_paiement_path);
        }

        $path = $request->file('preuve_paiement')->store('payment-proofs', 'private');
        $order->update([
            'transaction_id' => $data['transaction_id'],
            'preuve_paiement_path' => $path,
            'preuve_paiement_envoyee_at' => now(),
        ]);
        $order->load('user');
        $this->notifyAdmins($order, 'preuve_paiement');

        return back()->with('success', 'Preuve de paiement envoyée. Elle sera vérifiée par notre équipe.');
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product.images'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    protected function authorizeOwner(Order $order): void
    {
        abort_unless($order->user_id === Auth::id() || Auth::user()?->is_admin, 403);
    }

    protected function cleanCart(): array
    {
        $cart = session('cart', []);
        $validCart = [];

        foreach ($cart as $productId => $quantity) {
            if (Product::whereKey($productId)->exists() && (int) $quantity > 0) {
                $validCart[$productId] = (int) $quantity;
            }
        }

        if ($validCart !== $cart) {
            session()->put('cart', $validCart);
        }

        return $validCart;
    }

    protected function notifyAdmins(Order $order, string $event): void
    {
        User::where('is_admin', true)->get()->each(function (User $admin) use ($order, $event) {
            $admin->notify(new AdminOrderNotification($order, $event));
        });
    }
}
