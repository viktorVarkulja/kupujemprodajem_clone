<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool)$this->user();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:RSD,EUR,USD'],
            'city' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'condition' => ['required', 'in:new,like_new,used,for_parts'],
            'delivery_options' => ['nullable', 'array'],
            'delivery_options.*' => ['string'],
            'is_negotiable' => ['boolean'],

            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'cover_image_id' => ['nullable', 'integer'],
            'images_order' => ['nullable', 'array'],
        ];
    }
}

