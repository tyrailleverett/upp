@extends('mail.layout')

@section('title', __('Reset Password'))
@section('preheader', __('You requested a password reset for your account.'))

@section('content')
    <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #18181b; line-height: 28px;">
        {{ __('Reset your password') }}
    </h1>
    <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Hi :name,', ['name' => $user->name]) }}
    </p>
    <p style="margin: 0 0 32px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('You are receiving this email because we received a password reset request for your account.') }}
    </p>
    <table role="presentation" style="border: none; border-spacing: 0;">
        <tr>
            <td align="center">
                @component('mail.components.button', ['url' => $url])
                    {{ __('Reset Password') }}
                @endcomponent
            </td>
        </tr>
    </table>
    <p style="margin: 24px 0 0 0; font-size: 13px; line-height: 20px; color: #71717a;">
        {{ __('This password reset link will expire in :count minutes.', ['count' => $expireMinutes]) }}
    </p>
    <p style="margin: 8px 0 0 0; font-size: 13px; line-height: 20px; color: #71717a;">
        {{ __('If you did not request a password reset, no further action is required.') }}
    </p>
    <hr style="margin: 24px 0; border: none; border-top: 1px solid #e4e4e7;">
    <p style="margin: 0; font-size: 13px; line-height: 20px; color: #a1a1aa; word-break: break-all;">
        {{ __('If you\'re having trouble clicking the button, copy and paste this URL into your browser:') }}
        <a href="{{ $url }}" style="color: #2563eb; text-decoration: underline;">{{ $url }}</a>
    </p>
@endsection
