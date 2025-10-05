<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationWaitingController extends Controller
{
    public function show(Request $request): View
    {
        // If account_id is present, save it to session
        if ($request->has('account_id')) {
            session()->put('pending_integration.account_id', $request->query('account_id'));
        }

        return view('integration-waiting');
    }
}
