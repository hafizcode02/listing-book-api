<?php

namespace App\Repositories;

use App\Interfaces\BookRepositoryInterface;
use App\Models\Books;

class BookRepository implements BookRepositoryInterface
{
    public function index()
    {
        return Books::all();
    }

    public function getById($id)
    {
        return Books::findOrFail($id);
    }

    public function store(array $data)
    {
        return Books::create($data);
    }

    public function update(array $data, $id)
    {
        return Books::find($id)->update($data);
    }

    public function destroy($id)
    {
        $findBook = Books::findOrFail($id);

        // remove cover image
        $coverImagePath = public_path($findBook->cover_image);
        if (file_exists($coverImagePath)) {
            unlink($coverImagePath);
        }

        return $findBook->delete();
    }
}
