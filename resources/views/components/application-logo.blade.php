<a href="/" class="flex flex-col items-center justify-center w-full max-w-xs mx-auto mb-6">
    <img src="{{ asset($settings->store_logo ?? '/logo/default.png') }}" 
         class="h-16 w-auto mb-1" 
         alt="Store Logo" />
    <span class="text-xl font-serif font-semibold sm:text-2xl whitespace-nowrap tracking-wide italic text-center">
        {{ $settings->store_name ?? 'Nama Toko' }}
    </span>
</a>
