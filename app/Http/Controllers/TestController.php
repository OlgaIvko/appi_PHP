<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testAccount($accountId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Account access verified',
            'account_id' => $accountId
        ]);
    }
}
