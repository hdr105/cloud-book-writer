<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();
        return response()->json(['data' => $books]);
    }

    // API to store a new book
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 400);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => null,
                'data' => null,
            ], 400);
        } else {
            $book = new Book([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'author_id' => $request->user_id,
            ]);

            $book->save();

            return response()->json([
                'success' => true,
                'message' => 'Book created successfully',
                'errors' => null,
                'data' => $book,
            ], 201);
        }
    }

    // API to view an existing book
    public function show(Request $request)
    {
        $id = $request->book_id;

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'errors' => null,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Book retrieved successfully',
            'errors' => null,
            'data' => $book,
        ]);
    }


    // API to update an existing book
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'errors' => null,
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'data' => null,
            ], 400);
        }

        // Ensure that only the author can update the book
        if ($book->author_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => null,
                'data' => null,
            ], 403);
        }

        $book->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'errors' => null,
            'data' => $book,
        ], 200);
    }


    // API to delete an existing book
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'errors' => null,
                'data' => null,
            ], 404);
        }

        // Ensure that only the author can delete the book
        if ($book->author_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => null,
                'data' => null,
            ], 403);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book has been deleted successfully',
            'errors' => null,
            'data' => null,
        ], 200);
    }
}
