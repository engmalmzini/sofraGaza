<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $key = $request->string('area')->toString();
        $area = collect(config('brand.areas'))->firstWhere('key', $key);

        if (! $area) {
            return back();
        }

        session(['delivery_area' => $area]);

        return back();
    }
}
