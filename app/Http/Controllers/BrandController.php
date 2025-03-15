<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('products/brands/index',[
            'brands' => Brand::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands'
        ]);

        $validated['slug'] = str()->slug($validated['name']);

        Brand::create($validated);

        return back()->with('message', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return response()->json([
            'brand' => $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id
        ]);

        $validated['slug'] = str()->slug($validated['name']);

        $brand->update($validated);

        return back()->with('message', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return back()->with('message', 'Brand deleted successfully.');
    }
}
