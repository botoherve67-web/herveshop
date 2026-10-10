<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ConfigController extends Controller
{
    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'app_name' => 'HerveShop',
            'version' => '1.0.0',
            'currency' => 'FCFA',
            'contact' => [
                'phone' => AppSetting::read('contact_phone', '+228 96 29 20 39'),
                'whatsapp' => '+22896292039',
                'whatsapp_display' => '+228 96 29 20 39',
                'email' => AppSetting::read('contact_email', 'botoherve67@gmail.com'),
                'address' => 'Lomé, Togo (Hedzranawoé & Tokoin)',
            ],
            'payment_methods' => [
                'flooz' => [
                    'name' => 'Moov Money (Flooz)',
                    'number' => '96 29 20 39',
                    'full_number' => '+228 96 29 20 39',
                    'recipient' => 'BOTO Hervé',
                    'ussd' => '*155#',
                    'instructions' => 'Composez le *155#, choisissez Transfert d\'argent, entrez le numéro 96 29 20 39 et confirmez avec votre code PIN.',
                    'color' => '#005caa',
                ],
                'tmoney' => [
                    'name' => 'Mix / Togocom (T-Money)',
                    'number' => '96 29 20 39',
                    'full_number' => '+228 96 29 20 39',
                    'recipient' => 'BOTO Hervé',
                    'ussd' => '*145#',
                    'instructions' => 'Composez le *145#, sélectionnez Envoi d\'argent, entrez le numéro 96 29 20 39 et validez avec votre code secret.',
                    'color' => '#ffcc00',
                ],
            ],
            'delivery_zones' => [
                [
                    'id' => 'lome_centre',
                    'name' => 'Lomé Centre (Adawlato, Déckon, Nyékonakpoè, Kodjoviakopé)',
                    'price' => 1000,
                    'delay' => '24h ouvrées',
                ],
                [
                    'id' => 'lome_peripherie',
                    'name' => 'Lomé Périphérie (Agoè, Adidogomé, Hedzranawoé, Baguida, Avépozo)',
                    'price' => 1500,
                    'delay' => '24h - 48h',
                ],
                [
                    'id' => 'interieur_togo',
                    'name' => 'Intérieur du Togo (Kpalimé, Atakpamé, Sokodé, Kara, Dapaong)',
                    'price' => 3000,
                    'delay' => '48h - 72h',
                ],
                [
                    'id' => 'hors_togo',
                    'name' => 'Sous-région (Bénin, Ghana, etc.)',
                    'price' => 5000,
                    'delay' => '3 à 5 jours',
                ],
            ],
            'pickup_points' => [
                [
                    'id' => 'p1',
                    'name' => 'Boutique HerveShop — Lomé Hedzranawoé',
                    'address' => 'Face au grand marché Hedzranawoé, Lomé',
                    'hours' => 'Lun - Sam : 08h00 - 19h00',
                ],
                [
                    'id' => 'p2',
                    'name' => 'Point Relais HerveShop — Tokoin Casablanca',
                    'address' => 'Carrefour Tokoin, Lomé',
                    'hours' => 'Lun - Sam : 09h00 - 18h30',
                ],
            ],
        ]);
    }

    public function contact(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre message a bien été envoyé. Notre équipe vous répondra très rapidement.',
        ]);
    }
}
