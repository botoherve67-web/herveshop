<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    public function invoice(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load(['user', 'items']);

        return Pdf::loadView('documents.invoice', compact('order'))
            ->setPaper('a4')
            ->download('facture-'.$order->reference.'.pdf');
    }

    public function receipt(Order $order)
    {
        $this->authorizeOrder($order);
        abort_unless(in_array($order->statut_paiement, ['acompte_paye', 'paye'], true), 404);

        $order->load(['user', 'items']);

        return Pdf::loadView('documents.receipt', compact('order'))
            ->setPaper('a4')
            ->download('recu-'.$order->reference.'.pdf');
    }

    protected function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()?->is_admin, 403);
    }
}