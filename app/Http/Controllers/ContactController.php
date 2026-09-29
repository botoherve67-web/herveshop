<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        $contact = [
            'phone' => AppSetting::read('contact_phone', '+228 96 29 20 39'),
            'email' => AppSetting::read('contact_email', config('mail.from.address', 'botoherve67@gmail.com')),
            'address' => AppSetting::read('contact_address', 'Lomé, Togo'),
            'hours' => AppSetting::read('contact_hours', 'Lun. – Sam. : 08:00 – 18:00'),
        ];

        return view('contact.index', compact('contact'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $recipient = AppSetting::read('contact_email', config('mail.from.address', 'botoherve67@gmail.com'));

        $mail = new ContactFormMail(
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['subject'],
            $data['message']
        );

        Mail::to($recipient)->send($mail);

        return redirect()->route('contact.index')->with('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
    }
}
