<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="BookStore CRUD API",
 *     version="1.0",
 *     description="This is a simple API documentation for demonstration of CRUD operation of Bookstore.",
 *     @OA\Contact(
 *         name="Muhammad Haider",
 *         email="haadi.javaid@gmail.com"
 *     )
 * )
 */


class BookController extends Controller
{
    public function index()
    {
        $books = Book::all();
        return response()->json([
            'success' => true,
            'message' => 'Books retrieved successfully',
            'data' => $books,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/books/add",
     *     operationId="createBook",
     *     tags={"Books"},
     *     summary="Create a new book",
     *     description="Creates a new book record",
     *     security={ {"sanctum": {} }, {"X-XSRF-TOKEN": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"title","description"},
     *                  @OA\Property(property="title", type="string", example="Sample Book"),
     *                  @OA\Property(property="description", type="string", example="A brief description of the book."),
     *              )
     *         ),     
     *     ),
     * 
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     * )
     */

    // API to store a new book
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => [],
            ], 400);
        }

        // Get the current user's ID
        $user_id = auth()->user()->id;

        // Check if the user is authorized to create a book
        if ($this->authorize('create-books')) {
            // Create a new book instance
            $book = new Book([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'author_id' => $user_id,
            ]);

            // Save the book to the database
            $book->save();

            return response()->json([
                "success" => true,
                "message" => "Book added successfully",
                "code" => 200,
                'data' => $book,
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Permission Denied",
                "code" => 403,
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/books/view",
     *     operationId="getBook",
     *     tags={"Books"},
     *     summary="Get a specific book",
     *     description="Retrieves information about a specific book",
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="query",
     *         required=true,
     *         description="ID of the book to retrieve",
     *         @OA\Schema(type="integer", format="int64", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     ),
     * )
     */


    // API to view an existing book
    public function show(Request $request)
    {
        try {
            // Attempt to find the book by its ID; throws an exception if not found
            $book = Book::findOrFail($request->book_id);

            // Return a JSON response with the found book
            return response()->json([
                'success' => true,
                'message' => 'Book retrieved successfully',
                'data' => $book,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the exception if the book is not found and return a 404 response
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'data' => [],
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/books/edit/{book_id}",
     *     operationId="updateBook",
     *     tags={"Books"},
     *     summary="Update a specific book",
     *     description="Updates information about a specific book",
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to update",
     *         @OA\Schema(type="integer", format="int64", example=1),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated book data",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Book Title"),
     *             @OA\Property(property="description", type="string", example="Updated description."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *         @OA\JsonContent(),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (not the book owner)",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     ),
     * )
     */


    // API to update an existing book
    public function update(Request $request, $id)
    {
        // Find the book by its ID
        $book = Book::find($id);

        // If the book is not found, return a 404 response
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'data' => [],
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        // If validation fails, return a 400 response with the first validation error message
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => [],
            ], 400);
        }

        // Ensure that only the author can update the book
        if ($book->author_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => [],
            ], 403);
        }

        // Update the book with the new data
        $book->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        // Return a success response with the updated book data
        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book,
        ], 200);
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/books/delete/{book_id}",
     *     operationId="deleteBook",
     *     tags={"Books"},
     *     summary="Delete a specific book",
     *     description="Deletes a specific book",
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="book_id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to delete",
     *         @OA\Schema(type="integer", format="int64", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (not the book owner)",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     ),
     * )
     */

    // API to delete an existing book
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'data' => [],
            ], 404);
        }

        // Ensure that only the author can delete the book
        if ($book->author_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => [],
            ], 403);
        }

        // Delete the section of that book
        DB::table('sections')->where([
            'book_id' => $id
        ])->delete();
        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book has been deleted successfully',
            'data' => [],
        ], 200);
    }
}
