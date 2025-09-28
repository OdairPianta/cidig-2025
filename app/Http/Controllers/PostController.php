<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    private function findUserBySlug(string $userSlug): User
    {
        
        $user = User::get()->filter(function ($user) use ($userSlug) {
            return Str::slug($user->name) === $userSlug;
        })->first();

        if (!$user) {
            abort(404, 'Pesquisador não encontrado');
        }

        return $user;
    }

    public function showUserPosts(string $userSlug)
    {
        $user = $this->findUserBySlug($userSlug);
        
        $posts = $user->posts()
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('posts.user-posts', compact('user', 'posts'));
    }

    public function show(string $userSlug, string $postSlug)
    {
        $user = $this->findUserBySlug($userSlug);
        
        $post = $user->posts()
            ->where('slug', $postSlug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('posts.show', compact('user', 'post'));
    }

    public function index()
    {
        $posts = Post::where('is_published', true)
            ->with('user')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('posts.index', compact('posts'));
    }
}