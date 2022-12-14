<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ChartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $periods = [
            '60m' => now()->subDays(365 * 5)->unix(),
            '12m' => now()->subDays(365)->unix(),
            '3m' => now()->subDays(120)->unix(),
            '1m' => now()->subDays(30)->unix(),
            '7d' => now()->subDays(7)->unix(),
            '3d' => now()->subDays(3)->unix(),
            '1d' => now()->subDay()->unix(),
        ];

        if ( $this->command === 'returnChartData' && isset($periods[$this->get('start')]) ) {
            $start = $periods[$this->get('start')];

            $this->merge([
                'start' => (int)($start / $this->get('interval', 300)) * $this->get('interval', 300)
            ]);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'command' => [
                'required',
                Rule::in([
                    'returnTicker',
                    'returnOrderBook',
                    'returnTradeHistory',
                    'returnChartData',
                ])
            ]
        ];

        if ($this->command === 'returnTicker' && $this->has('trade_coin_id') && $this->has('base_coin_id')) {
            $rules['trade_coin_id'] = [
                Rule::exists('coin_pairs', 'child_coin_id')->where('status', STATUS_ACTIVE)->where('parent_coin_id', $this->base_coin_id)
            ];
            $rules['base_coin_id'] = [
                Rule::exists('coin_pairs', 'parent_coin_id')->where('status', STATUS_ACTIVE)->where('child_coin_id', $this->trade_coin_id)
            ];
        } else if ($this->command === 'returnOrderBook') {
            $rules['trade_coin_id'] = [
                'required',
                Rule::exists('coin_pairs', 'child_coin_id')->where('status', STATUS_ACTIVE)->where('parent_coin_id', $this->base_coin_id)
            ];
            $rules['base_coin_id'] = [
                'required',
                Rule::exists('coin_pairs', 'parent_coin_id')->where('status', STATUS_ACTIVE)->where('child_coin_id', $this->trade_coin_id)
            ];
        } else if ($this->command === 'returnTradeHistory') {
            $rules['trade_coin_id'] = [
                'required',
                Rule::exists('coin_pairs', 'child_coin_id')->where('status', STATUS_ACTIVE)->where('parent_coin_id', $this->base_coin_id)
            ];
            $rules['base_coin_id'] = [
                'required',
                Rule::exists('coin_pairs', 'parent_coin_id')->where('status', STATUS_ACTIVE)->where('child_coin_id', $this->trade_coin_id)
            ];

            $rules['start'] = [
                'nullable',
                'integer'
            ];

            $rules['end'] = [
                'nullable',
                'integer',
                'gt:start'
            ];
        } else if ($this->command === 'returnChartData') {
            $rules['trade_coin_id'] = [
                'required',
                Rule::exists('coin_pairs', 'child_coin_id')->where('status', STATUS_ACTIVE)->where('parent_coin_id', $this->base_coin_id)
            ];
            $rules['base_coin_id'] = [
                'required',
                Rule::exists('coin_pairs', 'parent_coin_id')->where('status', STATUS_ACTIVE)->where('child_coin_id', $this->trade_coin_id)
            ];

            $rules['interval'] = [
                'integer',
                Rule::in([300, 900, 1800, 7200, 14400, 21600, 86400])
            ];

            $rules['start'] = [
                'required',
                'integer',
            ];

            $rules['end'] = [
                'integer',
                'gt:start'
            ];
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
