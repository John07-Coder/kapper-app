<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Verwerkt het contactformulier op /contact.
 */
class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required' => 'Vul je naam in.',
            'name.min' => 'Je naam moet minimaal 2 tekens bevatten.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'message.required' => 'Vul een bericht in.',
            'message.min' => 'Je bericht moet minimaal 10 tekens bevatten.',
            'message.max' => 'Je bericht mag maximaal 2000 tekens bevatten.',
        ]);

        Mail::to(config('salon.email'))->send(
            new ContactMessage($data['name'], $data['email'], $data['message'])
        );

        return redirect()
            ->route('contact')
            ->with('status', 'Bedankt voor je bericht! We nemen zo snel mogelijk contact met je op.');
    }
}
