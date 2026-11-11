<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
  
    public function index()
    {
        $sales = Sale::with('items.medicine')->latest()->paginate(10);
        return response()->json($sales, 200);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string', 
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $totalAmount = 0;
                $saleItemsData = [];

               
                foreach ($request->items as $item) {
                    $medicine = Medicine::findOrFail($item['medicine_id']);

                  if ($medicine->quantity < $item['quantity']) {
                     return response()->json([
                         'message' => "الكمية المطلوبة غير متوفرة للدواء: {$medicine->name}. المتاح حالياً: {$medicine->quantity}"
                             ], 400);
                        }

                    $subtotal = $medicine->selling_price * $item['quantity'];
                    $totalAmount += $subtotal;

                   
                    $saleItemsData[] = [
                        'medicine' => $medicine,
                        'quantity' => $item['quantity'],
                        'unit_price' => $medicine->selling_price,
                        'subtotal' => $subtotal,
                    ];
                }

                
                $sale = Sale::create([
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->payment_method,
                ]);

                
                foreach ($saleItemsData as $data) {
                    $medicine = $data['medicine'];
                    
                    
                    $medicine->decrement('quantity', $data['quantity']);

                  
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'quantity' => $data['quantity'],
                        'unit_price' => $data['unit_price'],
                        'subtotal' => $data['subtotal'],
                    ]);
                }

                return response()->json([
                    'message' => 'تم إتمام عملية البيع بنجاح وتحديث المخزون',
                    'invoice' => $sale->load('items.medicine')
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إتمام العملية',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function deductStockUsingFEFO($medicineId, $requestedQuantity)
{
   
    $batches = MedicineBatch::where('medicine_id', $medicineId)
        ->where('quantity', '>', 0)
        ->orderBy('expiry_date', 'asc')
        ->get();

    $totalAvailable = $batches->sum('quantity');

   
    if ($totalAvailable < $requestedQuantity) {
        throw new \Exception("الكمية المطلوبة غير متوفرة في المخزون الحالي.");
    }

    $remainingQtyToDeduct = $requestedQuantity;

    foreach ($batches as $batch) {
        if ($remainingQtyToDeduct <= 0) {
            break;
        }

        if ($batch->quantity >= $remainingQtyToDeduct) {
            
            $batch->decrement('quantity', $remainingQtyToDeduct);
            $remainingQtyToDeduct = 0;
        } else {
            
            $remainingQtyToDeduct -= $batch->quantity;
            $batch->update(['quantity' => 0]);
        }
    }
}
}
?>