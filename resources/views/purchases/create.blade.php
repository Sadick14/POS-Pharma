@extends('layouts.app')

@section('title', 'Receive Stock')
@section('page-title', 'Receive Stock & Inward Batches')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="purchaseForm()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Receive Stock Inward</h1>
            <p class="text-xs text-slate-500">Record supplier invoice and add multiple batches with expiry dates to inventory.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            &larr; Back to Purchases
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6 text-xs">
        @csrf

        <!-- Invoice Details Header -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-6 border-b border-slate-100">
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Supplier *</label>
                <select name="supplier_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Choose Supplier --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Invoice / PO Number *</label>
                <input type="text" name="invoice_number" value="{{ old('invoice_number', 'PUR-' . date('Ymd') . '-' . rand(100, 999)) }}" required
                       placeholder="e.g. PUR-2026-001"
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Purchase Date *</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <!-- Dynamic Line Items Section -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Received Medicines & Batches</h3>
                    <p class="text-[11px] text-slate-500">Enter batch numbers, expiry dates, purchase costs, and quantities</p>
                </div>
                <button type="button" @click="addItem()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                            <th class="py-2.5 px-3 min-w-[200px]">Medicine *</th>
                            <th class="py-2.5 px-3 min-w-[120px]">Batch # *</th>
                            <th class="py-2.5 px-3 min-w-[130px]">Expiry Date *</th>
                            <th class="py-2.5 px-3 min-w-[90px]">Qty *</th>
                            <th class="py-2.5 px-3 min-w-[100px]">Cost ({{ \App\Models\Setting::get('currency_symbol', 'GHS') }}) *</th>
                            <th class="py-2.5 px-3 min-w-[100px]">Selling ({{ \App\Models\Setting::get('currency_symbol', 'GHS') }}) *</th>
                            <th class="py-2.5 px-3 min-w-[100px]">Subtotal</th>
                            <th class="py-2.5 px-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="py-2.5 px-3">
                                    <select :name="'items['+index+'][medicine_id]'" x-model="item.medicine_id" @change="onMedicineSelect(index)" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                        <option value="">Select Medicine</option>
                                        @foreach($medicines as $m)
                                            <option value="{{ $m->id }}" data-price="{{ $m->selling_price }}">{{ $m->name }} ({{ $m->unit }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="text" :name="'items['+index+'][batch_number]'" x-model="item.batch_number" required placeholder="BAT-001" class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg font-mono">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="date" :name="'items['+index+'][expiry_date]'" x-model="item.expiry_date" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" min="1" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg font-bold text-center">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_cost]'" x-model.number="item.unit_cost" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-right">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.01" min="0" :name="'items['+index+'][selling_price]'" x-model.number="item.selling_price" required class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-right font-bold">
                                </td>
                                <td class="py-2.5 px-3 font-bold font-mono text-slate-900 text-right">
                                    <span x-text="((item.quantity || 0) * (item.unit_cost || 0)).toFixed(2)"></span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-500 hover:text-rose-700 p-1">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary & Footer -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-slate-100 items-end">
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Status *</label>
                <select name="payment_status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500">
                    <option value="paid">Paid (Fully Settled)</option>
                    <option value="partial">Partial Payment</option>
                    <option value="pending">Pending / On Credit</option>
                </select>
                <textarea name="notes" rows="2" placeholder="Purchase notes..." class="w-full mt-2 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl"></textarea>
            </div>

            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-2 text-right">
                <span class="text-slate-500 block text-xs">Total Purchase Inward Cost:</span>
                <span class="text-2xl font-black text-slate-900 font-mono">
                    {{ \App\Models\Setting::get('currency_symbol', 'GHS') }} <span x-text="calculateGrandTotal().toFixed(2)"></span>
                </span>
                <div class="pt-3">
                    <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md shadow-emerald-900/20 text-sm">
                        Confirm Purchase & Receive Batches
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function purchaseForm() {
    return {
        items: [
            { medicine_id: '', batch_number: '', expiry_date: '', quantity: 10, unit_cost: 0.00, selling_price: 0.00 }
        ],

        addItem() {
            this.items.push({ medicine_id: '', batch_number: '', expiry_date: '', quantity: 10, unit_cost: 0.00, selling_price: 0.00 });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onMedicineSelect(index) {
            const selectEl = document.querySelectorAll('select[name^="items"][name$="[medicine_id]"]')[index];
            const opt = selectEl.options[selectEl.selectedIndex];
            if (opt && opt.dataset.price) {
                this.items[index].selling_price = parseFloat(opt.dataset.price) || 0.00;
            }
        },

        calculateGrandTotal() {
            return this.items.reduce((sum, it) => sum + ((it.quantity || 0) * (it.unit_cost || 0)), 0);
        }
    }
}
</script>
@endpush
