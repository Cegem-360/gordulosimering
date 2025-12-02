<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CartFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUserId($value)
 */
	final class Cart extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property int $cart_id
 * @property int $quantity
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Cart|null $cart
 * @property-read \App\Models\Product|null $product
 * @method static \Database\Factories\CartItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereUpdatedAt($value)
 */
	final class CartItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read Category|null $parentCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 */
	final class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property int $shipping_method_id
 * @property string $payment_method
 * @property string $payment_method_title
 * @property bool $set_paid
 * @property string|null $billing_name
 * @property string|null $billing_address_1
 * @property string|null $billing_address_2
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_postcode
 * @property string|null $billing_country
 * @property string|null $billing_email
 * @property string|null $billing_phone
 * @property string|null $billing_vat_number
 * @property string|null $billing_company_name
 * @property string|null $billing_company_office
 * @property string|null $shipping_name
 * @property string|null $shipping_address_1
 * @property string|null $shipping_address_2
 * @property string|null $shipping_city
 * @property string|null $shipping_state
 * @property string|null $shipping_postcode
 * @property string|null $shipping_country
 * @property string $shipping_tracking_number
 * @property string|null $order_key
 * @property \App\Enums\OrderStatus $order_status
 * @property string $order_currency
 * @property int $shipping_cost
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \App\Models\ShippingMethod $shippingMethod
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingCompanyOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingVatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentMethodTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereSetPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 */
	final class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string|null $tax_class
 * @property string|null $subtotal
 * @property string|null $subtotal_tax
 * @property string|null $total
 * @property string|null $total_tax
 * @property int $quantity
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSubtotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTaxClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereUpdatedAt($value)
 */
	final class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $group_code
 * @property string|null $product_code
 * @property bool|null $is_service
 * @property string|null $name
 * @property string $slug
 * @property string|null $catalog_number
 * @property string|null $type
 * @property string|null $size
 * @property numeric|null $weight
 * @property string|null $rating
 * @property string|null $quality
 * @property string|null $product_variety
 * @property string|null $trade_type
 * @property string|null $usage_type
 * @property string|null $currency_settlement
 * @property string|null $discount_group
 * @property bool|null $is_on_sale
 * @property numeric|null $sale_percentage
 * @property string|null $pricing
 * @property numeric|null $list_price
 * @property numeric|null $list_discount
 * @property numeric|null $purchase_currency_price
 * @property string|null $currency
 * @property numeric|null $currency_multiplier
 * @property numeric|null $purchase_price
 * @property numeric|null $profit_margin
 * @property numeric|null $net_selling_price
 * @property string|null $vat_class
 * @property numeric|null $gross_selling_price
 * @property string|null $quantity_unit
 * @property string|null $secondary_unit
 * @property int|null $minimum_stock
 * @property int|null $maximum_stock
 * @property int|null $buffer_stock
 * @property int|null $order_unit
 * @property string|null $ksh_prefix
 * @property string|null $ksh_number
 * @property string|null $supplier
 * @property string|null $short_note
 * @property string|null $description
 * @property string|null $barcode
 * @property string|null $ean_code
 * @property int|null $min_order_quantity
 * @property int|null $trade_quantity
 * @property int|null $pallet_quantity
 * @property array<array-key, mixed>|null $custom_fields
 * @property array<array-key, mixed>|null $images
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $image
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBufferStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCatalogNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCurrencyMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCurrencySettlement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDiscountGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEanCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGrossSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGroupCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsOnSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKshNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereKshPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereListDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereListPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMaximumStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinOrderQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinimumStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereNetSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOrderUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePalletQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePricing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductVariety($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProfitMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePurchaseCurrencyPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQuantityUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSalePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSecondaryUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTradeQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTradeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUsageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVatClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWeight($value)
 */
	final class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $cost
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Database\Factories\ShippingMethodFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingMethod whereUpdatedAt($value)
 */
	final class ShippingMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $phone
 * @property string|null $billing_name
 * @property string|null $billing_company_name
 * @property string|null $billing_vat_number
 * @property string|null $billing_company_office
 * @property string|null $billing_postcode
 * @property string|null $billing_city
 * @property string|null $billing_address_1
 * @property string|null $billing_address_2
 * @property string|null $billing_country
 * @property string|null $billing_state
 * @property string|null $shipping_name
 * @property string|null $shipping_postcode
 * @property string|null $shipping_city
 * @property string|null $shipping_address_1
 * @property string|null $shipping_address_2
 * @property string|null $shipping_country
 * @property string|null $shipping_state
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingCompanyOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingVatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShippingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	final class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

