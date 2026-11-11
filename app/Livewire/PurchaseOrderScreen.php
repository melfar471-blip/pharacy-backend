<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderScreen extends Component
{
    public $supplier_id;
    public $orderItems = [];
    public $selectedMedicine, $quantity = 1, $unit_cost = 0;

    public function addItem()
    {
        $this->validate([
            'selectedMedicine' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $medicine = Medicine::find($this->selectedMedicine);

        $this->orderItems[] = [
            'medicine_id' => $medicine->id,
            'name' => $medicine->name,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'subtotal' => $this->quantity * $this->unit_cost,
        ];

        $this->reset(['selectedMedicine', 'quantity', 'unit_cost']);
    }

    public function removeItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
    }

    public function createOrder()
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'orderItems' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalCost = collect($this->orderItems)->sum('subtotal');

            $po = PurchaseOrder::create([
                'supplier_id' => $this->supplier_id,
                'order_number' => 'PO-' . strtoupper(Str::random(8)),
                'total_cost' => $totalCost,
                'status' => 'pending',
            ]);

            foreach ($this->orderItems as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();
            $this->reset(['supplier_id', 'orderItems']);
            session()->flash('message', 'تم إنشاء طلب الشراء بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function receiveOrder($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        if ($po->status === 'received') return;

        DB::beginTransaction();
        try {
            $po->update(['status' => 'received']);
          foreach ($this->orderItems as $item) {
                Medicine::where('id', item->medicine_id)->increment('quantity', item->quantity);
            }
            DB::commit();
            session()->flash('message', 'تم استلام البضاعة وتحديث المخزون بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function render()
    {
        return view('livewire.purchase-order-screen', [
            'suppliers' => Supplier::all(),
            'medicines' => Medicine::all(),
            'purchaseOrders' => PurchaseOrder::with(['supplier', 'items.medicine'])->orderBy('created_at', 'desc')->paginate(10)
        ]);
    }
}

?>