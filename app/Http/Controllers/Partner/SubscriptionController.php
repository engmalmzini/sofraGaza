<?php

namespace App\Http\Controllers\Partner;

use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('partner.dashboard');
    }

    public function store(): RedirectResponse
    {
        return redirect()->route('partner.dashboard');
    }
}
