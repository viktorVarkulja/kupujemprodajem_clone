<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use Inertia\Inertia;

class AdPageController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->select(['id','name','slug','parent_id'])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('market/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $categories = Category::query()
            ->select(['id','name','slug','parent_id'])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('market/Create', [
            'categories' => $categories,
        ]);
    }

    public function show(Ad $ad)
    {
        $ad->load(['images', 'category']);
        return Inertia::render('market/Show', [
            'ad' => $ad,
        ]);
    }

    public function myListings()
    {
        return Inertia::render('market/MyListings');
    }

    public function edit(Ad $ad)
    {
        $user = request()->user();
        if (!$user || $user->id !== $ad->user_id) {
            abort(403);
        }

        $categories = Category::query()
            ->select(['id','name','slug','parent_id'])
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        $ad->load(['images', 'category']);

        return Inertia::render('market/Edit', [
            'ad' => $ad,
            'categories' => $categories,
        ]);
    }
}
