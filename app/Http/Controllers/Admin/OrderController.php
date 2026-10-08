<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Notifications\OrderUpdateNotification;
use App\Services\AffiliateCommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function export(Request $request)
    {
        $query = $this->filteredQuery($request);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Référence', 'Suivi', 'Client', 'Email', 'WhatsApp', 'Total (FCFA)', 'Paiement', 'Statut', 'Date'], ';');
            $query->chunkById(500, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->reference,
                        $order->tracking_code,
                        $order->user?->name,
                        $order->user?->email,
                        $order->user?->whatsapp,
                        $order->total,
                        $order->statut_paiement,
                        $order->statut,
                        $order->created_at?->format('Y-m-d H:i:s'),
                    ], ';');
                }
            });
            fclose($handle);
        }, 'commandes-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'statusHistory']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatut(Request $request, Order $order, AffiliateCommissionService $commissionService)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,confirmee,en_preparation,expediee,livree,annulee',
            'remarque_admin' => 'required_if:statut,annulee|nullable|string|max:2000',
            'tracking_code' => 'nullable|string|max:100|unique:orders,tracking_code,'.$order->id,
        ]);

        $result = DB::transaction(function () use ($order, $request, $commissionService): array {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $lockedOrder->statut;
            $data = [
                'statut' => $request->statut,
                'tracking_code' => $request->filled('tracking_code')
                    ? $request->tracking_code
                    : ($request->statut === 'expediee' && ! $lockedOrder->tracking_code ? $this->generateTrackingCode() : $lockedOrder->tracking_code),
            ];
            if ($request->filled('remarque_admin')) {
                $data['remarque_admin'] = $request->remarque_admin;
            }
            $lockedOrder->update($data);
            $changed = $lockedOrder->wasChanged('statut') || array_key_exists('remarque_admin', $data);
            if ($changed) {
                $this->recordHistory($lockedOrder, 'commande', $oldStatus, $lockedOrder->statut, $request->remarque_admin);
            }
            $commissionService->syncForOrder($lockedOrder);

            return [
                'changed' => $changed,
                'event' => $lockedOrder->statut === 'annulee' ? 'annulation' : ($lockedOrder->statut === 'expediee' ? 'expedition' : 'statut'),
            ];
        });

        if ($result['changed']) {
            $order->refresh()->load('user');
            try {
                $order->user->notify(new OrderUpdateNotification($order, $result['event'], $request->remarque_admin));
            } catch (\Throwable $exception) {
                Log::error('Notification statut non envoyee.', ['order_id' => $order->id, 'error' => $exception->getMessage()]);
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function confirmerPaiement(Request $request, Order $order, AffiliateCommissionService $commissionService)
    {
        $request->validate([
            'statut_paiement' => 'required|in:acompte_paye,paye,echec',
            'remarque_admin' => 'required_if:statut_paiement,echec|nullable|string|max:2000',
        ]);
        if (in_array($request->statut_paiement, ['acompte_paye', 'paye'], true)
            && (! $order->transaction_id || ! $order->preuve_paiement_path)) {
            throw ValidationException::withMessages([
                'statut_paiement' => 'Une transaction et une preuve de paiement sont obligatoires pour valider ce paiement.',
            ]);
        }
        $result = DB::transaction(function () use ($order, $request, $commissionService): bool {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $oldPaymentStatus = $lockedOrder->statut_paiement;
            $data = ['statut_paiement' => $request->statut_paiement];
            if ($request->filled('remarque_admin')) {
                $data['remarque_admin'] = $request->remarque_admin;
            }
            $lockedOrder->update($data);
            $changed = $lockedOrder->wasChanged('statut_paiement') || array_key_exists('remarque_admin', $data);
            if ($changed) {
                $this->recordHistory($lockedOrder, 'paiement', $oldPaymentStatus, $lockedOrder->statut_paiement, $request->remarque_admin);
            }
            $commissionService->syncForOrder($lockedOrder);

            return $changed;
        });

        if ($result) {
            $order->refresh()->load('user');
            try {
                $order->user->notify(new OrderUpdateNotification($order, 'paiement', $request->remarque_admin));
            } catch (\Throwable $exception) {
                Log::error('Notification paiement non envoyee.', ['order_id' => $order->id, 'error' => $exception->getMessage()]);
            }
        }

        return back()->with('success', 'Paiement mis à jour.');
    }

    public function paymentProof(Order $order)
    {
        abort_unless($order->preuve_paiement_path, 404);

        return response()->file(Storage::disk('private')->path($order->preuve_paiement_path));
    }

    protected function filteredQuery(Request $request)
    {
        $query = Order::with('user')->latest();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($builder) use ($search) {
                $builder->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('whatsapp', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('statut_paiement')) {
            $query->where('statut_paiement', $request->statut_paiement);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        return $query;
    }

    protected function recordHistory(Order $order, string $type, ?string $oldValue, string $newValue, ?string $remark): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'ancienne_valeur' => $oldValue,
            'nouvelle_valeur' => $newValue,
            'remarque' => $remark,
        ]);
    }

    protected function generateTrackingCode(): string
    {
        do {
            $code = 'HS-TRK-'.strtoupper(Str::random(8));
        } while (Order::where('tracking_code', $code)->exists());

        return $code;
    }
}
