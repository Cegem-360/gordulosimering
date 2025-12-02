<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendelés visszaigazolás</title>
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
                                Köszönjük a rendelését!
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
                                Megkaptuk rendelését, és hamarosan feldolgozzuk. Az alábbiakban találja a rendelés részleteit.
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
                                            <strong style="color: #293133;">Fizetési mód:</strong> {{ $order->payment_method_title }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Order Items -->
                            <h2 style="margin: 30px 0 15px; color: #293133; font-size: 18px; font-weight: 600; border-bottom: 2px solid #2271B3; padding-bottom: 10px;">
                                Rendelt termékek
                            </h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                        <span style="color: #293133; font-size: 14px;">{{ $item->product->name }}</span>
                                        <span style="color: #666; font-size: 14px;"> x {{ $item->quantity }}</span>
                                    </td>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #eee; text-align: right;">
                                        <span style="color: #293133; font-size: 14px; font-weight: 600;">
                                            {{ number_format($item->subtotal, 0, ',', ' ') }} Ft
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                        <span style="color: #666; font-size: 14px;">Szállítási költség</span>
                                    </td>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #eee; text-align: right;">
                                        <span style="color: #293133; font-size: 14px;">
                                            {{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0;">
                                        <span style="color: #293133; font-size: 16px; font-weight: 700;">Összesen (ÁFÁ-val)</span>
                                    </td>
                                    <td style="padding: 15px 0; text-align: right;">
                                        <span style="color: #2271B3; font-size: 18px; font-weight: 700;">
                                            {{ number_format($order->orderTotal() * 1.27 + $order->shipping_cost, 0, ',', ' ') }} Ft
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Shipping Address -->
                            <h2 style="margin: 30px 0 15px; color: #293133; font-size: 18px; font-weight: 600; border-bottom: 2px solid #2271B3; padding-bottom: 10px;">
                                Szállítási cím
                            </h2>
                            <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.8;">
                                {{ $order->shipping_name }}<br>
                                {{ $order->shipping_postcode }} {{ $order->shipping_city }}<br>
                                {{ $order->shipping_address_1 }}
                                @if($order->shipping_address_2)
                                    <br>{{ $order->shipping_address_2 }}
                                @endif
                            </p>

                            <!-- CTA -->
                            <p style="margin: 30px 0 0; color: #293133; font-size: 16px; line-height: 1.6;">
                                Ha kérdése van rendelésével kapcsolatban, kérjük vegye fel velünk a kapcsolatot.
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
