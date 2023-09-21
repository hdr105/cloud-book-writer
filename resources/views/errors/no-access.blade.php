<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('No Access') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="javascript:history.back()" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                        <span class="ml-2">Back</span>
                    </a>
                    
                    <h1 class="text-3xl font-semibold mb-4">Access Denied</h1>
                    <p class="text-lg mb-4">Sorry, you do not have permission to view this content.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
