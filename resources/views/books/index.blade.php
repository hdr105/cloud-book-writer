<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Books') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @can('create-books')
                        <a href="{{ route('books.create') }}" class="text-indigo-600 hover:text-indigo-900 px-4 py-2 bg-indigo-100 hover:bg-indigo-200 rounded-md transition duration-300 ease-in-out mb-4 inline-block">Create Book</a>
                    @endcan

                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-xs leading-4 font-medium uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($books as $book)
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    {{ $book->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    @can('edit-books')
                                        <a href="{{ route('books.edit', $book->id) }}" class="text-indigo-600 hover:text-indigo-900 px-2 py-1 bg-indigo-100 hover:bg-indigo-200 rounded-md transition duration-300 ease-in-out">Edit</a>
                                    @endcan
                                    @can('show-books')
                                        <a href="{{ route('books.show', $book->id) }}" class="text-green-600 hover:text-green-900 px-2 py-1 bg-green-100 hover:bg-green-200 rounded-md transition duration-300 ease-in-out">Show</a>
                                    @endcan
                                    @can('delete-books')
                                        <form action="{{ route('books.destroy', $book->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 px-2 py-1 bg-red-100 hover:bg-red-200 rounded-md transition duration-300 ease-in-out">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $books->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
