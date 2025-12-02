<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új kapcsolatfelvételi üzenet</title>
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
                                Új kapcsolatfelvételi üzenet
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; color: #293133; font-size: 16px; line-height: 1.6;">
                                Új üzenet érkezett a weboldalon keresztül.
                            </p>

                            <!-- Sender Info Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Feladó neve:</strong> {{ $senderName }}
                                        </p>
                                        <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Email cím:</strong>
                                            <a href="mailto:{{ $senderEmail }}" style="color: #2271B3; text-decoration: none;">{{ $senderEmail }}</a>
                                        </p>
                                        <p style="margin: 0; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Tárgy:</strong> {{ $formSubject }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Message Content -->
                            <h2 style="margin: 30px 0 15px; color: #293133; font-size: 18px; font-weight: 600; border-bottom: 2px solid #2271B3; padding-bottom: 10px;">
                                Üzenet tartalma
                            </h2>
                            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin: 15px 0;">
                                <p style="margin: 0; color: #293133; font-size: 14px; line-height: 1.8; white-space: pre-wrap;">{{ $messageContent }}</p>
                            </div>

                            <!-- Reply CTA -->
                            <p style="margin: 30px 0 0; text-align: center;">
                                <a href="mailto:{{ $senderEmail }}?subject=Re: {{ $formSubject }}"
                                   style="display: inline-block; background-color: #2271B3; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">
                                    Válasz küldése
                                </a>
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
                                Ez az üzenet a weboldalon keresztül érkezett.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
