<?php

namespace App\Http\Controllers\Api;

class ReturnController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::with('items')->findOrFail($request->sale_id);
            $totalRefundAmount = 0;
            $returnItemsData = [];

            foreach ($request->items as $itemInput) {
                $saleItem = SaleItem::findOrFail($itemInput['sale_item_id']);

                
                if ($saleItem->sale_id !== $sale->id) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'بعض الأصناف المطلوبة لا تنتمي لهذه الفاتورة.'
                    ], 422);
                }

               
                $alreadyReturnedQty = ReturnItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $maxAllowedReturn = $saleItem->quantity - $alreadyReturnedQty;

                
                if ($itemInput['quantity'] > $maxAllowedReturn) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "الكمية المرتجعة للصنف تجاوزت الكمية المباعة الفعليّة المتاحة للإرجاع. المتاح للإرجاع: {$maxAllowedReturn}"
                    ], 422);
                }

                $returnQty = $itemInput['quantity'];
                $itemSubtotal = $returnQty * $saleItem->unit_price;
                $totalRefundAmount += $itemSubtotal;

                $returnItemsData[] = [
                    'sale_item_id' => $saleItem->id,
                    'medicine_id' => $saleItem->medicine_id,
                    'quantity' => $returnQty,
                    'unit_price' => $saleItem->unit_price,
                    'subtotal' => $itemSubtotal,
                ];

               
                $batch = MedicineBatch::where('medicine_id', $saleItem->medicine_id)
                    ->orderBy('expiry_date', 'desc')
                    ->first();

                if ($batch) {
                    $batch->increment('quantity', $returnQty);
                } else {
                   
                    MedicineBatch::create([
                        'medicine_id' => $saleItem->medicine_id,
                        'batch_number' => 'RET-' . strtoupper(Str::random(6)),
                        'expiry_date' => now()->addYear(), 
                        'purchase_price' => $saleItem->unit_cost,
                        'quantity' => $returnQty,
                    ]);
                }
            }

           
            $returnNumber = 'RET-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $returnOrder = ReturnOrder::create([
                'sale_id' => $sale->id,
                'user_id' => $request->user()->id,
                'return_number' => $returnNumber,
                'total_refund_amount' => $totalRefundAmount,
                'reason' => $request->reason,
            ]);

            
            foreach ($returnItemsData as $rItem) {
                $returnOrder->items()->create($rItem);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء مرتجع الفاتورة وإعادة الكميات للمخزون بنجاح.',
                'data' => $returnOrder->load('items.medicine')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'فشل معالجة المرتجع: ' . $e->getMessage()
            ], 500);
        }
    }
}

?>