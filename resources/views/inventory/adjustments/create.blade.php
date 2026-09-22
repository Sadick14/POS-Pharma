@extends('layouts.app')

@section('title', 'Record Adjustment')
@section('page-title', 'Record Stock Adjustment')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="stockAdjustmentForm()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Record Stock Adjustment</h1>
            <p class="text-xs text-slate-500">Correct physical counts, write off damaged medicines, or record expired stock removal.</p>
        </div>
        <a href="{{ route('inventory.adjustments') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            &larr; Back
        </a>
    </div>

    <form action="{{ route('inventory.adjustments.store') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Select Medicine *</label>
            <select x-model="selectedMedicineId" @change="updateBatches()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                <option value="">-- Choose Medicine --</option>
                @foreach($medicines as $m)
                    <option value="{{ $m->id }}" data-batches="{{ json_encode($m->batches) }}">{{ $m->name }} ({{ $m->current_stock }} {{ $m->unit }} in stock)</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Select Batch *</label>
            <select name="batch_id" x-model="selectedBatchId" @change="updateBatchQty()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                <option value="">-- Choose Batch --</option>
                <template x-for="b in availableBatches" :key="b.id">
                    <option :value="b.id" x-text="'Batch #' + b.batch_number + ' (Current Qty: ' + b.quantity + ' | Exp: ' + b.expiry_date + ')'"></option>
                </template>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <div>
                <span class="text-slate-500 block">Current Batch Quantity:</span>
                <span class="text-lg font-black text-slate-900" x-text="currentQty"></span>
            </div>
            <div>
                <span class="text-slate-500 block">Calculated New Quantity:</span>
                <span class="text-lg font-black text-emerald-700" x-text="currentQty + (parseInt(adjQty) || 0)"></span>
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Adjustment Quantity (+ for addition, - for deduction) *</label>
            <input type="number" name="adjustment_quantity" x-model.number="adjQty" required
                   placeholder="e.g. -5 for damaged or +10 for found count"
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Reason for Adjustment *</label>
            <select name="reason" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="Damaged stock">Damaged stock / Broken container</option>
                <option value="Expired medicine">Expired medicine write-off</option>
                <option value="Stock count correction">Stock count / Audit discrepancy correction</option>
                <option value="Missing stock">Missing / Unaccounted stock</option>
                <option value="Returned stock">Customer return approved</option>
                <option value="Internal transfer">Internal clinic / branch transfer</option>
                <option value="Other">Other reason</option>
            </select>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Notes / Supporting Details</label>
            <textarea name="notes" rows="3" placeholder="Incident report number, staff remarks..."
                      class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('inventory.adjustments') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20">
                Confirm Adjustment
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function stockAdjustmentForm() {
    return {
        selectedMedicineId: '',
        selectedBatchId: '',
        availableBatches: [],
        currentQty: 0,
        adjQty: '',

        updateBatches() {
            const selectEl = document.querySelector('select[x-model="selectedMedicineId"]');
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.batches) {
                this.availableBatches = JSON.parse(selectedOpt.dataset.batches);
                this.selectedBatchId = '';
                this.currentQty = 0;
            } else {
                this.availableBatches = [];
                this.selectedBatchId = '';
                this.currentQty = 0;
            }
        },

        updateBatchQty() {
            const batch = this.availableBatches.find(b => b.id == this.selectedBatchId);
            this.currentQty = batch ? batch.quantity : 0;
        }
    }
}
</script>
@endpush
