<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        
     $TodaySales = Sale::whereDate('created_at', Carbon::today())->sum('total_amount');
                

      
       $TodayInvoicesCount = Sale::whereDate('created_at', Carbon::today())->count();
                          

       
        $lowStockMedicines = Medicine::where('is_archived', false)
            ->whereColumn('quantity', '<=', 'alert_threshold')
            ->get();

        
        $nearExpiryBatches = MedicineBatch::with('medicine')
            ->where('expiry_date', '<=', Carbon::now()->addDays(90))
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'today_sales' => $todaySales,
                'today_invoices_count' => $todayInvoicesCount,
                'low_stock_medicines' => $lowStockMedicines,
                'near_expiry_batches' => $nearExpiryBatches,
            ]
        ], 200);
    }
}

?>