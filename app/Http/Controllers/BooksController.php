<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Models\Books;
use App\Http\Requests\StoreBooksRequest;
use App\Http\Requests\UpdateBooksRequest;
use App\Interfaces\BookRepositoryInterface;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\DB;

class BooksController extends Controller
{

    private BookRepositoryInterface $bookRepository;

    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->bookRepository->index();
        return ApiResponseClass::sendResponse(BookResource::collection($data), "Books retrieved successfully", 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBooksRequest $request)
    {
        // Upload Cover Image
        $coverImage = $request->file('cover_image');
        $coverImageName = time() . '.' . $coverImage->extension();
        $coverImage->move(public_path('images'), $coverImageName);
        $imagePath = 'images/' . $coverImageName;

        $data = [
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'cover_image' => $imagePath,
            'description' => $request->description,
            'pages' => $request->pages,
            'publisher' => $request->publisher,
            'published_at' => $request->published_at,
        ];

        DB::beginTransaction();
        try {
            $this->bookRepository->store($data);

            DB::commit();
            return ApiResponseClass::sendResponse($data, "Book created successfully", 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponseClass::rollback($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $book = $this->bookRepository->getById($id);
            return ApiResponseClass::sendResponse(new BookResource($book), "Book retrieved successfully", 200);
        } catch (\Exception $e) {
            return ApiResponseClass::throw($e, "Book not found", 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBooksRequest $request, $id)
    {
        $imagePath = '';
        // If $request->cover_image is not null, upload new cover image
        if ($request->cover_image) {
            $coverImage = $request->file('cover_image');
            $coverImageName = time() . '.' . $coverImage->extension();
            $coverImage->move(public_path('images'), $coverImageName);
            $imagePath = 'images/' . $coverImageName;
        }

        if ($imagePath == '') {
            $book = Books::find($id);
            $imagePath = $book->cover_image;
        }

        $data = [
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'cover_image' => $imagePath,
            'description' => $request->description,
            'pages' => $request->pages,
            'publisher' => $request->publisher,
            'published_at' => $request->published_at,
        ];

        DB::beginTransaction();
        try {
            $this->bookRepository->update($data, $id);

            DB::commit();
            return ApiResponseClass::sendResponse('', 'Book updated successfully', 201);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->bookRepository->destroy($id);
            return ApiResponseClass::sendResponse(null, 'Book deleted successfully', 204);
        } catch (\Exception $e) {
            return ApiResponseClass::throw($e, "Book not found", 404);
        }
    }
}
