<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool)$this->user();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'required', 'in:RSD,EUR,USD'],
            'city' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'condition' => ['sometimes', 'required', 'in:new,like_new,used,for_parts'],
            'delivery_options' => ['nullable', 'array'],
            'delivery_options.*' => ['string'],
            'is_negotiable' => ['boolean'],

            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:5120'],
            'cover_image_id' => ['nullable', 'integer'],
            'cover_image_index' => ['nullable', 'integer', 'min:1'],
            'images_order' => ['nullable', 'array'],
        ];
    }
}
