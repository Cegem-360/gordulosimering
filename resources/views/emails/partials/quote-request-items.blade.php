{{-- An ajánlatkérés's products; with $withPrices the shop also sees the list prices. --}}
@if ($quoteRequest->items->isNotEmpty())
                            <h2 style="margin: 30px 0 15px; color: #293133; font-size: 18px; font-weight: 600; border-bottom: 2px solid #2271B3; padding-bottom: 10px;">
                                Termékek
                            </h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                @foreach ($quoteRequest->items as $item)
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #eee;">
                                        <span style="color: #293133; font-size: 14px;">{{ $item->product_name }}</span>
                                        @if ($item->product_code)
                                            <br><span style="color: #999; font-size: 12px;">{{ $item->product_code }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #eee; text-align: right; white-space: nowrap;">
                                        <span style="color: #293133; font-size: 14px; font-weight: 600;">{{ $item->quantity }} db</span>
                                        @if (($withPrices ?? false) && $item->unit_price)
                                            <br><span style="color: #666; font-size: 12px;">{{ Number::currency((float) $item->unit_price, in: 'HUF', locale: 'hu', precision: 0) }} / db + ÁFA</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @if ($withPrices ?? false)
                                <tr>
                                    <td style="padding: 15px 0;">
                                        <span style="color: #293133; font-size: 14px; font-weight: 700;">Listaáron összesen (nettó)</span>
                                    </td>
                                    <td style="padding: 15px 0; text-align: right;">
                                        <span style="color: #2271B3; font-size: 16px; font-weight: 700;">
                                            {{ Number::currency($quoteRequest->indicativeNetTotal(), in: 'HUF', locale: 'hu', precision: 0) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
@endif
