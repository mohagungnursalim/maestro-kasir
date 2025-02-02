<div>
    {{-- Sidebar Section --}}
<aside id="logo-sidebar"
class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0"
aria-label="Sidebar">
<div class="h-full px-3 pb-4 overflow-y-auto bg-white">
    <ul class="space-y-2 font-medium">
        

        <li>
            <a 
                class="flex items-center p-2 rounded-lg {{ Request::is('dashboard') ? 'bg-purple-300 text-dark' : 'hover:bg-gray-200' }}"
                @if (!Request::is('dashboard'))
                    wire:navigate href="/dashboard"
                @endif>
                <i class="bi bi-speedometer"></i>
                <span class="flex-1 ms-3 whitespace-nowrap">Dashboard</span>
            </a>
        </li>
        
        <li>
            <a 
                class="flex items-center p-2 rounded-lg {{ Request::is('dashboard/transactions') ? 'bg-purple-300 text-dark' : 'hover:bg-gray-200' }}"
                @if (!Request::is('dashboard/transactions'))
                    wire:navigate href="/dashboard/transactions"
                @endif>
                <i class="bi bi-bag-check-fill"></i>
                <span class="flex-1 ms-3 whitespace-nowrap">Transaksi</span>
            </a>
        </li>
        
        
        

        <li>
            <button 
                type="button" 
                class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group {{ Request::is('dashboard/products') || Request::is('dashboard/suppliers') ? 'bg-gray-200' : 'hover:bg-gray-200' }}" 
                aria-controls="dropdown" 
                data-collapse-toggle="dropdown"
                aria-expanded="{{ Request::is('dashboard/products') || Request::is('dashboard/suppliers') ? 'true' : 'false' }}">
                <i class="bi bi-list-ul"></i>
                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Produk</span>
                <i class="bi bi-caret-down-fill"></i>
            </button>
            <ul 
                id="dropdown" 
                class="{{ Request::is('dashboard/products') || Request::is('dashboard/suppliers') ? '' : 'hidden' }} py-2 space-y-2">
                
                <a 
                    @if (Request::is('dashboard/products'))
                        class="flex items-center p-2 rounded-lg bg-purple-300 text-dark"
                    @else
                        wire:navigate href="/dashboard/products" 
                        class="flex items-center p-2 rounded-lg hover:bg-gray-200"
                    @endif
                >
                    <i class="bi bi-arrow-return-right"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Master Produk</span>
                </a>
        
                <a 
                    @if (Request::is('dashboard/suppliers'))
                        class="flex items-center p-2 rounded-lg bg-purple-300 text-dark"
                    @else
                        wire:navigate href="/dashboard/suppliers" 
                        class="flex items-center p-2 rounded-lg hover:bg-gray-200"
                    @endif
                >
                    <i class="bi bi-arrow-return-right"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Master Supplier</span>
                </a>
        
            </ul>
        </li>
        
        


    </ul>
</div>
</aside>
</div>