<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('medicines')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category = Category::create($validated);

        AuditLog::log('create', 'Categories', (string) $category->id, "Created category: {$category->name}");

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' created successfully.");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $category->update($validated);

        AuditLog::log('update', 'Categories', (string) $category->id, "Updated category: {$category->name}");

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function destroy(Category $category)
    {
        if ($category->medicines()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because it contains {$category->medicines()->count()} medicine(s).");
        }

        $name = $category->name;
        $category->delete();

        AuditLog::log('delete', 'Categories', (string) $category->id, "Deleted category: {$name}");

        return redirect()->route('categories.index')->with('success', "Category '{$name}' deleted successfully.");
    }
}
