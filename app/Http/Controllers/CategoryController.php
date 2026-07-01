<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getCategory = Category::all();
        if ($getCategory->isEmpty()) {
            return response()->json(['message' => 'No category found'], 404);
        }
        return response()->json($getCategory);
    }    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->description = $request->description;

        $category->save();

        return response()->json(['message' => 'Category Created Sucessfully'], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
         return response()->json($category);
    }    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->description = $request->description;

        $category->save();

        return response()->json(['message' => 'category update sucessfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'category deleted sucessfully'], 200);
    }
}
