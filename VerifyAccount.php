<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Account;

class VerifyAccount
{
    public function handle($request, Closure $next)
    {
        $accountId = $request->route('accountId') ?? $request->input('account_id');

        // Если accountId не передан
        if (!$accountId) {
            return response()->json([
                'success' => false,
                'error' => 'Account ID is required'
            ], 400);
        }

        // Проверяем что аккаунт существует
        $account = Account::find($accountId);

        if (!$account) {
            return response()->json([
                'success' => false,
                'error' => 'Account not found'
            ], 404);
        }

        // Проверяем что аккаунт активен
        if (!$account->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Account is not active'
            ], 403);
        }

        // Добавляем аккаунт в request для дальнейшего использования
        $request->attributes->set('account', $account);

        return $next($request);
    }
}
