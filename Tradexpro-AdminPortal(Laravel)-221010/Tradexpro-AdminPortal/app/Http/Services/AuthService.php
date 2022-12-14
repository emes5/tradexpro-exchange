<?php

namespace App\Http\Services;

use App\Http\Repositories\AffiliateRepository;
use App\Http\Repositories\AuthRepositories;
use App\Model\AffiliationCode;
use App\Model\UserVerificationCode;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class AuthService
{
    public $repository;
    public $logger;
    public $emailService;
    public function __construct()
    {
        $this->repository =  new AuthRepositories;
        $this->logger = new Logger;
        $this->emailService = new MailService;
    }

    // sign up process
    public function signUpProcess($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>(object)[]];
        DB::beginTransaction();
        $parentUserId = 0;
        try {
            if ($request->has('ref_code')) {
                $parentUser = AffiliationCode::where('code', $request->ref_code)->first();
                if (!$parentUser) {
                    return ['success' => false, 'message' => __('Invalid referral code.'), 'data' =>(object)[]];
                } else {
                    $parentUserId = $parentUser->user_id;
                }
            }
            $mail_key = $this->repository->generate_email_verification_key();
            $userData = [
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'role' => USER_ROLE_USER,
                'password' => Hash::make($request['password']),
            ];
            $user = $this->repository->create($userData);
            if ($user) {
                $userVerificationData = [
                    'user_id' => $user->id,
                    'code' => $mail_key,
                    'expired_at' => date('Y-m-d', strtotime('+15 days'))
                ];
                $userVerification = $this->repository->createUserVerification($userVerificationData);
                $wallet = $this->repository->createUserWallet($user->id);

                if ($parentUserId > 0) {
                    $this->logger->log('signUpProcess -> parent id -> '.$parentUserId);
                    $referralRepository = new AffiliateRepository;
                    $createdReferral = $referralRepository->createReferralUser($user->id, $parentUserId);
                }

                $this->sendVerifyemail($user, $mail_key);
                DB::commit();
                // all good
                $response = ['success' => true, 'message' => __('Sign up successful. Please verify your email'), 'data' =>(object)[]];
            }

        } catch (\Exception $e) {
            DB::rollback();
            $this->logger->log('signUpProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' =>(object)[]];
        }

        return $response;
    }


    // send verify email
    public function sendVerifyemail($user, $mail_key)
    {
        try {
            $userName = $user->first_name.' '.$user->last_name;
            $userEmail = $user->email;
            $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
            $subject = __('Email Verification | :companyName', ['companyName' => $companyName]);
            $data['data'] = $user;
            $data['key'] = $mail_key;
            if($user->role == USER_ROLE_ADMIN) {
                $template = 'email.verifyWeb';
            } else {
                $template = 'email.verifyapp';
            }
            $this->emailService->send($template, $data, $userEmail, $userName, $subject);
        } catch (\Exception $e) {
            $this->logger->log('sendVerifyemail', $e->getMessage());
        }
    }

    // password change process
    public function changePassword($request)
    {
        $data = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $user = Auth::user();
            if (!Hash::check($request->password, $user->password)) {

                $data['message'] = __('Old password doesn\'t match');
                return $data;
            }
            if (Hash::check($request->new_password, $user->password)) {
                $data['message'] = __('You already used this password');
                return $data;
            }

            $user->password = Hash::make($request->new_password);

            $user->save();
//         DB::table('oauth_access_tokens')
//             ->where('user_id', Auth::id())->where('id', '!=', Auth::user()->token()->id)
//             ->delete();

            return ['success' => true, 'message' => __('Password change successfully')];
        } catch (\Exception $exception)
        {
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    // send forgot mail process
    public function sendForgotMailProcess($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        $user = User::where(['email' => $request->email])->first();

        if ($user) {
            DB::beginTransaction();
            try {
                $key = randomNumber(6);
                $existsToken = User::join('user_verification_codes','user_verification_codes.user_id','users.id')
                    ->where('user_verification_codes.user_id',$user->id)
                    ->whereDate('user_verification_codes.expired_at' ,'>=', Carbon::now()->format('Y-m-d'))
                    ->first();
                if(!empty($existsToken)) {
                    $token = $existsToken->code;
                } else {
                    UserVerificationCode::create(['user_id' => $user->id, 'code'=>$key,'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
                    $token = $key;
                }
                $user_data = [
                    'user' => $user,
                    'token' => $token,
                ];

                $userName = $user->first_name.' '.$user->last_name;
                $userEmail = $user->email;
                $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                $subject = __('Forgot Password | :companyName', ['companyName' => $companyName]);
                $this->emailService->send('email.password_reset', $user_data, $userEmail, $userName, $subject);

                $data['message'] = __('Mail sent successfully to ') . $user->email . __(' with password reset code.');
                $data['success'] = true;
                Session::put(['resend_email'=>$user->email]);
                DB::commit();

                $response = ['success' => true, 'message' => $data['message']];
            } catch (\Exception $e) {
                DB::rollBack();
                $this->logger->log('sendForgotMailProcess', $e->getMessage());
                $response = ['success' => false, 'message' => __('Something went wrong')];
            }
        } else {
            $response = ['success' => false, 'message' => __('Email not found')];
        }

        return $response;
    }

    // reset password process
    public function passwordResetProcess($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $vf_code = UserVerificationCode::where(['code' => $request->token, 'status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                ->first();

            if (!empty($vf_code)) {
                $user = User::where(['id'=> $vf_code->user_id, 'email'=>$request->email])->first();
                if (empty($user)) {
                    $response = ['success' => false, 'message' => __('User not found')];
                }
                $data_ins['password'] = hash::make($request->password);
                $data_ins['is_verified'] = STATUS_SUCCESS;
                if(!Hash::check($request->password,User::find($vf_code->user_id)->password)) {

                    User::where(['id' => $vf_code->user_id])->update($data_ins);
                    UserVerificationCode::where(['id' => $vf_code->id])->delete();

                    $data['success'] = 'success';
                    $data['message'] = __('Password Reset Successfully');

                    $response = ['success' => true, 'message' => $data['message']];
                } else {
                    $data['success'] = 'dismiss';
                    $data['message'] = __('You already used this password');
                    $response = ['success' => false, 'message' => $data['message']];
                }
            } else {
                $data['success'] = 'dismiss';
                $data['message'] = __('Invalid code');

                $response = ['success' => false, 'message' => $data['message']];
            }
        } catch (\Exception $e) {
            $this->logger->log('passwordResetProcess', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }

        return $response;
    }

    // add new user process
    public function addNewUser($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        DB::beginTransaction();
        try {
            $userData = [
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'role' => $request->role,
                'phone' => $request->phone,
                'status' => STATUS_SUCCESS,
                'is_verified' => STATUS_SUCCESS,
                'password' => Hash::make(randomString(8)),
            ];
            $user = $this->repository->create($userData);
            if ($user) {
                $wallet = $this->repository->createUserWallet($user->id);

                $key = randomNumber(6);
                $existsToken = User::join('user_verification_codes', 'user_verification_codes.user_id', 'users.id')
                    ->where('user_verification_codes.user_id', $user->id)
                    ->whereDate('user_verification_codes.expired_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->first();

                if ( !empty($existsToken) ) {
                    $token = $existsToken->code;
                } else {
                    $s = UserVerificationCode::create(['user_id' => $user->id, 'code' => $key, 'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
                    $token = $key;
                }

                $user_data = [
                    'email' => $user->email,
                    'user' => $user,
                    'token' => $token,
                ];
                DB::commit();
                try {
                    $userName = $user->first_name.' '.$user->last_name;
                    $userEmail = $user->email;
                    $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                    $subject = __('Change Password | :companyName', ['companyName' => $companyName]);
                    $this->emailService->send('email.password_reset', $user_data, $userEmail, $userName, $subject);

                    $data['message'] = __('New user created and Mail sent successfully to ') . $user->email . __(' with password reset Code.');
                    $data['success'] = true;
                    Session::put(['resend_email' => $user->email]);

                    $response = ['success' => true, 'message' => $data['message']];
                } catch (\Exception $e) {
                    $response = ['success' => true, 'message' => __('New user created successfully but Mail not sent')];
                }
            } else {
                $response = ['success' => false, 'message' => __('Failed to create user')];
            }

        } catch (\Exception $e) {
            DB::rollback();
            $this->logger->log('addNewUser', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }

        return $response;
    }

    // verify email
    public function verifyEmailProcess($request)
    {
        $data = ['success' => false, 'message' => __('Something went wrong')];
        try {
            if($request->token) {
                $token = explode('email', $request->token);
                $user = User::where(['email' => decrypt($token[1])])->first();
            } else {
                $user = User::where(['email' => $request->email])->first();
            }
            if (!empty($user)) {
                if($request->token) {
                    $verify = UserVerificationCode::where(['user_id' => $user->id])
                        ->where('code', decrypt($token[0]))
                        ->where(['status'=> STATUS_PENDING,'type' => CODE_TYPE_EMAIL])
                        ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                        ->first();
                } else {
                    $verify = UserVerificationCode::where(['user_id' => $user->id])
                        ->where('code', $request->verify_code)
                        ->where(['status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                        ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                        ->first();
                }

                if ($verify) {
                    $check = $user->update(['is_verified' => STATUS_SUCCESS]);
                    if ($check) {
                        UserVerificationCode::where(['user_id' => $user->id, 'id' => $verify->id])->delete();
                        $data = ['success' => true, 'message' => __('Verify successful,you can login now')];
                    }
                } else {
                    Auth::logout();
                    $data = ['success' => false, 'message' => __('Your verify code was expired,you can generate new one')];
                }
            } else {
                $data = ['success' => false, 'message' => __('Your email not found or token expired')];
            }
        } catch (\Exception $e) {
            $this->logger->log('signUpProcess', $e->getMessage());
            $data = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $data;
    }

    // g2fa verify process
    public function g2fVerifyProcess($request)
    {
        try {
            $user = User::where('id',$request->user_id)->first();
            if ($request->code) {
                $google2fa = new Google2FA();
                $google2fa->setAllowInsecureCallToGoogleApis(true);
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code, 8);

                if ($valid){
                    Session::put('g2f_checked',true);
                    $token = $user->createToken($user->email)->accessToken;
                    $data['access_token'] = $token;
                    $data['access_type'] = 'Bearer';
                    $data['user'] = $user;
                    $data['user']->photo = show_image_path($user->photo,IMG_USER_PATH);
                    $data = ['success' => true, 'message' => __('Code verify success'), 'data' => $data];
                } else {
                    $data = ['success' => false, 'message' => __('Code doesn\'t match') , 'data' => []];
                }
            } else {
                $data = ['success' => false, 'message' => __('Code is required'), 'data' => []];
            }

        } catch (\Exception $e) {
            $this->logger->log('g2fVerifyProcess', $e->getMessage());
            $data = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $data;
    }
}
