<?php

namespace Botble\Ecommerce\Http\Requests\API;

use Botble\Support\Http\Requests\Request;

class AddCompareRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:ec_products,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'product_id' => [
                'description' => 'The ID of the product to add to the compare list',
                'example' => 1,
            ],
        ];
    }
}
