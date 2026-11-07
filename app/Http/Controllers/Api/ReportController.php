<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //
}

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function financialReport(Request $request)
    {
       
        $totalSales = Sale::sum('total_amount');
        
      
        $totalInvoices = Sale::count();

       
        $saleItems = SaleItem::with('medicine')->get();
        $totalProfit = 0;

        foreach ($saleItems as $item) {
            if ($item->medicine) {
                $purchasePrice = $item->medicine->purchase_price;
                $profitPerUnit = $item->unit_price - $purchasePrice;
                $totalProfit += ($profitPerUnit * $item->quantity);
            }
        }

        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_sales_amount' => $totalSales,
            'total_estimated_profit' => $totalProfit,
        ], 200);
    }

   
    public function topSellingMedicines()
    {
        $topMedicines = SaleItem::select('medicine_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('medicine_id')
            ->orderByDesc('total_sold')
            ->with('medicine')
            ->take(5)
            ->get();

        return response()->json([
            'top_medicines' => $topMedicines
        ], 200);
    }
}
?>
