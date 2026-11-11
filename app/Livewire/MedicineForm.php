<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Medicine;

class MedicineForm extends Component
{
    public $medicineId;
    public $barcode, $name, $category, $quantity = 0, $alert_threshold = 10, $purchase_price, $selling_price, $expiry_date, $supplier_name;

    public function mount($id = null)
    {
        if ($id) {
            $medicine = Medicine::findOrFail($id);
            $this->medicineId = $medicine->id;
            $this->barcode = $medicine->barcode;
            $this->name = $medicine->name;
            $this->category = $medicine->category;
            $this->quantity = $medicine->quantity;
            $this->alert_threshold = $medicine->alert_threshold;
            $this->purchase_price = $medicine->purchase_price;
            $this->selling_price = $medicine->selling_price;
            $this->expiry_date = $medicine->expiry_date;
            $this->supplier_name = $medicine->supplier_name;
        }
    }

    public function save()
    {
        $this->validate([
            'barcode' => 'required|string|max:50|unique:medicines,barcode,' . $this->medicineId,
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'alert_threshold' => 'nullable|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
        ]);

        if ($this->medicineId) {
            $medicine = Medicine::findOrFail($this->medicineId);
            $medicine->update($this->all());
            session()->flash('message', 'تم تحديث بيانات الدواء بنجاح.');
        } else {
            Medicine::create($this->all());
            session()->flash('message', 'تم إضافة الدواء بنجاح.');
        }

        return redirect()->route('medicines.index');
    }

    public function render()
    {
        return view('livewire.medicine-form');
    }
}
?>