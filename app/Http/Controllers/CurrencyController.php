<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Switch active currency (USD or BDT).
     */
    public function switch(Request $request): RedirectResponse|JsonResponse
    {
        $currency = strtoupper($request->input('currency', $request->route('code', CurrencyService::USD)));
        
        $activeCurrency = CurrencyService::setCurrency($currency);
        $cookie = cookie('sbl_currency', $activeCurrency, 60 * 24 * 365); // 1 year

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'currency' => $activeCurrency,
                'symbol' => CurrencyService::getSymbol($activeCurrency),
                'rate' => CurrencyService::getRate($activeCurrency),
            ])->withCookie($cookie);
        }

        return redirect()->back()->withCookie($cookie);
    }
}
