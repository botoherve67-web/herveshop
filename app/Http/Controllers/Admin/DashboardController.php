<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $paidStatuses = ['acompte_paye', 'paye'];

        $stats = [
            'commandes_en_attente' => Order::where('statut', 'en_attente')->count(),
            'produits_stock_bas' => Product::where('stock', '<=', 3)->where('type', 'stock')->count(),
            'ca_total' => Order::whereIn('statut_paiement', $paidStatuses)
                ->where('statut', '!=', 'annulee')
                ->sum(DB::raw("CASE WHEN statut_paiement = 'acompte_paye' THEN montant_acompte ELSE total END")),
            'commandes_du_jour' => Order::whereDate('created_at', today())->count(),
            'ca_du_jour' => Order::whereDate('created_at', today())
                ->whereIn('statut_paiement', $paidStatuses)
                ->where('statut', '!=', 'annulee')
                ->sum(DB::raw("CASE WHEN statut_paiement = 'acompte_paye' THEN montant_acompte ELSE total END")),
            'paiements_en_attente' => Order::where('statut_paiement', 'en_attente')->count(),
            'montant_paiements_en_attente' => Order::where('statut_paiement', 'en_attente')->sum('total'),
            'precommandes_actives' => Order::where('type', 'precommande')->whereNotIn('statut', ['livree', 'annulee'])->count(),
            'clients_inscrits' => User::where('is_admin', false)->count(),
            'preuves_en_attente' => Order::whereNotNull('preuve_paiement_path')->where('statut_paiement', 'en_attente')->count(),
            'avis_a_moderer' => Review::where('is_approved', false)->count(),
        ];

        $meilleuresVentes = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.statut_paiement', $paidStatuses)
            ->where('orders.statut', '!=', 'annulee')
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as quantite_vendue'), DB::raw('SUM(order_items.quantity * order_items.unit_price) as chiffre_affaires'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('quantite_vendue')
            ->limit(5)
            ->get();

        $dernieresCommandes = Order::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'meilleuresVentes', 'dernieresCommandes'));
    }
}
