<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\MedicineBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosScreen extends Component
{
    public $search = '';
    public $cart = [];
    public $paymentMethod = 'cash';
    public $errorMessage = '';
    public $successMessage = '';

  
    public function addToCart($medicineId)
    {
        $medicine = Medicine::findOrFail($medicineId);

        
        if ($medicine->total_stock <= 0) {
            $this->errorMessage = "عذراً، هذا الدواء غير متوفر في المخزون حالياً.";
            return;
        }

        $this->errorMessage = '';
        $this->successMessage = '';

       
        if (isset($this->cart[$medicineId])) {
            $this->cart[$medicineId]['quantity']++;
            $this->cart[$medicineId]['subtotal'] = $this->cart[$medicineId]['quantity'] * $this->cart[$medicineId]['selling_price'];
        } else {
           
            $this->cart[$medicineId] = [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'barcode' => $medicine->barcode,
                'selling_price' => $medicine->selling_price,
                'quantity' => 1,
                'subtotal' => $medicine->selling_price,
            ];
        }
    }

   
    public function updateQuantity($medicineId, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$medicineId]);
        } else {
            $this->cart[$medicineId]['quantity'] = $quantity;
            $this->cart[$medicineId]['subtotal'] = $quantity * $this->cart[$medicineId]['selling_price'];
        }
    }

   
    public function removeFromCart($medicineId)
    {
        unset($this->cart[$medicineId]);
    }

   
    public function getTotalProperty()
    {
        return collect($this->cart)->sum('subtotal');
    }

    
    public function checkout()
    {
        if (empty($this->cart)) {
            $this->errorMessage = "السلة فارغة، يرجى إضافة أدوية أولاً.";
            return;
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $saleItemsData = [];

            foreach ($this->cart as $item) {
                $medicineId = $item['id'];
                $requestedQty = $item['quantity'];
                $unitSellingPrice = $item['selling_price'];

                $medicine = Medicine::findOrFail($medicineId);

                
                $batches = MedicineBatch::where('medicine_id', $medicineId)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                $totalAvailableStock = $batches->sum('quantity');

                if ($totalAvailableStock < $requestedQty) {
                    DB::rollBack();
                    $this->errorMessage = "الكمية المطلوبة غير متوفرة للدواء: {$medicine->name}. المتوفر: {$totalAvailableStock}";
                    return;
                }

                $remainingQtyToDeduct = $requestedQty;
                $itemSubtotal = $requestedQty * $unitSellingPrice;
                $totalAmount += $itemSubtotal;

               
                foreach ($batches as $batch) {
                    if ($remainingQtyToDeduct <= 0) {
                        break;
                    }

                    $qtyFromThisBatch = min($batch->quantity, $remainingQtyToDeduct);
                    $batch->decrement('quantity', $qtyFromThisBatch);
                    $remainingQtyToDeduct -= $qtyFromThisBatch;

                    $saleItemsData[] = [
                        'medicine_id' => $medicineId,
                        'quantity' => $qtyFromThisBatch,
                        'unit_price' => $unitSellingPrice,
                        'unit_cost' => $batch->purchase_price,
                        'subtotal' => $qtyFromThisBatch * $unitSellingPrice,
                    ];
                }
            }

            
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'payment_method' => $this->paymentMethod,
                'user_id' => auth()->id(),
            ]);

            foreach ($saleItemsData as $saleItem) {
                $sale->items()->create($saleItem);
            }

            DB::commit();

            
            $this->cart = [];
            $this->errorMessage = '';
            $this->successMessage = "تم إتمام الفاتورة بنجاح برقم: {$invoiceNumber}";

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = "حدث خطأ أثناء إتمام البيع: " . $e->getMessage();
        }
    }

    public function render()
    {
        
        $medicines = [];
        if (!empty($this->search)) {
            $medicines = Medicine::where('is_archived', false)
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
                })
                ->take(10)
                ->get();
        }

        return view('livewire.pos-screen', [
            'medicines' => $medicines
        ]);
    }
}

?>