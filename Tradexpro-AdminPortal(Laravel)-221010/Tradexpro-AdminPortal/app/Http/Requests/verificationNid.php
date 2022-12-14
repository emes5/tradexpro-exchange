<?php

namespace App\Http\Requests;

use App\Model\VerificationDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class verificationNid extends FormRequest
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
        $check = VerificationDetails::where('user_id',Auth::id())->where('field_name','file_two')->exists();
        $check2 = VerificationDetails::where('user_id',Auth::id())->where('field_name','file_three')->exists();
        if ($check and $check2){
            return [
                'file_two'=>'mimes:jpeg,png,jpg|max:2048',
                'file_three'=>'mimes:jpeg,png,jpg|max:2048'
            ];
        }else
            return [
                'file_two'=>'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'file_three'=>'required|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ];

    }

    public function messages()
    {
        $messages=[
            'file_two.required'=>__('NID front copy is required'),
            'file_two.mimes'=>__('NID front copy is must be(jpeg,png,jpg) '),
            'file_three.mimes'=>__('NID front copy is must be(jpeg,png,jpg) '),
            'file_three.required'=>__('NID back copy is required')

        ];

        return $messages;
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        if ($validator->fails()) {
            $e = $validator->errors()->all();
            foreach ($e as $error) {
                $errors[] = $error;
            }
        }
        $json = ['success'=>false,
            'data'=>[],
            'message' => $errors[0],
        ];
        $response = new JsonResponse($json, 200);

        throw (new ValidationException($validator, $response))->errorBag($this->errorBag)->redirectTo($this->getRedirectUrl());


    }

}
