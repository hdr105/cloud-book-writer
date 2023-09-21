<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index() {
        if($this->authorize('view-books')) {
            $books = Book::paginate(5);
            return view('books.index', compact('books'));
        } else {
            return view('errors.no-access');
        }
    }

    public function create() {
        if($this->authorize('create-books')) {
            return view('books.create');
        } else {
            return view('errors.no-access');
        }
    }

    public function store(Request $request) {
        if($this->authorize('store-books')) {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
        
            $book = new Book();
            $book->title = $request->input('title');
            $book->description = $request->input('description');
            $book->author_id = Auth::user()->id;
            $book->save();
        
            return redirect()->route('books.index')->with('success', 'Book created successfully!');
        } else {
            return view('errors.no-access');
        }
    }

    public function edit(Book $book) {
        if($this->authorize('edit-books')) {
            return view('books.edit', compact('book'));
        } else {
            return view('errors.no-access');
        }
    }

    public function update(Request $request, Book $book) {
        if($this->authorize('update-books')) {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
        
            $book->title = $request->input('title');
            $book->description = $request->input('description');
            
            $book->save();
        
            return redirect()->route('books.index')->with('success', 'Book updated successfully!');
        } else {
            return view('errors.no-access');
        }
    }

    public function show(Book $book) {
        if($this->authorize('show-books')) {
            return view('books.show', compact('book'));
        } else {
            return view('errors.no-access');
        }
    }

    public function destroy(Book $book) {
        if($this->authorize('delete-books')) {
            $book->delete();

            return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
        } else {
            return view('errors.no-access');
        }
    }

    public function sections(Book $book) {
        if($this->authorize('view-sections')) {
            $sections = Section::where('book_id' , $book->id)->where('parent_id' , null)->get();
            $parent_id = $book->id;
            return view('books.sections', compact('book', 'sections', 'parent_id'));
        } else {
            return view('errors.no-access');
        }
    }

    public function subSections(Book $book , Section $section) {
        if($this->authorize('view-sections')) {
            $subsections = Section::where('book_id' , $book->id)->where('parent_id' , $section->id)->get();
            $parent_id = $section->id;
            return view('books.subSections', compact('book', 'subsections', 'parent_id'));
        } else {
            return view('errors.no-access');
        }
    }
}
