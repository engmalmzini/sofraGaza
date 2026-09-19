<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Restaurant;

abstract class Controller extends BaseController
{
    protected function restaurant(): Restaurant
    {
        $restaurant = auth()->user()?->ownedRestaurant;

        abort_unless($restaurant, 403, 'لا يوجد مطعم مرتبط بحسابك.');

        return $restaurant;
    }
}
