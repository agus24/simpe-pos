<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\JsonErrorResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "code" => "required|unique:products,code," . $this->product?->id,
            "name" => "required",
            "price" => "required|numeric|min:0"
        ];
    }

    /**
     * Save Process
     *
     * @param boolean $isUpdating
     * @return Product
     */
    public function save(bool $isUpdating = false)
    {
        if (!$isUpdating) {
            return Product::create($this->only('code', 'name', 'price'));
        }

        Cache::lock("product:{$this->product->id}")->get(function() {
            $this->product->update($this->only('code', 'name', 'price'));
        });

        return $this->product;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            (new JsonErrorResource(data: $errors))->toResponse($this)
        );
    }
}
