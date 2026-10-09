{{-- The order's items, shipping cost and gross total; used by the order emails. --}}
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
                                            {{ Number::currency($item->subtotal, in: 'HUF', locale: 'hu', precision: 0) }}
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
                                            {{ Number::currency($order->shipping_cost, in: 'HUF', locale: 'hu', precision: 0) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0;">
                                        <span style="color: #293133; font-size: 16px; font-weight: 700;">Összesen (ÁFÁ-val)</span>
                                    </td>
                                    <td style="padding: 15px 0; text-align: right;">
                                        <span style="color: #2271B3; font-size: 18px; font-weight: 700;">
                                            {{ Number::currency($order->orderTotal() * 1.27 + $order->shipping_cost, in: 'HUF', locale: 'hu', precision: 0) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
