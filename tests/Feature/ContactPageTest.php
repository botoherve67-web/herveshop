<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    public function test_the_contact_page_returns_a_successful_response(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Une question ?');
        $response->assertSee('Envoyez-nous un message');
    }

    public function test_the_contact_form_sends_a_real_email(): void
    {
        Mail::fake();
        config(['mail.contact_address' => 'support@example.com']);

        $response = $this->post('/contact', [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+228 90000000',
            'subject' => 'Commande',
            'message' => 'Je souhaite plus d’informations sur un produit.',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');
        Mail::assertSent(ContactFormMail::class, fn (ContactFormMail $mail): bool => $mail->hasTo('support@example.com'));
    }

    public function test_the_contact_email_template_renders_without_mail_message_conflict(): void
    {
        $mail = new ContactFormMail(
            'Jean Dupont',
            'jean@example.com',
            '+228 90000000',
            'Commande',
            'Je souhaite plus d’informations sur un produit.'
        );

        $html = $mail->render();

        $this->assertStringContainsString('Jean Dupont', $html);
        $this->assertStringContainsString('Je souhaite plus d’informations sur un produit.', $html);
    }
}
