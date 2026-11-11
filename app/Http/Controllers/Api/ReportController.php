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
   
    private function applyDateFilter($query, Request $request, $dateColumn = 'created_at')
    {
        if ($request->filled('from_date')) {
            $query->whereDate($dateColumn, '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate($dateColumn, '<=', $request->to_date);
        }
        return $query;
    }

  
    public function salesReport(Request $request)
    {
        $query = Sale::query();
        $this->applyDateFilter($query, $request);

        $totalSales = $query->sum('total_amount');
        $totalInvoices = $query->count();
        
      
        $paymentMethodsBreakdown = Sale::query()
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $perPage = $request->get('per_page', 15);
        $invoices = $query->with(['user', 'items.medicine'])->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_sales_amount' => $totalSales,
                'total_invoices_count' => $totalInvoices,
                'payment_methods_breakdown' => $paymentMethodsBreakdown,
                'invoices' => $invoices
            ]
        ], 200);
    }

   
    public function profitReport(Request $request)
    {
       
        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id');

        
        if ($request->filled('from_date')) {
            $query->whereDate('sales.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sales.created_at', '<=', $request->to_date);
        }

       
        $financials = $query->select(
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_historical_cost'),
            DB::raw('SUM((sale_items.unit_price - sale_items.unit_cost) * sale_items.quantity) as net_profit')
        )->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_revenue' => $financials->total_revenue ?? 0,          
                'total_historical_cost' => $financials->total_historical_cost ?? 0,
                'net_profit' => $financials->net_profit ?? 0,                
            ]
        ], 200);
    }

   
    public function topSellingMedicines(Request $request)
    {
        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id');

        if ($request->filled('from_date')) {
            $query->whereDate('sales.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sales.created_at', '<=', $request->to_date);
        }

        $limit = $request->get('limit', 10);

        $topMedicines = $query->select(
            'medicines.id',
            'medicines.name',
            'medicines.barcode',
            DB::raw('SUM(sale_items.quantity) as total_quantity_sold'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue_generated')
        )
        ->groupBy('medicines.id', 'medicines.name', 'medicines.barcode')
        ->orderBy('total_quantity_sold', 'desc')
        ->limit($limit)
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $topMedicines
        ], 200);
    }
}

?>
