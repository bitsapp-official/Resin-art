<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Switch user currency in session
     */
    public function switchCurrency(Request $request, string $currency): RedirectResponse|JsonResponse
    {
        $newCurrency = CurrencyService::setUserCurrency($currency);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'currency' => CurrencyService::getClientPayload(),
            ]);
        }

        return back();
    }
}
