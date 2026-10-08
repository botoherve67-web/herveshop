<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourierController extends Controller
{
    public function dashboard()
    {
        $courier = Auth::user();
        abort_unless($courier->account_type === 'courier', 403);

        $availableOrders = Order::with(['user', 'items'])
            ->where('mode_livraison', 'domicile')
            ->where('statut_paiement', 'paye')
            ->whereIn('statut', ['confirmee', 'en_preparation', 'expediee'])
            ->whereNull('courier_id')
            ->latest()
            ->get();
        $myOrders = Order::with(['user', 'items'])
            ->where('courier_id', $courier->id)
            ->whereNotIn('statut', ['livree', 'annulee'])
            ->latest()
            ->get();
        $completedOrders = Order::with(['user', 'items'])
            ->where('courier_id', $courier->id)
            ->where('statut', 'livree')
            ->latest()
            ->take(10)
            ->get();

        return view('courier.dashboard', compact('availableOrders', 'myOrders', 'completedOrders'));
    }

    public function claim(Order $order)
    {
        $courier = Auth::user();
        abort_unless($courier->account_type === 'courier', 403);

        DB::transaction(function () use ($order, $courier): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->mode_livraison !== 'domicile'
                || $lockedOrder->statut_paiement !== 'paye'
                || ! in_array($lockedOrder->statut, ['confirmee', 'en_preparation', 'expediee'], true)
                || $lockedOrder->courier_id !== null) {
                throw ValidationException::withMessages([
                    'delivery' => 'Cette livraison n’est plus disponible.',
                ]);
            }

            $lockedOrder->update(['courier_id' => $courier->id]);
        });

        return back()->with('success', 'La livraison vous a été attribuée.');
    }

    public function complete(Order $order)
    {
        $courier = Auth::user();
        abort_unless($courier->account_type === 'courier', 403);

        DB::transaction(function () use ($order, $courier): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->courier_id !== $courier->id
                || $lockedOrder->statut === 'annulee'
                || $lockedOrder->statut === 'livree') {
                throw ValidationException::withMessages([
                    'delivery' => 'Cette livraison ne peut pas être clôturée par ce compte.',
                ]);
            }

            $oldStatus = $lockedOrder->statut;
            $lockedOrder->update(['statut' => 'livree']);
            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'user_id' => $courier->id,
                'type' => 'commande',
                'ancienne_valeur' => $oldStatus,
                'nouvelle_valeur' => 'livree',
                'remarque' => 'Livraison confirmée par le livreur.',
            ]);
        });

        return back()->with('success', 'La livraison a été marquée comme effectuée.');
    }
}
