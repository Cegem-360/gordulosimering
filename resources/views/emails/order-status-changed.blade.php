<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendelése: {{ $status->getLabel() }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #2271B3; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                Rendelése: {{ $status->getLabel() }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; color: #293133; font-size: 16px; line-height: 1.6;">
                                Kedves <strong>{{ $order->billing_name }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; color: #293133; font-size: 16px; line-height: 1.6;">
                                {{ $status->customerMessage() }}
                            </p>

                            <!-- Order Info Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Rendelés száma:</strong> #{{ $order->id }}
                                        </p>
                                        <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Rendelés dátuma:</strong> {{ $order->created_at->format('Y. m. d.') }}
                                        </p>
                                        <p style="margin: 0; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Állapot:</strong> {{ $status->getLabel() }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            @include('emails.partials.order-items')

                            <p style="margin: 30px 0 0; color: #293133; font-size: 16px; line-height: 1.6;">
                                Kérdése van? Válaszoljon erre a levélre, vagy írjon a
                                <a href="mailto:{{ config('shop.admin_email') }}" style="color: #2271B3;">{{ config('shop.admin_email') }}</a> címre.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #293133; padding: 25px 30px; text-align: center;">
                            <p style="margin: 0; color: #ffffff; font-size: 14px;">
                                {{ config('app.name') }}
                            </p>
                            <p style="margin: 10px 0 0; color: #aaa; font-size: 12px;">
                                Erre a levélre válaszolva ügyfélszolgálatunkat éri el.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
