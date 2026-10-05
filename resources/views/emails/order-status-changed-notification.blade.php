<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendelés #{{ $order->id }}: {{ $order->order_status->getLabel() }}</title>
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
                                Rendelés #{{ $order->id }} – státuszváltozás
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border-radius: 6px; margin: 0 0 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Új állapot:</strong> {{ $order->order_status->getLabel() }}
                                            <span style="color: #999;">(korábban: {{ $previousStatus->getLabel() }})</span>
                                        </p>
                                        <p style="margin: 0 0 10px; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">Vevő:</strong> {{ $order->billing_name }}
                                            @if ($order->billing_company_name)
                                                ({{ $order->billing_company_name }})
                                            @endif
                                            – {{ $order->billing_email }}
                                        </p>
                                        <p style="margin: 0; color: #666; font-size: 14px;">
                                            <strong style="color: #293133;">A vevő értesítést kapott:</strong>
                                            {{ $customerNotified ? 'igen' : 'nem' }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            @include('emails.partials.order-items')

                            <p style="margin: 30px 0 0; font-size: 14px;">
                                <a href="{{ route('filament.admin.resources.orders.edit', $order) }}" style="color: #2271B3;">Rendelés megnyitása az adminban</a>
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
                                Ez egy automatikus üzenet, kérjük ne válaszoljon rá.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
