<!DOCTYPE html>
<html>
<head>
    <title>CRUD Books</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <h2>Daftar Buku</h2>
        <button class="btn btn-success mb-2" id="createNewBook">Add Book</button>
        <table id="bookTable" class="table table-bordered data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                    <th>Status</th>
                    {{-- <th>Description</th> --}}
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- ModalAdd -->
    <div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modelHeading"></h5>
                </div>
                <div class="modal-body">
                    <form id="bookForm">
                        @csrf
                        <input type="hidden" name="book_id" id="book_id">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="cover">Cover Image</label>
                            <input type="file" class="form-control" id="cover" name="cover">
                        </div>                        
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                        <div class="form-group">
                            <label for="year">Year</label>
                            <input type="number" class="form-control" id="year" name="year" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Published">Published</option>
                                <option value="Not Published">Not Published</option>
                            </select>
                        </div>                        
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ModalView -->
    <div class="modal fade" id="bookDetailModal" tabindex="-1" role="dialog" aria-labelledby="bookDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookDetailModalLabel">Book Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Title:</strong> <span id="book-title"></span></p>
                    <p><strong>Author:</strong> <span id="book-author"></span></p>
                    <p><strong>Year:</strong> <span id="book-year"></span></p>
                    <p><strong>Status:</strong> <span id="book-status"></span></p>
                    <p><strong>Description:</strong> <span id="book-description"></span></p>
                    <p><strong>Cover:</strong> <br> <img id="book-cover" src="" alt="Cover" width="150"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(function () {
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('books.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                    {data: 'cover', name: 'cover', render: function(data, type, full, meta){
                        return '<img src="/covers/'+data+'" height="200"/>';
                    }},
                    {data: 'title', name: 'title'},
                    {data: 'author', name: 'author'},
                    {data: 'year', name: 'year'},
                    {data: 'status', name: 'status'},
                    // {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, render: function(data, type, full, meta){
                        return `
                            <button class="btn btn-info view-details" data-id="${full.id}">View Details</button>
                            <button class="btn btn-warning edit-book" data-id="${full.id}">Edit</button>
                            <button class="btn btn-danger delete-book" data-id="${full.id}">Delete</button>`;
                    }},
                ]
            });

            $('#createNewBook').click(function () {
                $('#bookForm').trigger("reset");
                $('#modelHeading').html("Create New Book");
                $('#ajaxModal').modal('show');
            });

            $('body').on('click', '.edit-book', function () {
                var book_id = $(this).data('id');
                $.get("{{ route('books.index') }}" + '/' + book_id + '/edit', function (data) {
                    $('#modelHeading').html("Edit Book");
                    $('#ajaxModal').modal('show');
                    $('#book_id').val(data.id);
                    $('#title').val(data.title);
                    $('#author').val(data.author);
                    $('#description').val(data.description);
                    $('#year').val(data.year);
                    $('#cover').val(data.cover);
                    $('#status').val(data.status);
                })
            });

            $('#bookForm').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);

                var actionUrl = '';
                var method = 'POST';
                if ($('#modelHeading').html() === "Create New Book") {
                    // return alert('Book add.');
                    actionUrl = "{{ route('books.store') }}";
                } else if ($('#modelHeading').html() === "Edit Book") {
                    var bookId = $('#book_id').val();
                    // return alert(bookId);
                    actionUrl = "{{ url('books/update') }}/" + bookId;
                    method = 'POST';
                }

                $.ajax({
                    type: method,
                    url: actionUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        if (data.success) {
                            $('#bookForm').trigger("reset");
                            $('#ajaxModal').modal('hide');
                            table.draw();
                            alert('Book saved successfully.');
                        }
                    },
                    error: function (data) {
                        if (data.status === 422) { // Error validasi dari Laravel
                            var errors = data.responseJSON.errors;
                            var errorMessage = '';
                            $.each(errors, function (key, value) {
                                errorMessage += value + '\n';
                            });
                            alert('Validation Error:\n' + errorMessage);
                        } else {
                            alert('Error:\n' + data.responseJSON.error);
                        }
                    }
                });
            });

            $('body').on('click', '.delete-book', function () {
                var book_id = $(this).data('id');
                if(confirm("Are you sure you want to delete this book?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('books.destroy', '') }}/" + book_id,
                        success: function (data) {
                            table.draw();
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                }
            });
            $('body').on('click', '.view-details', function () {
        var bookId = $(this).data('id');

        $.ajax({
            type: "GET",
            url: "{{ url('books/show') }}/" + bookId,
            success: function (data) {
                if (data.success) {
                    // Set data ke modal
                    $('#book-title').text(data.data.title);
                    $('#book-author').text(data.data.author);
                    $('#book-year').text(data.data.year);
                    $('#book-status').text(data.data.status);
                    $('#book-description').text(data.data.description);
                    $('#book-cover').attr('src', '/covers/' + data.data.cover);

                    // Tampilkan modal
                    $('#bookDetailModal').modal('show');
                } else {
                    alert('Book not found.');
                }
            },
            error: function (data) {
                alert('Error retrieving book details.');
            }
        });
    });
        });
    </script>
</body>
</html>
