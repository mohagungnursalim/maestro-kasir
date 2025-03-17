<button {{ $attributes->merge(['type' => 'submit', 'class' => 'text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:outline-none focus:ring-purple-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
