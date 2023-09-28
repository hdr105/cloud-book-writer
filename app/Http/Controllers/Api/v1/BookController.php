<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="BookStore CRUD API",
 *     version="1.0",
 *     description="This is a simple API documentation for demonstration of CRUD operation of Bookstore.",
 *     @OA\Contact(
 *         name="Mian Umar",
 *         email="mdumar.bitsclan@gmail.com"
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"title","description","user_id"},
     *                  @OA\Property(property="title", type="string", example="Sample Book"),
     *                  @OA\Property(property="description", type="string", example="A brief description of the book."),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *              )
     *         ),     
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string")),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    
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
                'message' => $validator->errors()->first(),
                'data' => [],
            ], 400);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => [],
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
                'data' => $book,
            ], 201);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/books/view",
     *     operationId="getBook",
     *     tags={"Books"},
     *     summary="Get a specific book",
     *     description="Retrieves information about a specific book",
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
        $id = $request->book_id;

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Book retrieved successfully',
            'data' => $book,
        ]);
    }
    
     /**
     * @OA\Put(
     *     path="/api/v1/books/edit/{book_id}",
     *     operationId="updateBook",
     *     tags={"Books"},
     *     summary="Update a specific book",
     *     description="Updates information about a specific book",
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
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found',
                'data' => [],
            ], 404);
        }

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

        // Ensure that only the author can update the book
        if ($book->author_id !== auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => [],
            ], 403);
        }

        $book->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

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

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book has been deleted successfully',
            'data' => [],
        ], 200);
    }
}
