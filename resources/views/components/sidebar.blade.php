<div>
 
 <!-- drawer component -->
<div id="drawer-navigation"
    class="pt-20 fixed top-0 left-0 z-40 w-64 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white dark:bg-gray-800"
    tabindex="-1"
    aria-labelledby="drawer-navigation-label">
     
     <button type="button" data-drawer-hide="drawer-navigation" aria-controls="drawer-navigation" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 end-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" >
         <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
         <span class="sr-only">Close menu</span>
     </button>
   <div class="py-4 overflow-y-auto">
    <ul class="space-y-2 font-medium">
        
        <div class="text-center">
            <a class="text-center text-xs text-gray-500">Analitik</a>
        </div>
        
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
        
        <div class="text-center">
            <hr>
            <a class="text-center text-xs text-gray-500">Pembayaran</a>
        </div>
        @hasrole('admin|owner|kasir')
        <li>
            <a 
                class="flex items-center p-2 rounded-lg {{ Request::is('dashboard/orders') ? 'bg-purple-300 text-dark' : 'hover:bg-gray-200' }}"
                @if (!Request::is('dashboard/orders'))
                    wire:navigate href="/dashboard/orders"
                @endif>
                <i class="bi bi-cash-coin"></i>
                <span class="flex-1 ms-3 whitespace-nowrap">Kasir</span>
            </a>
        </li>
        @endrole

        
        <li>
            <button 
                type="button" 
                class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group {{ Request::is('dashboard/transactions') || Request::is('dashboard/reports') ? 'bg-gray-200' : 'hover:bg-gray-200' }}" 
                aria-controls="dropdown-transactions"
                data-collapse-toggle="dropdown-transactions"
                aria-expanded="{{ Request::is('dashboard/transactions') || Request::is('dashboard/reports') ? 'true' : 'false' }}">
                <i class="bi bi-wallet2"></i>
                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Transaksi</span>
                <i class="bi bi-caret-down-fill"></i>
            </button>
            <ul 
                id="dropdown-transactions"
                class="{{ Request::is('dashboard/transactions') || Request::is('dashboard/reports') ? '' : 'hidden' }} py-2 space-y-2">
                
                <a 
                    @if (Request::is('dashboard/transactions'))
                        class="flex items-center p-2 rounded-lg bg-purple-300 text-dark"
                    @else
                        wire:navigate href="/dashboard/transactions" 
                        class="flex items-center p-2 rounded-lg hover:bg-gray-200"
                    @endif
                >
                    <i class="bi bi-arrow-return-right"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Rekap Transaksi</span>
                </a>
        
                @hasrole('admin|owner')
                <a 
                    @if (Request::is('dashboard/reports'))
                        class="flex items-center p-2 rounded-lg bg-purple-300 text-dark"
                    @else
                        wire:navigate href="/dashboard/reports" 
                        class="flex items-center p-2 rounded-lg hover:bg-gray-200"
                    @endif
                >
                    <i class="bi bi-arrow-return-right"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">File Laporan</span>
                </a>
                @endrole

            </ul>
        </li>
        

        <div class="text-center">
            <hr>
            <a class="text-center text-xs text-gray-500">Menu</a>
        </div>
        <li>
            <button 
                type="button" 
                class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group {{ Request::is('dashboard/products') || Request::is('dashboard/suppliers') ? 'bg-gray-200' : 'hover:bg-gray-200' }}" 
                aria-controls="dropdown" 
                data-collapse-toggle="dropdown"
                aria-expanded="{{ Request::is('dashboard/{products,suppliers,units}') ? 'true' : 'false' }}">
                <i class="bi bi-list-ul"></i>
                <span class="flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">Produk</span>
                <i class="bi bi-caret-down-fill"></i>
            </button>
            <ul 
                id="dropdown" 
                class="{{ Request::is('dashboard/products') || Request::is('dashboard/suppliers') || Request::is('dashboard/units') ? '' : 'hidden' }} py-2 space-y-2">
                
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
        
                @hasrole('admin|owner')
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
                @endrole

                @hasrole('admin|owner')
                <a 
                    @if (Request::is('dashboard/units'))
                        class="flex items-center p-2 rounded-lg bg-purple-300 text-dark"
                    @else
                        wire:navigate href="/dashboard/units" 
                        class="flex items-center p-2 rounded-lg hover:bg-gray-200"
                    @endif
                >
                    <i class="bi bi-arrow-return-right"></i>
                    <span class="flex-1 ms-3 whitespace-nowrap">Master Satuan</span>
                </a>
                @endrole
        
            </ul>
        </li>

        <div class="text-center">
            <hr>
            <a class="text-center text-xs text-gray-500">Pengaturan</a>
        </div>
        @hasrole('admin|owner')
        <li>
            <a 
                class="flex items-center p-2 rounded-lg {{ Request::is('dashboard/store-settings') ? 'bg-purple-300 text-dark' : 'hover:bg-gray-200' }}"
                @if (!Request::is('dashboard/store-settings'))
                    wire:navigate href="/dashboard/store-settings"
                @endif>
                <i class="bi bi-shop"></i>
                <span class="flex-1 ms-3 whitespace-nowrap">Pengaturan Toko</span>
            </a>
        </li>
        
        <li>
            <a 
                class="flex items-center p-2 rounded-lg {{ Request::is('dashboard/users-management') ? 'bg-purple-300 text-dark' : 'hover:bg-gray-200' }}"
                @if (!Request::is('dashboard/users-management'))
                    wire:navigate href="/dashboard/users-management"
                @endif>
                <i class="bi bi-people"></i>
                <span class="flex-1 ms-3 whitespace-nowrap">Manajemen User</span>
            </a>
        </li>

        <li>
            <a 
                class="flex items-center p-2 rounded-lg {{ Request::is('dashboard/roles-permission') ? 'bg-purple-300 text-dark' : 'hover:bg-gray-200' }}"
                @if (!Request::is('dashboard/roles-permission'))
                    wire:navigate href="/dashboard/roles-permission"
                @endif>
                <i class="bi bi-check2-square"></i>
                <span class="flex-1 ms-3 whitespace-nowrap">Peran & Izin User</span>
            </a>
        </li>
        @endrole
        


    </ul>
    </div>
 </div>
 
</div>