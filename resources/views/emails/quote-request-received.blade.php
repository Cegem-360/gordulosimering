<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajánlatkérés {{ $quoteRequest->reference }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #293133; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 600;">
                                Ajánlatkérését megkaptuk
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; color: #293133; font-size: 16px;">Kedves {{ $quoteRequest->name }}!</p>
                            <p style="margin: 0 0 20px; color: #666; font-size: 14px; line-height: 1.6;">
                                Köszönjük ajánlatkérését, megkaptuk. Kollégánk hamarosan jelentkezik ajánlatunkkal a megadott elérhetőségein.
                                Ha kérdése van, válaszoljon erre a levélre, vagy hívjon minket: +36 1 261 1566.
                            </p>
                            <p style="margin: 0; color: #666; font-size: 14px;">
                                <strong style="color: #293133;">Azonosító:</strong> {{ $quoteRequest->reference }}
                            </p>

                            @include('emails.partials.quote-request-items')

                            @if ($quoteRequest->message)
                                <h2 style="margin: 30px 0 15px; color: #293133; font-size: 18px; font-weight: 600; border-bottom: 2px solid #2271B3; padding-bottom: 10px;">
                                    Üzenete
                                </h2>
                                <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6;">{!! nl2br(e($quoteRequest->message)) !!}</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #293133; padding: 25px 30px; text-align: center;">
                            <p style="margin: 0; color: #ffffff; font-size: 14px;">
                                {{ config('app.name') }}
                            </p>
                            <p style="margin: 10px 0 0; color: #aaa; font-size: 12px;">
                                Kérdés esetén válaszoljon erre a levélre.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
