<?php

namespace App\Http\Controllers;

use App\Models\Serie;
use App\Models\User;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SerieController extends Controller
{
    public function index()
    {
        $series = Serie::where('is_active', true)
            ->with(['tags'])
            ->get();
        return view('v2.series.index', compact('series'));
    }

    public function show(string $serieSlug)
    {
        $serie = Serie::where('slug', $serieSlug)
            ->with(['tags', 'sponsors', 'users'])
            ->firstOrFail();
            
        $videos = $serie->videos;
        
        $videosArray = $videos->map(function($video) {
            return [
                'id' => $video->video_id,
                'title' => $video->pivot->title ?: $video->title,
                'description' => $video->pivot->description ?: $video->description,
            ];
        })->toArray();
        
        // Mapear os users para incluir o slug
        $researchers = $serie->users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'title' => $user->information?->title,
                'university' => $user->information?->university?->name,
                'picture_url' => $user->getPictureLink(),
                'minibio' => $user->information?->minibio,
            ];
        })->toArray();
        
        $seriesData = [
            'title' => $serie->title,
            'description' => $serie->description,
            'background-image' => $serie->background_image,
            'background-credit' => $serie->background_credit,
            'thumbnail' => $serie->thumbnail,
            'videos' => $videosArray,
            'researchers' => $researchers,
            'subjects' => [],
            'sponsors' => $serie->sponsors->pluck('image')->toArray(),
        ];
        
        $tags = $serie->tags;
        $resources = collect([]);
        
        return view('v2.series.detail', compact('serie', 'seriesData', 'tags', 'resources'));
    }

    public function indexV2()
    {
        $series = Serie::where('is_active', true)
            ->with(['tags'])
            ->get();
        return view('v2.series.index', compact('series'));
    }

    public function showV2(string $serieSlug)
    {
        $serie = Serie::where('slug', $serieSlug)
            ->with(['tags', 'sponsors', 'users'])
            ->firstOrFail();
            
        $videos = $serie->videos;
        
        $videosArray = $videos->map(function($video) {
            return [
                'id' => $video->video_id,
                'title' => $video->pivot->title ?: $video->title,
                'description' => $video->pivot->description ?: $video->description,
            ];
        })->toArray();
        
        // Mapear os users para incluir o slug
        $researchers = $serie->users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'title' => $user->information?->title,
                'university' => $user->information?->university?->name,
                'picture_url' => $user->getPictureLink(),
                'minibio' => $user->information?->minibio,
            ];
        })->toArray();
            
        $seriesData = [
            'title' => $serie->title,
            'description' => $serie->description,
            'background-image' => $serie->background_image,
            'background-img' => $serie->background_image,
            'background-credit' => $serie->background_credit,
            'thumbnail' => $serie->thumbnail,
            'videos' => $videosArray,
            'researchers' => $researchers,
            'subjects' => [],
            'sponsors' => $serie->sponsors->pluck('image')->toArray(),
        ];
        
        $tags = $serie->tags;
        $resources = collect([]);
        
        return view('v2.series.detail', compact('serie', 'seriesData', 'tags', 'resources'));
    }
}