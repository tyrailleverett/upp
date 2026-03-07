@extends('mail.layout')

@section('title', __('Support Ticket Submitted'))
@section('preheader', __('We\'ve received your support ticket.'))

@section('content')
    <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #18181b; line-height: 28px;">
        {{ __('Support Ticket Submitted') }}
    </h1>
    <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Hi :name,', ['name' => $ticket->user->name]) }}
    </p>
    <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('We\'ve received your support ticket and our team will review it shortly.') }}
    </p>
    <table style="width: 100%; margin: 0 0 24px 0; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; font-size: 14px; color: #71717a; vertical-align: top; width: 100px;">{{ __('Title') }}</td>
            <td style="padding: 8px 0; font-size: 14px; color: #18181b;">{{ $ticket->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-size: 14px; color: #71717a; vertical-align: top;">{{ __('Topic') }}</td>
            <td style="padding: 8px 0; font-size: 14px; color: #18181b;">{{ $ticket->topic->value }}</td>
        </tr>
    </table>
    <p style="margin: 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('Thank you for reaching out to us.') }}
    </p>
    <hr style="margin: 24px 0; border: none; border-top: 1px solid #e4e4e7;">
    <p style="margin: 0; font-size: 13px; line-height: 20px; color: #a1a1aa;">
        {{ __('If you did not submit this ticket, please contact us immediately.') }}
    </p>
@endsection
