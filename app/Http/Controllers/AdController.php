<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $query = Ad::query()
            ->with(['category', 'coverImage'])
            ->status('active');

        if ($search = $request->string('q')->toString()) {
            $query->search($search);
        }

        if ($currency = $request->string('currency')->toString()) {
            $query->currency($currency);
        }

        if ($city = $request->string('city')->toString()) {
            $query->whereRaw('LOWER(city) = ?', [strtolower($city)]);
        }

        if ($condition = $request->string('condition')->toString()) {
            $query->where('condition', $condition);
        }

        $min = $request->has('price_min') ? (float)$request->query('price_min') : null;
        $max = $request->has('price_max') ? (float)$request->query('price_max') : null;
        $query->priceBetween($min, $max);

        if ($categoryId = $request->query('category_id')) {
            $ids = Category::idsWithDescendants((int)$categoryId);
            $query->whereIn('category_id', $ids);
        }

        $sort = $request->string('sort')->toString();
        if ($sort === 'price_asc') $query->orderBy('price');
        elseif ($sort === 'price_desc') $query->orderByDesc('price');
        else $query->orderByDesc('published_at')->orderByDesc('id');

        $ads = $query->paginate($request->integer('per_page', 15));

        return response()->json($ads);
    }

    public function show(Ad $ad)
    {
        $ad->load(['category', 'images']);
        return response()->json($ad);
    }

    public function store(StoreAdRequest $request)
    {
        $user = $request->user();

        $ad = DB::transaction(function () use ($request, $user) {
            $ad = Ad::create([
                'user_id' => $user->id,
                'category_id' => $request->integer('category_id'),
                'title' => $request->string('title')->toString(),
                'description' => $request->string('description')->toString(),
                'price' => $request->input('price'),
                'currency' => $request->string('currency')->toString() ?: 'RSD',
                'city' => $request->string('city')->toString(),
                'phone' => $request->string('phone')->toString(),
                'condition' => $request->string('condition')->toString() ?: 'used',
                'delivery_options' => $request->input('delivery_options'),
                'is_negotiable' => (bool)$request->boolean('is_negotiable', false),
                'status' => 'active',
                'published_at' => now(),
            ]);

            $this->syncImages($ad, $request);

            return $ad;
        });

        return response()->json($ad->load(['images', 'category']), 201);
    }

    public function update(UpdateAdRequest $request, Ad $ad)
    {
        if ($request->user()->id !== $ad->user_id) {
            abort(403);
        }

        DB::transaction(function () use ($request, $ad) {
            $ad->update([
                'category_id' => $request->has('category_id') ? $request->integer('category_id') : $ad->category_id,
                'title' => $request->has('title') ? $request->string('title')->toString() : $ad->title,
                'description' => $request->has('description') ? $request->string('description')->toString() : $ad->description,
                'price' => $request->has('price') ? $request->input('price') : $ad->price,
                'currency' => $request->has('currency') ? $request->string('currency')->toString() : $ad->currency,
                'city' => $request->has('city') ? $request->string('city')->toString() : $ad->city,
                'phone' => $request->has('phone') ? $request->string('phone')->toString() : $ad->phone,
                'condition' => $request->has('condition') ? $request->string('condition')->toString() : $ad->condition,
                'delivery_options' => $request->has('delivery_options') ? $request->input('delivery_options') : $ad->delivery_options,
                'is_negotiable' => (bool)$request->boolean('is_negotiable', $ad->is_negotiable),
            ]);

            $this->syncImages($ad, $request);
        });

        return response()->json($ad->load(['images', 'category']));
    }

    public function destroy(Request $request, Ad $ad)
    {
        if (!$request->user() || $request->user()->id !== $ad->user_id) {
            abort(403);
        }

        // Delete files
        $dir = 'ads/'.$ad->id;
        if (Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->deleteDirectory($dir);
        }

        $ad->delete();

        return response()->noContent();
    }

    private function syncImages(Ad $ad, Request $request): void
    {
        // Remove specific images
        $removeIds = collect($request->input('remove_image_ids', []))->filter()->all();
        if (!empty($removeIds)) {
            $images = AdImage::query()->where('ad_id', $ad->id)->whereIn('id', $removeIds)->get();
            foreach ($images as $img) {
                if ($img->path && Storage::disk('public')->exists($img->path)) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }
        }

        // Upload new images
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            foreach ($files as $index => $file) {
                $path = $file->store('ads/'.$ad->id, 'public');
                $ad->images()->create([
                    'path' => $path,
                    'is_cover' => false,
                    'position' => (int)($ad->images()->max('position') + 1),
                ]);
            }
        }

        // Set cover image
        $coverImageId = $request->input('cover_image_id');
        if ($coverImageId) {
            $ad->images()->update(['is_cover' => false]);
            $cover = $ad->images()->where('id', $coverImageId)->first();
            if ($cover) {
                $cover->is_cover = true;
                $cover->save();
            }
        } else {
            // Ensure exactly one cover exists
            if (!$ad->images()->where('is_cover', true)->exists()) {
                $first = $ad->images()->orderBy('position')->first();
                if ($first) {
                    $first->is_cover = true;
                    $first->save();
                }
            }
        }

        // Reorder images if provided
        $order = $request->input('images_order'); // [id1, id2, id3]
        if (is_array($order)) {
            foreach ($order as $pos => $id) {
                AdImage::query()->where('ad_id', $ad->id)->where('id', $id)->update(['position' => (int)$pos]);
            }
        }
    }
}
