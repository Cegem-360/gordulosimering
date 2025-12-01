 <div class="bg-white rounded-lg border p-4 md:p-6" wire:key="cart-product-{{ $product->id }}">
     <div class="flex flex-col md:flex-row gap-4">
         <!-- Product Image -->
         <a href="{{ route('products.show', $product->slug) }}"
             class="shrink-0 w-full md:w-32 h-32 bg-gray-100 rounded-lg overflow-hidden">
             @if ($product->images)
                 <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
             @else
                 <div class="w-full h-full flex products-center justify-center">
                     <i class="fas fa-box text-4xl text-gray-300"></i>
                 </div>
             @endif
         </a>

         <!-- Product Details -->
         <div class="flex-1 min-w-0">
             <div class="flex flex-col md:flex-row md:justify-between gap-2">
                 <div>
                     <a href="{{ route('products.show', $product->slug) }}"
                         class="text-lg font-semibold text-blue-600 hover:underline line-clamp-2">
                         {{ $product->name }}
                     </a>
                     <p class="text-sm text-gray-500 mt-1">
                         Cikkszám: <span class="font-mono">{{ $product->product_code }}</span>
                     </p>
                 </div>
                 <div class="text-right">
                     @if ($product->isInStock())
                         <span
                             class="bg-green-500 text-white px-2 py-1 rounded text-xs font-bold flex products-center gap-1">
                             <i class="fa fa-cube"></i> Készleten
                         </span>
                     @else
                         <span
                             class="bg-orange-500 text-white px-2 py-1 rounded text-xs font-bold flex products-center gap-1">
                             <i class="fa fa-clock"></i> Rendelésre
                         </span>
                     @endif
                 </div>
             </div>

             <!-- Price and Quantity Row -->
             <div class="flex flex-col md:flex-row md:products-end justify-between mt-4 gap-4">
                 <!-- Quantity Selector -->
                 <div>
                     <label class="block text-sm text-gray-600 mb-1">Mennyiség
                         ({{ $product->quantity_unit }})
                     </label>
                     <div class="flex products-center gap-1">
                         <button type="button" wire:click="decreaseQuantity()"
                             class="w-10 h-10 rounded-l-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                             <i class="fas fa-minus text-xs"></i>
                         </button>
                         <input type="number" wire:model.live="quantity"
                             class="w-16 h-10 text-center font-semibold border-y border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                         <button type="button" wire:click="increaseQuantity()"
                             class="w-10 h-10 rounded-r-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                             <i class="fas fa-plus text-xs"></i>
                         </button>
                     </div>
                 </div>

                 <!-- Price -->
                 <div class="text-right">
                     <p class="text-sm text-gray-500">Egységár (nettó)</p>
                     <p class="text-lg font-semibold">
                         {{ Number::currency($product->purchase_currency_price, 'HUF', 'hu', 0) }}
                     </p>
                     <p class="text-xl font-bold text-blue-600 mt-1">
                         {{ Number::currency($product->purchase_currency_price * $quantity, 'HUF', 'hu', 0) }}
                     </p>
                 </div>
             </div>

             <!-- Remove Button -->
             <div class="mt-4 pt-4 border-t flex justify-end">
                 <button type="button" wire:click="removeProduct()"
                     class="text-red-600 hover:text-red-700 text-sm inline-flex products-center gap-1 cursor-pointer">
                     <i class="fas fa-trash-alt"></i>
                     Eltávolítás
                 </button>
             </div>
         </div>
     </div>
 </div>
