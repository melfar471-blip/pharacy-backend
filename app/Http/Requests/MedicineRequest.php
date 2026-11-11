<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
      
        $medicineId = $this->route('id');

        return [
            'barcode' => 'required|string|max:50|unique:medicines,barcode,' . $medicineId,
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'alert_threshold' => 'nullable|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'supplier_name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'barcode.required' => 'باركود الدواء مطلوب.',
            'barcode.unique' => 'هذا الباركود مستخدم مسبقاً لدواء آخر.',
            'name.required' => 'اسم الدواء مطلوب.',
            'selling_price.required' => 'سعر البيع مطلوب.',
            'expiry_date.after' => 'تاريخ الصلاحية يجب أن يكون في المستقبل.',
        ];
    }
}
?>