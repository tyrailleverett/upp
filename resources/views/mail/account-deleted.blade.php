@extends('mail.layout')

@section('title', __('Account Deleted'))
@section('preheader', __('We\'re sorry to see you go.'))

@section('content')
    <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #18181b; line-height: 28px;">
        {{ __('We\'re sorry to see you go') }}
    </h1>
    <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Hi :name,', ['name' => $user->name]) }}
    </p>
    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Your account has been successfully deleted. We\'re sad to see you leave, and we hope your experience with :app was a positive one.', ['app' => config('app.name')]) }}
    </p>
    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('If you ever change your mind, you\'re always welcome back.') }}
    </p>
    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Thank you for being a part of :app.', ['app' => config('app.name')]) }}
    </p>
    <p style="margin: 0 0 32px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Your feedback is optional but greatly appreciated — it helps us improve for everyone.') }}
    </p>
    <table role="presentation" style="border: none; border-spacing: 0;">
        <tr>
            <td align="center">
                @component('mail.components.button', ['url' => $feedbackUrl])
                    {{ __('Send Feedback') }}
                @endcomponent
            </td>
        </tr>
    </table>
    <p style="margin: 32px 0 0 0; font-size: 13px; line-height: 20px; color: #71717a; word-break: break-all;">
        {{ __('If you\'re having trouble clicking the button, copy and paste this URL into your browser:') }}
        <a href="{{ $feedbackUrl }}" style="color: #2563eb; text-decoration: underline;">{{ $feedbackUrl }}</a>
    </p>
    <hr style="margin: 24px 0; border: none; border-top: 1px solid #e4e4e7;">
    <p style="margin: 0; font-size: 13px; line-height: 20px; color: #a1a1aa;">
        {{ __('If you did not delete your account, please contact us immediately.') }}
    </p>
@endsection
