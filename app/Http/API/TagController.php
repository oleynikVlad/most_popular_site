<?php

namespace App\Http\API;

use App\Http\Controllers\Controller;
use Firefly\FilamentBlog\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    // Отримати список усіх тегів
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags);
    }

    // Створити новий тег
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:fblog_tags,slug|max:255'
        ]);

        // Генеруємо slug, якщо не вказано
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    // Отримати конкретний тег
    public function show($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['error' => 'Tag not found'], 404);
        }

        return response()->json($tag);
    }

    // Оновити тег
    public function update(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['error' => 'Tag not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:tags,slug,' . $id . '|max:255'
        ]);

        // Генеруємо slug, якщо не вказано
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $tag->update($validated);

        return response()->json($tag);
    }

    // Видалити тег
    public function destroy($id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['error' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
