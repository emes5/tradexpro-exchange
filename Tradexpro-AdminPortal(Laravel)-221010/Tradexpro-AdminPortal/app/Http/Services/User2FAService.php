<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use PragmaRX\Google2FA\Google2FA;

class User2FAService
{
    // user google 2fa validation check
    public function userGoogle2faValidation($user,$request)
    {
        try {
            if (!empty($request->code)) {
                $google2fa = new Google2FA();
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
                if ($valid) {
                    $response = responseData(true,__('Success'));
                } else {
                    $response = responseData(false,__('Verify code is invalid'));
                }
            } else {
                $response = responseData(false,__('Verify code is required'));
            }
        } catch (\Exception $e) {
            storeException('userGoogle2faValidation', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }
}
