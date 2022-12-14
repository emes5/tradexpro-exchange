@extends('auth.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : __('Admin Login'))

@section('content')
    <div class="user-content-wrapper" style="background-image: @if(!empty(settings('login_logo')))  url('{{asset(path_image().settings()['login_logo'])}}') @else url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}') @endif">
        <div class="user-content-inner-wrap">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="user-form">
                        <div class="user-form-inner">
                            <div class="form-top">
                                <h2>{{__('Sign In')}}</h2>
                                <p>{{__('Please sign in to your account')}}</p>
                            </div>
                            {{Form::open(['route' => 'loginProcess', 'files' => true])}}
                            <div class="form-group">
                                <input type="email" value="{{old('email')}}" id="exampleInputEmail1" name="email"
                                        class="form-control" placeholder="{{__('Your email')}}">
                                @error('email')
                                <p class="invalid-feedback">{{ $message }} </p>
                                @enderror
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" id="exampleInputPassword1"
                                        class="form-control form-control-password look-pass-a"
                                        placeholder="{{__('Your password')}}">
                                @error('password')
                                <p class="invalid-feedback">{{ $message }} </p>
                                @enderror
                                <span class="eye"><i class="fa fa-eye-slash toggle-password"
                                                        onclick="showHidePassword('old_password')"></i></span>
                            </div>
                            @if(settings('google_recapcha'))
                                <div class="form-group">
                                    <label></label>
                                    {!! app('captcha')->display() !!}
                                    @error('g-recaptcha-response')
                                    <p class="invalid-feedback">{{ $message }} </p>
                                    @enderror
                                </div>
                            @endif
                            <div class="d-flex justify-content-between rememberme align-items-center mb-4">
                                <div class="text-right"><a class="text-theme forgot-password" href="{{route('forgotPassword')}}">{{__('Forgot Password?')}}</a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary nimmu-user-sibmit-button">{{__('Sign In')}}</button>
                            {{Form::close()}}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="user-content-text text-center">
                        <h3>{{__('Welcome To')}}</h3>
                        <a class="auth-logo" href="javascript:;">
                            <img src="{{show_image(1,'logo')}}" class="img-fluid" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            $(".toggle-password").on('click', function () {
                $(this).toggleClass("fa-eye-slash fa-eye");
            });

            $(".eye").on('click', function () {
                var $pwd = $(".look-pass-a");
                if ($pwd.attr('type') === 'password') {
                    $pwd.attr('type', 'text');
                } else {
                    $pwd.attr('type', 'password');
                }
            });
        })(jQuery)
    </script>
@endsection
