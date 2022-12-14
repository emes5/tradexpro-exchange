@extends('auth.master',['menu'=>'dashboard'])
@section('title', isset($title) ? $title : '')

@section('content')
    <div class="user-content-wrapper" style="background-image: @if(!empty(settings('login_logo')))  url('{{asset(path_image().settings()['login_logo'])}}') @else url('{{asset('assets/user/images/user-content-wrapper-bg.jpg')}}') @endif">
            <div class="user-form">
                <div class="right">
                    <div class="form-top">
                        <a class="auth-logo" href="javascript:">
                            <img src="{{show_image(1,'logo')}}" class="img-fluid" alt="">
                        </a>
                        <h2>{{__('Two Factor Authentication')}}</h2>
                        <p>{{__('Open your authentication app and enter the code for')}} {{settings('app_title')}}</p>
                    </div>
                    {{Form::open(['route' => 'g2fVerify', 'files' => true])}}
                    <div class="form-group">
                        <label>{{__('Authentication Code')}}</label>
                        <input type="text" value="{{old('code')}}" id="exampleInputEmail1" name="code"
                               class="form-control" placeholder="{{__('code')}}">
                        @error('code')
                        <p class="invalid-feedback">{{ $message }} </p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary nimmu-user-sibmit-button">{{__('Verify')}}</button>
                    {{Form::close()}}
                </div>
            </div>
    </div>
@endsection

@section('script')
@endsection
