<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;

class FreshDataService
{
    public function getFreshOrders($accountId, $days = 7)
    {
        return Order::where('account_id', $accountId)
            ->where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getTodayOrders($accountId)
    {
        return Order::where('account_id', $accountId)
            ->whereDate('date', Carbon::today())
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getFreshSales($accountId, $days = 7)
    {
        return Sale::where('account_id', $accountId)
            ->where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getFreshIncomes($accountId, $days = 7)
    {
        return Income::where('account_id', $accountId)
            ->where('date', '>=', Carbon::now()->subDays($days))
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getDashboardData($accountId, $days = 7)
    {
        return [
            'orders' => [
                'today' => Order::where('account_id', $accountId)
                    ->whereDate('date', Carbon::today())
                    ->count(),
                'last_7_days' => Order::where('account_id', $accountId)
                    ->where('date', '>=', Carbon::now()->subDays($days))
                    ->count(),
                'total' => Order::where('account_id', $accountId)->count()
            ],
            'sales' => [
                'today' => Sale::where('account_id', $accountId)
                    ->whereDate('date', Carbon::today())
                    ->count(),
                'last_7_days' => Sale::where('account_id', $accountId)
                    ->where('date', '>=', Carbon::now()->subDays($days))
                    ->count(),
                'total' => Sale::where('account_id', $accountId)->count()
            ],
            'revenue' => [
                'today' => Sale::where('account_id', $accountId)
                    ->whereDate('date', Carbon::today())
                    ->sum('total_price'),
                'last_7_days' => Sale::where('account_id', $accountId)
                    ->where('date', '>=', Carbon::now()->subDays($days))
                    ->sum('total_price')
            ]
        ];
    }

    public function getDateRangeStats($accountId, $startDate, $endDate)
    {
        return [
            'orders' => Order::where('account_id', $accountId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get(),
            'sales' => Sale::where('account_id', $accountId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get(),
            'incomes' => Income::where('account_id', $accountId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
        ];
    }
}
