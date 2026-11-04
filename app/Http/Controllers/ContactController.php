<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'topic' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:15', 'max:5000'],
        ]);

        ContactInquiry::query()->create($validated);

        return redirect()->route('contact')->with('status', 'Your message has been recorded for administrator review. Please do not send passwords or payment details.');
    }
}