<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'relative text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l
        focus:ring-4 focus:outline-none focus:ring-purple-200
        font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2
        transition ease-in-out duration-150
        disabled:opacity-50 disabled:cursor-not-allowed'
    ]) }}
    wire:loading.attr="disabled"
>

    <span wire:loading.remove>
        {{ $slot }}
    </span>

    <span wire:loading class="flex items-center justify-center">
        Loading <i class="fas fa-spinner fa-spin"></i>
    </span>

</button>