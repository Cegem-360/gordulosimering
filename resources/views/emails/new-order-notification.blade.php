<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új rendelés érkezett</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #293133; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                Új rendelés érkezett!
                            </h1>
                            <p style="margin: 10px 0 0; color: #2271B3; font-size: 18px; font-weight: 600;">
                                #{{ $order->id }}
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <!-- Customer Info -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; margin: 0 0 25px;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; color: #856404; font-size: 14px; font-weight: 600;">
                                            Vásárló: {{ $order->billing_name }}
                                        </p>
                                        <p style="margin: 5px 0 0; color: #856404; font-size: 14px;">
                                            {{ $order->billing_email }} | {{ $order->billing_phone }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Order Details -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td width="50%" style="vertical-align: top; padding-right: 15px;">
                                        <h3 style="margin: 0 0 10px; color: #293133; font-size: 14px; font-weight: 700; text-transform: uppercase;">
                                            Számlázási adatok
                                        </h3>
                                        <p style="margin: 0; color: #666; font-size: 13px; line-height: 1.6;">
                                            {{ $order->billing_name }}<br>
                                            @if($order->billing_company_name)
                                                {{ $order->billing_company_name }}<br>
                                            @endif
                                            @if($order->billing_vat_number)
                                                Adószám: {{ $order->billing_vat_number }}<br>
                                            @endif
                                            {{ $order->billing_postcode }} {{ $order->billing_city }}<br>
                                            {{ $order->billing_address_1 }}
                                            @if($order->billing_address_2)
                                                <br>{{ $order->billing_address_2 }}
                                            @endif
                                        </p>
                                    </td>
                                    <td width="50%" style="vertical-align: top; padding-left: 15px;">
                                        <h3 style="margin: 0 0 10px; color: #293133; font-size: 14px; font-weight: 700; text-transform: uppercase;">
                                            Szállítási cím
                                        </h3>
                                        <p style="margin: 0; color: #666; font-size: 13px; line-height: 1.6;">
                                            {{ $order->shipping_name }}<br>
                                            {{ $order->shipping_postcode }} {{ $order->shipping_city }}<br>
                                            {{ $order->shipping_address_1 }}
                                            @if($order->shipping_address_2)
                                                <br>{{ $order->shipping_address_2 }}
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Order Info -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #e7f3ff; border-radius: 6px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <span style="color: #293133; font-size: 13px;"><strong>Fizetési mód:</strong> {{ $order->payment_method_title }}</span>
                                                </td>
                                                <td style="text-align: right;">
                                                    <span style="color: #293133; font-size: 13px;"><strong>Szállítás:</strong> {{ $order->shippingMethod?->name ?? 'N/A' }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Order Items -->
                            <h2 style="margin: 0 0 15px; color: #293133; font-size: 16px; font-weight: 700; text-transform: uppercase;">
                                Rendelt termékek
                            </h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #ddd; border-radius: 6px;">
                                <tr style="background-color: #f8f9fa;">
                                    <td style="padding: 10px 15px; font-size: 12px; font-weight: 700; color: #293133; text-transform: uppercase;">Termék</td>
                                    <td style="padding: 10px 15px; font-size: 12px; font-weight: 700; color: #293133; text-align: center; text-transform: uppercase;">Mennyiség</td>
                                    <td style="padding: 10px 15px; font-size: 12px; font-weight: 700; color: #293133; text-align: right; text-transform: uppercase;">Ár</td>
                                </tr>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td style="padding: 12px 15px; border-top: 1px solid #eee; font-size: 14px; color: #293133;">
                                        {{ $item->product->name }}
                                        <br><span style="color: #999; font-size: 12px;">SKU: {{ $item->product->sku ?? 'N/A' }}</span>
                                    </td>
                                    <td style="padding: 12px 15px; border-top: 1px solid #eee; font-size: 14px; color: #293133; text-align: center;">
                                        {{ $item->quantity }}
                                    </td>
                                    <td style="padding: 12px 15px; border-top: 1px solid #eee; font-size: 14px; color: #293133; text-align: right; font-weight: 600;">
                                        {{ number_format($item->subtotal, 0, ',', ' ') }} Ft
                                    </td>
                                </tr>
                                @endforeach
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="2" style="padding: 10px 15px; border-top: 1px solid #ddd; font-size: 13px; color: #666;">Szállítási költség</td>
                                    <td style="padding: 10px 15px; border-top: 1px solid #ddd; font-size: 13px; color: #293133; text-align: right;">
                                        {{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft
                                    </td>
                                </tr>
                                <tr style="background-color: #293133;">
                                    <td colspan="2" style="padding: 15px; font-size: 16px; color: #ffffff; font-weight: 700;">ÖSSZESEN (ÁFÁ-val)</td>
                                    <td style="padding: 15px; font-size: 18px; color: #2271B3; text-align: right; font-weight: 700;">
                                        {{ number_format($order->orderTotal() * 1.27 + $order->shipping_cost, 0, ',', ' ') }} Ft
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #eee;">
                            <p style="margin: 0; color: #666; font-size: 12px;">
                                {{ config('app.name') }} - Admin értesítés
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
