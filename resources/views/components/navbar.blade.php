<div>
   {{-- Navbar Section --}}
   <div class="flex items-center">
    <div class="flex items-center mr-5 ms-7">
        <div>
            {{-- Tombol Fullscreen --}}
            <button type="button" id="fullscreenBtn" aria-de
                class="flex mr-6 text-sm rounded-full focus:ring-4 focus:ring-gray-300"
                aria-expanded="false" data-dropdown-toggle="dropdown-user">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
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
                <p class="text-sm text-gray-900" role="none">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-sm font-medium text-gray-900 truncate" role="none">
                    {{ Auth::user()->email }}

                </p>
            </div>
            <ul class="py-1" role="none">
                <li>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">Dashboard</a>
                </li>
                <li>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">Settings</a>
                </li>
                <li>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">Earnings</a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                        @csrf

                        <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="#"
                            onclick="event.preventDefault(); 
                            if (confirm('Apakah Anda yakin ingin logout?')) {
                                this.closest('form').submit();
                            }">
                            Logout
                        </a>
                    </form>

                </li>
            </ul>
        </div>
    </div>
    
</div>
</div>