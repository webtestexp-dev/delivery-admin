<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\Payment;
use App\Models\Product;
use App\Services\OutstandingBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index(OutstandingBalance $balance): View
    {
        $today = Carbon::today();
        $deliveriesToday = Delivery::whereDate('delivery_date', $today);
        $deliveredSalesToday = DeliveryLine::whereHas('delivery', function ($query) use ($today) {
            $query->whereDate('delivery_date', $today)->where('status', 'delivered');
        })->sum('line_total');

        return view('home', [
            'today' => $today,
            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::where('is_active', true)->count(),
            'totalProducts' => Product::count(),
            'totalBrands' => Brand::count(),
            'activeBrands' => Brand::where('is_active', true)->count(),
            'deliveriesToday' => (clone $deliveriesToday)->count(),
            'completedDeliveriesToday' => (clone $deliveriesToday)->where('status', 'delivered')->count(),
            'salesToday' => $deliveredSalesToday,
            'paymentsToday' => Payment::whereDate('paid_at', $today)->sum('amount'),
            'outstandingBalance' => $balance->total(),
            'recentDeliveries' => Delivery::with('customer')
                ->withCount('lines')
                ->orderByDesc('delivery_date')
                ->orderByDesc('id')
                ->limit(6)
                ->get(),
            'recentPayments' => Payment::with('customer')
                ->orderByDesc('paid_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
