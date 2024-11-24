<?php

namespace App\Http\API;

use App\Http\Controllers\Controller;
use Firefly\FilamentBlog\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Отримати список усіх категорій
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Створити нову категорію
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:fblog_categories,slug|max:255'
        ]);

        // Генеруємо slug, якщо не надано
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    // Отримати конкретну категорію
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    // Оновити категорію
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|unique:categories,slug,' . $id . '|max:255'
        ]);

        // Генеруємо slug, якщо не надано
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json($category);
    }

    // Видалити категорію
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
