{{-- Bulletproof button: VML for Outlook, <a> for everything else --}}
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:48px;v-text-anchor:middle;width:auto;" arcsize="13%" strokecolor="#2563eb" fillcolor="#2563eb">
    <w:anchorlock/>
    <center style="color:#ffffff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;font-size:15px;font-weight:600;">
        {{ $slot }}
    </center>
</v:roundrect>
<![endif]-->
<!--[if !mso]><!-->
<a href="{{ $url }}" target="_blank" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 15px; font-weight: 600; line-height: 1; text-decoration: none; padding: 14px 32px; border-radius: 6px; -webkit-text-size-adjust: none; mso-hide: all;">
    {{ $slot }}
</a>
<!--<![endif]-->
