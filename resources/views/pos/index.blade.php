@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale (POS) Terminal')

@section('content')
<div x-data="posTerminal()" x-init="initPos()" class="space-y-4">
    <!-- Top Search & Quick Barcode Bar -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- Left 7 Cols: Search, Filters & Product Grid -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Search & Barcode Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <!-- Barcode Scan Input -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-barcode text-base"></i>
                        </div>
                        <input type="text" x-model="barcodeQuery" @keydown.enter.prevent="scanBarcode()"
                               placeholder="Scan barcode or type barcode + Enter..."
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    </div>

                    <!-- Live Name / Generic Search -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input type="text" x-model="searchQuery" @input.debounce.250ms="searchMedicines()"
                               placeholder="Search medicine name, generic, brand..."
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">

                        <!-- Live Search Results Dropdown -->
                        <div x-cloak x-show="searchResults.length > 0" @click.outside="searchResults = []"
                             class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-72 overflow-y-auto z-50 divide-y divide-slate-100">
                            <template x-for="item in searchResults" :key="item.id">
                                <div @click="addToCart(item); searchResults = []; searchQuery = ''"
                                     class="p-3 hover:bg-emerald-50 cursor-pointer flex items-center justify-between transition text-xs">
                                    <div>
                                        <p class="font-bold text-slate-800" x-text="item.name"></p>
                                        <p class="text-[11px] text-slate-500">
                                            <span x-text="item.dosage_form"></span> • <span x-text="item.strength"></span> •
                                            <span class="text-slate-600">Stock: <strong x-text="item.current_stock"></strong></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-emerald-700">{{ $currency }} <span x-text="item.selling_price.toFixed(2)"></span></span>
                                        <span class="block text-[10px] text-slate-400">Exp: <span x-text="item.earliest_expiry"></span></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                    <button type="button" @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg font-bold whitespace-nowrap transition">
                        All Items
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" @click="selectedCategory = '{{ $cat->id }}'"
                                :class="selectedCategory === '{{ $cat->id }}' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-lg font-bold whitespace-nowrap transition">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Quick Product Catalog Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($quickMedicines as $qm)
                    <div x-show="selectedCategory === 'all' || selectedCategory === '{{ $qm->category_id }}'"
                         @click="addToCart({{ json_encode([
                             'id' => $qm->id,
                             'name' => $qm->name,
                             'selling_price' => (float) $qm->selling_price,
                             'current_stock' => $qm->current_stock,
                             'dosage_form' => $qm->dosage_form,
                             'strength' => $qm->strength,
                             'unit' => $qm->unit,
                             'earliest_expiry' => $qm->earliest_expiry_batch?->expiry_date?->format('Y-m-d') ?? 'N/A'
                         ]) }})"
                         class="bg-white p-3.5 rounded-2xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-md transition cursor-pointer flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 mb-1">
                                <span class="truncate">{{ $qm->category?->name ?? 'General' }}</span>
                                <span class="font-bold px-1.5 py-0.5 rounded {{ $qm->current_stock > 10 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    Stock: {{ $qm->current_stock }}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 line-clamp-2 group-hover:text-emerald-700 transition">{{ $qm->name }}</h4>
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $qm->dosage_form }} • {{ $qm->strength }}</p>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-slate-900">{{ $currency }} {{ number_format($qm->selling_price, 2) }}</span>
                            <span class="w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:bg-emerald-600 group-hover:text-white transition">
                                <i class="fa-solid fa-plus"></i>
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right 5 Cols: Cart & Checkout Ledger -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 shadow-lg p-5 space-y-4 sticky top-20">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Current Cart</h3>
                        <p class="text-[10px] text-slate-500"><span x-text="cart.length"></span> item(s) selected</p>
                    </div>
                </div>

                <button type="button" @click="clearCart()" x-show="cart.length > 0" class="text-xs font-bold text-rose-600 hover:text-rose-700">
                    <i class="fa-solid fa-trash-can"></i> Clear
                </button>
            </div>

            <!-- Customer Picker + Quick Modal Trigger -->
            <div class="space-y-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Customer</label>
                <div class="flex gap-2">
                    <select x-model="customerId" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Walk-in Customer (General Public)</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'No phone' }})</option>
                        @endforeach
                    </select>
                    <button type="button" @click="showCustomerModal = true" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition" title="Add New Customer">
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                </div>
            </div>

            <!-- Cart Items Container -->
            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 space-y-2 pr-1">
                <template x-for="(item, index) in cart" :key="item.medicine_id">
                    <div class="py-2.5 flex items-center justify-between gap-3 text-xs">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-800 truncate" x-text="item.name"></p>
                            <p class="text-[10px] text-slate-500">{{ $currency }} <span x-text="item.unit_price.toFixed(2)"></span> each</p>
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                            <button type="button" @click="decrementQty(index)" class="w-6 h-6 rounded-lg bg-white shadow-xs text-slate-700 hover:bg-slate-200 font-bold flex items-center justify-center text-xs">-</button>
                            <input type="number" min="1" :max="item.available_stock" x-model.number="item.quantity" @change="validateQty(index)"
                                   class="w-10 text-center bg-transparent border-none text-xs font-bold focus:outline-none">
                            <button type="button" @click="incrementQty(index)" class="w-6 h-6 rounded-lg bg-white shadow-xs text-slate-700 hover:bg-slate-200 font-bold flex items-center justify-center text-xs">+</button>
                        </div>

                        <!-- Item Subtotal & Delete -->
                        <div class="text-right min-w-[70px]">
                            <p class="font-bold text-slate-900">{{ $currency }} <span x-text="((item.quantity * item.unit_price) - (item.discount || 0)).toFixed(2)"></span></p>
                            <button type="button" @click="removeFromCart(index)" class="text-[10px] text-rose-500 hover:text-rose-700 font-semibold">Remove</button>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="py-10 text-center text-slate-400">
                    <i class="fa-solid fa-basket-shopping text-3xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-semibold">Cart is currently empty</p>
                    <p class="text-[10px] text-slate-400">Scan barcode or pick medicines from catalog</p>
                </div>
            </div>

            <!-- Discount & Totals Breakdown -->
            <div class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-800">{{ $currency }} <span x-text="calculateSubtotal().toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Discount ({{ $currency }}):</span>
                    <input type="number" step="0.5" min="0" x-model.number="orderDiscount"
                           class="w-20 px-2 py-1 text-right bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:bg-white focus:outline-none">
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Tax ({{ $taxRate }}%):</span>
                    <span class="font-bold text-slate-800">{{ $currency }} <span x-text="calculateTax().toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-slate-200 text-sm font-black text-slate-900">
                    <span>Total Due:</span>
                    <span class="text-lg text-emerald-600">{{ $currency }} <span x-text="calculateTotal().toFixed(2)"></span></span>
                </div>
            </div>

            <!-- Payment Method Buttons -->
            <div class="space-y-1.5">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Payment Method</label>
                <div class="grid grid-cols-3 gap-2 text-xs font-bold">
                    <button type="button" @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="py-2.5 rounded-xl transition text-center flex flex-col items-center gap-1">
                        <i class="fa-solid fa-money-bill-1-wave"></i> Cash
                    </button>
                    <button type="button" @click="paymentMethod = 'mobile_money'"
                            :class="paymentMethod === 'mobile_money' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="py-2.5 rounded-xl transition text-center flex flex-col items-center gap-1">
                        <i class="fa-solid fa-mobile-screen-button"></i> MoMo
                    </button>
                    <button type="button" @click="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="py-2.5 rounded-xl transition text-center flex flex-col items-center gap-1">
                        <i class="fa-regular fa-credit-card"></i> Card
                    </button>
                </div>
            </div>

            <!-- Cash Tender & Preset Quick Buttons -->
            <div x-show="paymentMethod === 'cash'" class="space-y-2 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                <div class="flex justify-between items-center text-xs">
                    <label class="font-bold text-slate-700">Amount Tendered:</label>
                    <div class="flex items-center gap-1 font-mono font-bold text-slate-800">
                        <span>{{ $currency }}</span>
                        <input type="number" step="1" x-model.number="amountPaid"
                               class="w-24 px-2 py-1 bg-white border border-slate-300 rounded-lg text-right font-bold text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Quick Cash Presets -->
                <div class="flex gap-1.5 text-[11px] font-bold">
                    <button type="button" @click="amountPaid = calculateTotal()" class="px-2 py-1 bg-white border border-slate-200 rounded-md hover:bg-emerald-50 hover:text-emerald-700 flex-1 text-center">Exact</button>
                    <button type="button" @click="amountPaid = 20" class="px-2 py-1 bg-white border border-slate-200 rounded-md hover:bg-emerald-50 hover:text-emerald-700 flex-1 text-center">20</button>
                    <button type="button" @click="amountPaid = 50" class="px-2 py-1 bg-white border border-slate-200 rounded-md hover:bg-emerald-50 hover:text-emerald-700 flex-1 text-center">50</button>
                    <button type="button" @click="amountPaid = 100" class="px-2 py-1 bg-white border border-slate-200 rounded-md hover:bg-emerald-50 hover:text-emerald-700 flex-1 text-center">100</button>
                    <button type="button" @click="amountPaid = 200" class="px-2 py-1 bg-white border border-slate-200 rounded-md hover:bg-emerald-50 hover:text-emerald-700 flex-1 text-center">200</button>
                </div>

                <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-200">
                    <span class="text-slate-500">Change Due:</span>
                    <span class="font-bold font-mono text-emerald-700">{{ $currency }} <span x-text="calculateChange().toFixed(2)"></span></span>
                </div>
            </div>

            <!-- Complete Sale Button -->
            <button type="button" @click="processCheckout()" :disabled="cart.length === 0 || isProcessing"
                    class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 text-sm transition">
                <template x-if="!isProcessing">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-check-circle"></i> Complete Sale & Print</span>
                </template>
                <template x-if="isProcessing">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-circle-notch fa-spin"></i> Deducting FEFO Stock...</span>
                </template>
            </button>
        </div>
    </div>

    <!-- Quick Customer Modal -->
    <div x-cloak x-show="showCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
        <div @click.outside="showCustomerModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Register New Customer</h3>
                <button type="button" @click="showCustomerModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Customer Full Name *</label>
                    <input type="text" x-model="newCustName" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="e.g. Ama Mensah">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" x-model="newCustPhone" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="+233 24 000 0000">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Email (Optional)</label>
                    <input type="email" x-model="newCustEmail" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="ama@example.com">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showCustomerModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancel</button>
                <button type="button" @click="saveNewCustomer()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold">Save Customer</button>
            </div>
        </div>
    </div>

    <!-- Sale Success Modal -->
    <div x-cloak x-show="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl">
                <i class="fa-solid fa-check"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900">Sale Completed!</h3>
                <p class="text-xs text-slate-500 mt-1 font-mono">Invoice #<span x-text="completedSale.invoice_number"></span></p>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Charged:</span>
                    <span class="font-bold text-slate-900">{{ $currency }} <span x-text="completedSale.total"></span></span>
                </div>
                <div class="flex justify-between text-emerald-700 font-bold">
                    <span>Change Due:</span>
                    <span>{{ $currency }} <span x-text="completedSale.change_due"></span></span>
                </div>
            </div>
            <div class="flex gap-2">
                <a :href="completedSale.receipt_url" target="_blank" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </a>
                <button type="button" @click="showSuccessModal = false" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold">
                    Next Sale
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posTerminal() {
    return {
        selectedCategory: 'all',
        barcodeQuery: '',
        searchQuery: '',
        searchResults: [],
        cart: [],
        customerId: '',
        orderDiscount: 0,
        paymentMethod: 'cash',
        amountPaid: 0,
        taxRate: {{ $taxRate }},
        isProcessing: false,
        showCustomerModal: false,
        showSuccessModal: false,
        newCustName: '',
        newCustPhone: '',
        newCustEmail: '',
        completedSale: { invoice_number: '', total: '0.00', change_due: '0.00', receipt_url: '#' },

        initPos() {
            // Focus barcode or search
        },

        async scanBarcode() {
            if (!this.barcodeQuery.trim()) return;
            try {
                const res = await fetch(`{{ route('api.medicines.search') }}?barcode=${encodeURIComponent(this.barcodeQuery.trim())}`);
                const data = await res.json();
                if (data.success && data.medicine) {
                    this.addToCart(data.medicine);
                    this.barcodeQuery = '';
                } else {
                    alert('Medicine not found for this barcode.');
                }
            } catch (err) {
                alert('Error searching barcode.');
            }
        },

        async searchMedicines() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            try {
                const res = await fetch(`{{ route('api.medicines.search') }}?q=${encodeURIComponent(this.searchQuery.trim())}`);
                const data = await res.json();
                if (data.success) {
                    this.searchResults = data.medicines;
                }
            } catch (err) {
                console.error(err);
            }
        },

        addToCart(medicine) {
            if (medicine.current_stock <= 0) {
                alert(`'${medicine.name}' is currently out of active non-expired stock!`);
                return;
            }

            const existing = this.cart.find(item => item.medicine_id === medicine.id);
            if (existing) {
                if (existing.quantity + 1 > existing.available_stock) {
                    alert(`Cannot exceed available stock of ${existing.available_stock} units.`);
                    return;
                }
                existing.quantity++;
            } else {
                this.cart.push({
                    medicine_id: medicine.id,
                    name: medicine.name,
                    unit_price: medicine.selling_price,
                    quantity: 1,
                    available_stock: medicine.current_stock,
                    discount: 0,
                });
            }

            if (this.amountPaid === 0 || this.amountPaid < this.calculateTotal()) {
                this.amountPaid = this.calculateTotal();
            }
        },

        incrementQty(index) {
            const item = this.cart[index];
            if (item.quantity + 1 > item.available_stock) {
                alert(`Cannot exceed available stock of ${item.available_stock} units.`);
                return;
            }
            item.quantity++;
            this.amountPaid = this.calculateTotal();
        },

        decrementQty(index) {
            const item = this.cart[index];
            if (item.quantity > 1) {
                item.quantity--;
            } else {
                this.removeFromCart(index);
            }
            this.amountPaid = this.calculateTotal();
        },

        validateQty(index) {
            const item = this.cart[index];
            if (item.quantity < 1) item.quantity = 1;
            if (item.quantity > item.available_stock) {
                alert(`Cannot exceed available stock of ${item.available_stock} units.`);
                item.quantity = item.available_stock;
            }
            this.amountPaid = this.calculateTotal();
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
            this.amountPaid = this.calculateTotal();
        },

        clearCart() {
            this.cart = [];
            this.orderDiscount = 0;
            this.amountPaid = 0;
        },

        calculateSubtotal() {
            return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        },

        calculateTax() {
            const taxable = Math.max(0, this.calculateSubtotal() - (this.orderDiscount || 0));
            return this.taxRate > 0 ? (taxable * this.taxRate) / 100 : 0;
        },

        calculateTotal() {
            const taxable = Math.max(0, this.calculateSubtotal() - (this.orderDiscount || 0));
            return taxable + this.calculateTax();
        },

        calculateChange() {
            return Math.max(0, (this.amountPaid || 0) - this.calculateTotal());
        },

        async saveNewCustomer() {
            if (!this.newCustName.trim()) {
                alert('Customer name is required.');
                return;
            }

            try {
                const res = await fetch(`{{ route('api.customers.quick-store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.newCustName,
                        phone: this.newCustPhone,
                        email: this.newCustEmail
                    })
                });

                const data = await res.json();
                if (data.success && data.customer) {
                    this.customerId = data.customer.id;
                    this.showCustomerModal = false;
                    this.newCustName = '';
                    this.newCustPhone = '';
                    this.newCustEmail = '';
                    alert(`Customer '${data.customer.name}' registered & selected.`);
                }
            } catch (err) {
                alert('Error saving customer.');
            }
        },

        async processCheckout() {
            if (this.cart.length === 0) return;

            const total = this.calculateTotal();
            if (this.paymentMethod === 'cash' && this.amountPaid < total) {
                alert(`Amount paid (${this.amountPaid}) cannot be less than total (${total.toFixed(2)}).`);
                return;
            }

            this.isProcessing = true;

            try {
                const res = await fetch(`{{ route('pos.checkout') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        customer_id: this.customerId || null,
                        items: this.cart.map(item => ({
                            medicine_id: item.medicine_id,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            discount: item.discount || 0
                        })),
                        payment_method: this.paymentMethod,
                        amount_paid: this.amountPaid,
                    })
                });

                const data = await res.json();

                if (data.success) {
                    this.completedSale = {
                        invoice_number: data.invoice_number,
                        total: parseFloat(data.total).toFixed(2),
                        change_due: parseFloat(data.change_due).toFixed(2),
                        receipt_url: data.receipt_url
                    };
                    this.clearCart();
                    this.showSuccessModal = true;
                } else {
                    alert(data.message || 'Checkout failed.');
                }
            } catch (err) {
                alert('Error processing sale.');
            } finally {
                this.isProcessing = false;
            }
        }
    }
}
</script>
@endpush
