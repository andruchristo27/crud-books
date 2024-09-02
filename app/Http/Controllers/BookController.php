<?php

namespace App\Http\Controllers;

use App\Models\Book;
use DataTables;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Book::latest()->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<button data-id="'.$row->id.'" class="btn btn-sm btn-primary editBtn">Edit</button>';
                    $btn .= ' <button data-id="'.$row->id.'" class="btn btn-sm btn-danger deleteBtn">Delete</button>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('books.index');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
                'author' => 'required|string|max:255',
                'year' => 'required|integer',
                'description' => 'required|string',
                'status' => 'required|in:Published,Not Published',
            ]);

            if ($request->hasFile('cover')) {
                $imageName = time().'.'.$request->cover->extension();
                $request->cover->move(public_path('covers'), $imageName);
                $validatedData['cover'] = $imageName;
            }

            Book::updateOrCreate(
                ['id' => $request->book_id],
                $validatedData
            );

            return response()->json(['success' => 'Book saved successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->getMessageBag()], 422);
        }
    }

    public function update(Request $request, $id)
    {
        // return dd($request);
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'year' => 'required|integer',
            'description' => 'required|string',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
        ]);

        $book = Book::findOrFail($id);

        if ($request->hasFile('cover')) {
            $imagePath = public_path('covers/'.$book->cover);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $imageName = time().'.'.$request->cover->extension();
            $request->cover->move(public_path('covers'), $imageName);
            $validatedData['cover'] = $imageName;
        }

        $book->update($validatedData);

        return response()->json(['success' => 'Book saved successfully.']);
    }

    public function edit($id)
    {
        $book = Book::find($id);

        return response()->json($book);
    }

    public function destroy($id)
    {
        $data = Book::find($id);
        $imagePath = public_path('covers/'.$data->cover);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        $data->delete();

        return response()->json(['success' => 'Book deleted successfully.']);
    }

    public function show($id)
    {
        $book = Book::find($id);
        if ($book) {
            return response()->json(['success' => true, 'data' => $book]);
        } else {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }
    }
}
