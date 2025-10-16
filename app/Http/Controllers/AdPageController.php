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

        return Inertia::render('ads/Index', [
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

        return Inertia::render('ads/Create', [
            'categories' => $categories,
        ]);
    }

    public function show(Ad $ad)
    {
        $ad->load(['images', 'category']);
        return Inertia::render('ads/Show', [
            'ad' => $ad,
        ]);
    }
}

