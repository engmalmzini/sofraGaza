<?php

namespace App\Http\Controllers\Partner;

use App\Models\MembershipSubscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CardController extends Controller
{
    public function show(Request $request): View
    {
        $this->restaurant();

        $query = trim((string) $request->input('q', ''));
        $subscription = null;
        $searched = $query !== '';

        if ($searched) {
            $subscription = MembershipSubscription::findActiveByCardNumber($query);
        }

        return view('partner.cards.show', [
            'query' => $query,
            'searched' => $searched,
            'subscription' => $subscription,
        ]);
    }
}
