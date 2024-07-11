<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiRequestTrait
{
    protected function failedValidation(Validator $validator)
    {

        $response = new JsonResponse([
            'status' => 0,
            'message' => $validator->errors()->first(),
            'data' => new stdClass,
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
