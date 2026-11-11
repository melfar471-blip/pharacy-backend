<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalytics extends Component
{
    public function render()
    {
        $today = Carbon::today();

       
        $todaySalesAmount = Sale::whereDate('created_at', $today)->sum('total_amount');

       
        $todayNetProfit = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.created_at', $today)
            ->select(DB::raw('SUM((sale_items.unit_price - sale_items.unit_cost) * sale_items.quantity) as profit'))
            ->value('profit') ?? 0;

        
        $todayInvoicesCount = Sale::whereDate('created_at', $today)->count();

       
        $topMedicines = SaleItem::query()
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
            ->select(
                'medicines.name',
                'medicines.barcode',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('medicines.id', 'medicines.name', 'medicines.barcode')
            ->orderBy('total_qty', 'desc')
            ->take(5)
            ->get();

        
        $lowStockMedicines = Medicine::where('is_archived', false)
            ->get()
            ->filter(function($medicine) {
                return $medicine->total_stock <= ($medicine->alert_threshold ?? 10);
            });

       
        $nearExpiryBatches = MedicineBatch::with('medicine')
            ->where('expiry_date', '<=', Carbon::now()->addDays(90))
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->take(10)
            ->get();

        return view('livewire.admin-analytics', [
            'todaySalesAmount' => $todaySalesAmount,
            'todayNetProfit' => $todayNetProfit,
            'todayInvoicesCount' => $todayInvoicesCount,
            'topMedicines' => $topMedicines,
            'lowStockMedicines' => $lowStockMedicines,
            'nearExpiryBatches' => $nearExpiryBatches,
        ]);
    }
}
?>