<?php

namespace OrderManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
            'store_id' => ['nullable', 'integer', 'exists:'.config('order-management.tables.stores', 'stores').',store_id'],
            'product_id' => ['required', 'integer', 'exists:'.config('order-management.tables.products', 'products').',product_id'],
            'workflow_id' => ['nullable', 'integer', 'exists:'.config('order-management.tables.workflows', 'workflows').',workflow_id'],
            'template_id' => ['required', 'integer', 'exists:'.config('order-management.tables.workflow_templates', 'workflow_templates').',template_id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'dt_required' => ['nullable', 'date'],
            'dt_deadline' => ['nullable', 'date', 'after:dt_required'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'A product must be selected for the order.',
            'template_id.required' => 'A workflow template must be selected.',
            'title.required' => 'The order title is required.',
            'dt_deadline.after' => 'The deadline must be after the required date.',
        ];
    }
}
