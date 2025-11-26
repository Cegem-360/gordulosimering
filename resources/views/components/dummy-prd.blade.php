<section class="py-8 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Legújabb termékeink</h2>
            <p class="text-gray-600">Tekintse meg legnépszerűbb termékeink választékát</p>
        </div>

        @php
            // Dummy products based on bearing categories
            $dummyProducts = [
                (object) [
                    'id' => 1,
                    'item_name' => 'Mélyhornyú golyóscsapágy 6205 ZZ',
                    'manufacturer' => (object) ['name' => 'SKF'],
                    'net_retail_price' => 2850,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 150,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 2,
                    'item_name' => 'Beálló golyóscsapágy 1306 ETN9',
                    'manufacturer' => (object) ['name' => 'FAG'],
                    'net_retail_price' => 4200,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 85,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 3,
                    'item_name' => 'Kúpgörgős csapágy 32216',
                    'manufacturer' => (object) ['name' => 'Timken'],
                    'net_retail_price' => 12500,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 45,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 4,
                    'item_name' => 'Hengergörgős csapágy NU 2210 ECP',
                    'manufacturer' => (object) ['name' => 'SKF'],
                    'net_retail_price' => 18900,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 0,
                    'min_order_quantity' => 1,
                    'in_stock' => false,
                ],
                (object) [
                    'id' => 5,
                    'item_name' => 'Y csapágy YAR 207-2F',
                    'manufacturer' => (object) ['name' => 'SKF'],
                    'net_retail_price' => 8750,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 120,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 6,
                    'item_name' => 'Axiális golyóscsapágy 51107',
                    'manufacturer' => (object) ['name' => 'FAG'],
                    'net_retail_price' => 3450,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 200,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 7,
                    'item_name' => 'Tűgörgős csapágy NK 25/20',
                    'manufacturer' => (object) ['name' => 'INA'],
                    'net_retail_price' => 2150,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 95,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 8,
                    'item_name' => 'Csapágyház SN 512',
                    'manufacturer' => (object) ['name' => 'SKF'],
                    'net_retail_price' => 15200,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 60,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 9,
                    'item_name' => 'Gömbcsukló GE 30 ES',
                    'manufacturer' => (object) ['name' => 'SKF'],
                    'net_retail_price' => 6890,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 75,
                    'min_order_quantity' => 1,
                    'in_stock' => true,
                ],
                (object) [
                    'id' => 10,
                    'item_name' => 'Axiális tűgörgős csapágy AXK 100135',
                    'manufacturer' => (object) ['name' => 'INA'],
                    'net_retail_price' => 9450,
                    'main_image' => Vite::asset('resources/images/bearing.webp'),
                    'all_quantity' => 0,
                    'min_order_quantity' => 1,
                    'in_stock' => false,
                ],
            ];
        @endphp

        <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
            @foreach ($dummyProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
