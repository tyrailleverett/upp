@extends('mail.layout')

@section('title', __('New Support Ticket Received'))
@section('preheader', __('A new support ticket has been submitted.'))

@section('content')
    <h1 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 700; color: #18181b; line-height: 28px;">
        {{ __('New Support Ticket Received') }}
    </h1>
    <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 24px; color: #3f3f46;">
        {{ __('A new support ticket has been submitted by :name.', ['name' => $ticket->user->name]) }}
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
        <tr>
            <td style="padding: 8px 0; font-size: 14px; color: #71717a; vertical-align: top;">{{ __('User') }}</td>
            <td style="padding: 8px 0; font-size: 14px; color: #18181b;">{{ $ticket->user->email }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; font-size: 14px; color: #71717a; vertical-align: top;">{{ __('Description') }}</td>
            <td style="padding: 8px 0; font-size: 14px; color: #18181b;">{{ $ticket->description }}</td>
        </tr>
    </table>
@endsection
