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
                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                        @csrf

                        <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="#"
                            onclick="event.preventDefault(); 
                            if (confirm('Apakah Anda yakin ingin mengakhiri sesi?')) {
                                this.closest('form').submit();
                            }">
                            Keluar Akun
                        </a>
                    </form>

                </li>
            </ul>
        </div>
    </div>
    
</div>
</div>