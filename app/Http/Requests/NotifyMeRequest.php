<?php

namespace App\Http\Requests;

use App\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Validation\ValidationException;


class NotifyMeRequest extends FormRequest
{
    use ApiRequestTrait;
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
            'service_id' => 'required|exists:services,id',
            'address_id' => 'required|numeric|exists:user_addresses,id'
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $response = new JsonResponse([
            'status' => 0,
            'message' => $validator->errors()->first(),
            'data' => new stdClass,
        ], 200);

        throw new ValidationException($validator, $response);
    }
}
