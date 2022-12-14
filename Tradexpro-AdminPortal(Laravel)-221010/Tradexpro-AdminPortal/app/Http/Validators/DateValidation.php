<?php

namespace App\Http\Validators;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;

class DateValidation {
    use ValidatesRequests;

    private $rules;
    private $messages;

    /**
     * DateValidation constructor.
     * Initialized the rules and messages for validation
     */
    public function __construct() {
        $this->rules = [
            'from_date' => 'required|date|date_format:"Y-m-d"',
        ];

        $this->messages = [
            'from_date.required' =>  __('Please enter from date.'),
            'from_date.date' =>  __('Please enter a valid date.'),
            'from_date.date_format' =>  __('Please enter date in yyyy-mm-dd format.'),
            'to_date.required' =>  __('Please enter to date.'),
            'to_date.date' =>  __('Please enter a valid date.'),
            'to_date.date_format' =>  __('Please enter date in yyyy-mm-dd format.'),
            'to_date.after' =>  __('To date must be greater than or equal from date.'),
        ];
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function dateValidate(Request $request){
        $temp = new DateTime($request->get('from_date'));
        $from = $temp->modify('-1 day')
            ->format('Y-m-d');
        $this->rules = ['to_date' => 'required|date|date_format:"Y-m-d"|after:'.$from,];

        return $this->validate($request, $this->rules, $this->messages);
    }
}
