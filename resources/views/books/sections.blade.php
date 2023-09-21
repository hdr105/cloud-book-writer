<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sections for Book') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="javascript:history.back()" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                        <span class="ml-2">Back</span>
                    </a>
                    @can('create-sections')
                        <button class="text-indigo-600 hover:text-indigo-900 ml-4" id="addSectionBtn">Add Section</button>
                    @endcan
                    
                    @foreach ($sections as $section)
                        <div class="mb-4">
                            <h1 class="text-3xl font-semibold">
                                <a href="{{ route('books.subsections', ['book' => $book->id, 'section' => $section->id]) }}" class="text-indigo-600 hover:text-indigo-900">{{ $section->title }}</a>
                            </h1>
                            <p class="text-lg">{{ $section->description }}</p>
                        </div>
                        @can('edit-sections')
                            <button class="text-indigo-600 hover:text-indigo-900 ml-2 editSectionBtn" data-section-id="{{ $section->id }}">Edit</button>
                        @endcan
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal modal-lg fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSectionModalLabel">Add Section</h5>
                    <button type="button" class="btn-close closeModalBtn" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSectionForm">
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <input type="hidden" name="parent_id" value="null">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary closeModalBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSectionBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSectionModalLabel">Edit Section</h5>
                    <button type="button" class="btn-close closeEditModalBtn" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSectionForm">
                        <input type="hidden" name="section_id" id="editSectionId">
                        <div class="form-group">
                            <label for="editTitle">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="editDescription">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="4"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary closeEditModalBtn">Close</button>
                    <button type="button" class="btn btn-primary" id="updateSectionBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<script>
    // Open the modal when the "Add Section" button is clicked
    $('#addSectionBtn').click(function () {
        $('#addSectionModal').modal('show');
    });

    $('.closeModalBtn').click(function () {
        $('#addSectionModal').modal('hide');
    });

    // Handle form submission when the "Save" button is clicked
    $('#saveSectionBtn').click(function () {
        // Get form data
        const formData = $('#addSectionForm').serialize();

        // Perform an AJAX request to submit the form data
        $.ajax({
            url: "{{ route('sections.store') }}", // Replace with your route for creating sections
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                $('#addSectionModal').modal('hide');
                location.reload();
            }
        });
    });

    $('.editSectionBtn').click(function () {
        const sectionId = $(this).data('section-id');
    
        // Make an AJAX GET request to fetch section data
        $.get(`/sections/${sectionId}`, function (data) {
            // Populate the modal with the fetched data
            $('#editSectionId').val(data.id);
            $('#editTitle').val(data.title);
            $('#editDescription').val(data.description);
            $('#editSectionModal').modal('show');
        });
    });

    $('.closeEditModalBtn').click(function () {
        $('#editSectionModal').modal('hide');
    });
    
    $('#updateSectionBtn').click(function () {
        // Get form data
        const formData = $('#editSectionForm').serialize();

        // Get the section ID from the form
        const sectionId = $('#editSectionId').val();

        // Perform an AJAX request to update the section data
        $.ajax({
            url: `/sections/${sectionId}`, // Replace with your route for updating sections
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                $('#editSectionModal').modal('hide');
                location.reload();
            }
        });
    });
</script>

