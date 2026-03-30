<div class="relative flex items-center mr-4">
    <i class="bi bi-shop text-xl sm:hidden text-gray-500 mr-2"></i>
    <select wire:model.live="activeBranchId" wire:change="switchBranch($event.target.value)" class="bg-white border-2 border-yellow-400 text-gray-900 text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2 py-1 shadow-sm font-semibold cursor-pointer outline-none hover:bg-yellow-50 transition-colors">
        @foreach($branches as $branch)
            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
        @endforeach
    </select>
</div>
