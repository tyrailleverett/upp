<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', config('app.name'))</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        * { margin: 0; padding: 0; }
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { width: 100% !important; height: 100% !important; margin: 0 !important; padding: 0 !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; margin: auto !important; }
            .fluid { max-width: 100% !important; height: auto !important; margin-left: auto !important; margin-right: auto !important; }
            .stack-column { display: block !important; width: 100% !important; max-width: 100% !important; }
            .email-padding { padding-left: 20px !important; padding-right: 20px !important; }
        }
    </style>
    <span class="preheader" style="display: none !important; visibility: hidden; mso-hide: all; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden;">
        @yield('preheader')
    </span>
</head>
<body style="margin: 0; padding: 0; word-spacing: normal; background-color: #ffffff;">
    <div role="article" aria-roledescription="email" lang="en" style="text-size-adjust: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #ffffff;">
        <table role="presentation" style="width: 100%; border: none; border-spacing: 0; background-color: #ffffff;">
            <tr>
                <td align="center" style="padding: 40px 20px;">
                    <!--[if mso]>
                    <table role="presentation" align="center" style="width:600px;">
                    <tr>
                    <td>
                    <![endif]-->
                    <table role="presentation" class="email-container" style="width: 100%; max-width: 600px; border: none; border-spacing: 0; text-align: left; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                        {{-- Logo / App Name --}}
                        <tr>
                            <td style="padding: 0 0 24px 0; text-align: center;">
                                <span style="font-size: 18px; font-weight: 700; color: #18181b; letter-spacing: -0.025em;">{{ config('app.name') }}</span>
                            </td>
                        </tr>

                        {{-- Card --}}
                        <tr>
                            <td style="border: 1px solid #e4e4e7; border-radius: 12px; overflow: hidden;">
                                <table role="presentation" style="width: 100%; border: none; border-spacing: 0;">
                                    <tr>
                                        <td class="email-padding" style="padding: 40px 48px;">
                                            @yield('content')
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- Footer --}}
                        <tr>
                            <td style="padding: 32px 0 0 0; text-align: center;">
                                @hasSection('footer')
                                    @yield('footer')
                                @else
                                    <p style="margin: 0; font-size: 13px; line-height: 20px; color: #a1a1aa;">
                                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                                    </p>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <!--[if mso]>
                    </td>
                    </tr>
                    </table>
                    <![endif]-->
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
