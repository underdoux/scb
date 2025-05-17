<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PostController extends Controller
{
    public function __construct(private PostService $postService)
    {}

    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'platform' => ['nullable', Rule::in(['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'])],
            'status' => ['nullable', Rule::in(['draft', 'scheduled', 'published', 'failed'])],
            'search' => 'nullable|string|max:100',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'sort_by' => ['nullable', Rule::in(['created_at', 'scheduled_time', 'platform', 'status'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $result = $this->postService->getPosts(Auth::user(), $filters);

        return Inertia::render('Posts/Index', [
            'posts' => $result['posts'],
            'filters' => $filters,
            'total' => $result['total']
        ]);
    }

    /**
     * Show the form for creating a new post
     */
    public function create()
    {
        return Inertia::render('Posts/Create');
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'hashtags' => 'nullable|string|max:1000',
            'platform' => ['required', Rule::in(['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'])],
            'scheduled_time' => 'nullable|date|after:now',
            'gpt_prompt' => 'nullable|string|max:1000',
            'gpt_response' => 'nullable|string'
        ]);

        $post = $this->postService->createPost($data, Auth::user());

        return redirect()->route('posts.edit', $post)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Generate content using OpenAI
     */
    public function generateContent(Request $request)
    {
        $data = $request->validate([
            'prompt' => 'required|string|max:1000'
        ]);

        $result = $this->postService->generateContent($data['prompt'], Auth::user());

        return response()->json($result);
    }

    /**
     * Generate content variations
     */
    public function generateVariations(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'count' => 'nullable|integer|min:1|max:5'
        ]);

        $result = $this->postService->generateVariations(
            $data['content'], 
            Auth::user(),
            $data['count'] ?? 3
        );

        return response()->json($result);
    }

    /**
     * Show the form for editing a post
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return Inertia::render('Posts/Edit', [
            'post' => $post->load('schedule')
        ]);
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $data = $request->validate([
            'content' => 'required|string|max:5000',
            'hashtags' => 'nullable|string|max:1000',
            'platform' => ['required', Rule::in(['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'])],
            'scheduled_time' => 'nullable|date|after:now',
            'gpt_prompt' => 'nullable|string|max:1000',
            'gpt_response' => 'nullable|string'
        ]);

        $this->postService->updatePost($post, $data);

        return redirect()->back()
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $this->postService->deletePost($post);

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Cancel scheduled post
     */
    public function cancelSchedule(Post $post)
    {
        $this->authorize('update', $post);

        if ($this->postService->cancelSchedule($post)) {
            return redirect()->back()
                ->with('success', 'Schedule cancelled successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to cancel schedule.');
    }
}
