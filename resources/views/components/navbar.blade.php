<div>
   {{-- Navbar Section --}}
   <div class="flex items-center">
    <div class="flex items-center mr-5 ms-7">
        <div>
        <!-- Tombol Fullscreen -->
        <button type="button" id="fullscreenBtn"
            class="hidden sm:flex mr-6 text-sm rounded-full focus:ring-4 focus:ring-gray-300">
        <i class="bi bi-arrows-fullscreen"></i>
        </button>

        </div>
        <div class="mr-3">
            {{ Auth::user()->name }}
        </div>
        <div>
            <button type="button"
                class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
                aria-expanded="false" data-dropdown-toggle="dropdown-user">
                <span class="sr-only">Open user menu</span>
                <img class="w-8 h-8 rounded-full"
                    src="https://flowbite.com/docs/images/people/profile-picture-5.jpg" alt="user photo">
            </button>
        </div>
        <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow"
            id="dropdown-user">
            <div class="px-4 py-3" role="none">
                <p class="text-sm font-medium text-gray-900 truncate" role="none">
                    {{ Auth::user()->email }}
                </p>
            </div>
            <ul class="py-1" role="none">
                <li>
                    <a wire:navigate href="/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">Dashboard</a>
                </li>
                <li>
                    <a wire:navigate href="/dashboard/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">Profil</a>
                </li>
                <li>
                    <button 
                        type="button"
                        data-modal-target="popup-modal" 
                        data-modal-toggle="popup-modal" 
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                        Keluar Akun
                    </button>
                </li>



                
            </ul>
        </div>
    </div>
    
    {{-- Modal Konfirmasi Keluar --}}
    <div id="popup-modal" tabindex="-1" class="hidden fixed top-0 right-0 left-0 z-50 w-full h-full overflow-x-hidden overflow-y-auto justify-center items-center md:inset-0">
        <div class="relative p-4 w-full max-w-md max-h-full mx-auto mt-24">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                {{-- Tombol Tutup --}}
                <button 
                    type="button" 
                    class="absolute top-3 right-2.5 text-gray-400 hover:text-gray-900 hover:bg-gray-200 dark:hover:text-white dark:hover:bg-gray-600 rounded-lg text-sm w-8 h-8 flex items-center justify-center"
                    data-modal-hide="popup-modal"
                >
                    <svg class="w-3 h-3" aria-hidden="true" fill="none" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M1 1l6 6m0 0l6 6M7 7l6-6M7 7L1 13"/>
                    </svg>
                    <span class="sr-only">Tutup</span>
                </button>

                {{-- Konten Modal --}}
                <div class="p-4 md:p-5 text-center">
                    <svg class="mx-auto mb-4 w-12 h-12 text-gray-400 dark:text-gray-200" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                    </svg>                    
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                        Apakah kamu yakin untuk keluar dari sistem?
                    </h3>

                    {{-- Tombol Konfirmasi Logout --}}
                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                        @csrf
                        <button 
                            type="submit" 
                            data-modal-hide="popup-modal"
                            class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5"
                        >
                            Ya, keluar!
                        </button>
                        {{-- Tombol Batal --}}
                        <button 
                            type="button" 
                            data-modal-hide="popup-modal" 
                            class="mt-3 ms-3 py-2.5 px-5 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-gray-100"
                        >
                            Tidak, batal
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</div>