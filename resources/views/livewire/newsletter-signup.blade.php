<div>
    @if ($subscribed)
        <p class="flex items-center gap-2 max-w-2xl px-4 py-3 rounded bg-green-600/20 border border-green-500/40 text-green-100"
            role="status">
            <i class="fas fa-check-circle"></i>
            Köszönjük, feliratkozott hírlevelünkre!
        </p>
    @else
        <form wire:submit="subscribe" class="flex flex-col sm:flex-row gap-4 max-w-2xl" novalidate>
            <label for="newsletter-email" class="sr-only">E-mail cím</label>
            <input id="newsletter-email" type="email" wire:model="email" placeholder="Adja meg az email címét"
                autocomplete="email" required
                class="flex-1 px-4 py-3 rounded bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:border-white/40">
            <button type="submit" wire:loading.attr="disabled"
                class="px-8 py-3 bg-green-600 hover:bg-green-700 disabled:opacity-60 rounded font-medium transition-colors">
                Regisztrálok
            </button>
        </form>
        @error('email')
            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
        @enderror
    @endif
</div>
