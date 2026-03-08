<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct() {
        $this->authorizeResource(Book::class, 'book');
    }

    public function index(Request $request)
    {
        $books = Book::when($request->has('title'), function ($query) use ($request) {
            $query->where('title', 'like', '%'.$request->input('title').'%');
        })->when($request->has('isbn'), function ($query) use ($request) {
            $query->where('ISBN', 'like', '%'.$request->input('isbn').'%');
        })->when($request->has('is_available'), function ($query) use ($request) {
            $query->where('is_available', $request->boolean('is_available'));
        })
            ->paginate();

        return response()->json(BookResource::collection($books));
    }

    public function show(Book $book)
    {
        $this->authorize('view', $book);

        return response()->json(BookResource::make($book));
    }

    public function store(Request $request)
    {
        $book = Book::create($request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'ISBN' => 'required|string|unique:books',
            'total_copies' => 'required|integer',
            'available_copies' => 'required|integer',
            'is_available' => 'boolean',
        ]));

        return response()->json(BookResource::make($book), 201);
    }

    public function update(Request $request, Book $book)
    {
        $book->update($request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'ISBN' => 'sometimes|string|unique:books,ISBN,'.$book->id,
            'total_copies' => 'sometimes|integer',
            'available_copies' => 'sometimes|integer',
            'is_available' => 'sometimes|boolean',
        ]));

        return response()->json(BookResource::make($book));
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(['message' => 'Book deleted'], 200);
    }
}
