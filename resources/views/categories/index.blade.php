@extends('layouts.app')

@section('title', 'Medicine Categories')
@section('page-title', 'Pharmaceutical Categories')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, editMode: false, currentCategory: { id: null, name: '', description: '' } }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Categories Management</h1>
            <p class="text-xs text-slate-500">Organize pharmaceutical classifications (e.g., Antibiotics, Analgesics, Antimalarials).</p>
        </div>
        <button type="button" @click="editMode = false; currentCategory = { id: null, name: '', description: '' }; openModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-900/20 transition">
            <i class="fa-solid fa-plus"></i> Add Category
        </button>
    </div>

    <!-- Categories Grid / Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/75 text-slate-500 border-b border-slate-200 font-bold uppercase text-[10px]">
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Medicines Count</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $cat->name }}</td>
                            <td class="py-3 px-4 text-slate-600 max-w-md truncate">{{ $cat->description ?? 'No description provided' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $cat->medicines_count }} item(s)
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="editMode = true; currentCategory = { id: {{ $cat->id }}, name: '{{ addslashes($cat->name) }}', description: '{{ addslashes($cat->description) }}' }; openModal = true"
                                            class="p-1.5 text-emerald-600 hover:text-emerald-800 rounded-lg hover:bg-emerald-50" title="Edit">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 rounded-lg hover:bg-rose-50" title="Delete">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Modal -->
    <div x-cloak x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
        <div @click.outside="openModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900" x-text="editMode ? 'Edit Category' : 'New Category'"></h3>
                <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form :action="editMode ? '{{ url('categories') }}/' + currentCategory.id : '{{ route('categories.store') }}'" method="POST" class="space-y-4 text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="currentCategory.name" required
                           placeholder="e.g. Analgesics & Pain Relief"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" x-model="currentCategory.description" rows="3"
                              placeholder="Describe pharmaceutical purpose..."
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
