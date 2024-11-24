<?php

namespace App\Http\API;

use App\Http\Controllers\Controller;
use Firefly\FilamentBlog\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Отримання всіх постів
    public function index()
    {
        $posts = Post::with(['categories', 'tags'])->get();
        return response()->json($posts);
    }

    // Отримання поста за ID
    public function show($id)
    {
        $post = Post::with(['categories', 'tags'])->findOrFail($id);
        return response()->json($post);
    }

    public function store(Request $request)
    {
        // Валідуємо дані
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
            'cover_image_alt' => 'nullable|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:fblog_posts,slug',
            'status' => 'required|in:pending,published,scheduled',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:fblog_categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:fblog_tags,id',
        ]);

        // Обробка зображення, якщо воно є
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('cover_images', 'public');
        } else {
            $coverImagePath = null;
        }

        // Створення поста
        $post = Post::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'cover_photo_path' => $coverImagePath,
            'photo_alt_text' => $validated['cover_image_alt'] ?? null,
            'sub_title' => $validated['sub_title'] ?? null,
            'slug' => $validated['slug'],
            'status' => $validated['status'],
        ]);

        // Призначаємо категорії посту
        $post->categories()->sync($validated['category_ids']);

        // Призначаємо теги посту, якщо вони є
        if (!empty($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'message' => 'Пост успішно створено!',
            'data' => $post
        ], 201);
    }

    // Оновлення поста
    public function update(Request $request, $id)
    {
        // Валідуємо дані
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
            'cover_image_alt' => 'nullable|string|max:255',
            'sub_title' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:posts,slug,' . $id,
            'status' => 'required|in:draft,published,archived',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        // Знаходимо пост
        $post = Post::findOrFail($id);

        // Обробка зображення, якщо воно є
        if ($request->hasFile('cover_image')) {
            // Якщо є нове зображення, видаляємо старе
            if ($post->cover_image) {
                \Storage::delete('public/' . $post->cover_image);
            }
            $coverImagePath = $request->file('cover_image')->store('cover_images', 'public');
        } else {
            $coverImagePath = $post->cover_image;
        }

        // Оновлюємо пост
        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'cover_image' => $coverImagePath,
            'cover_image_alt' => $validated['cover_image_alt'] ?? null,
            'sub_title' => $validated['sub_title'] ?? null,
            'slug' => $validated['slug'],
            'status' => $validated['status'],
        ]);

        // Оновлюємо категорії посту
        $post->categories()->sync($validated['category_ids']);

        // Оновлюємо теги посту
        if (!empty($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'message' => 'Пост успішно оновлено!',
            'data' => $post
        ]);
    }

    public function destroy($id)
    {
        // Знаходимо пост
        $post = Post::findOrFail($id);

        // Видаляємо пост
        $post->delete();

        return response()->json([
            'message' => 'Пост успішно видалено!'
        ]);
    }
}
