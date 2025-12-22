<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Pagination
        $perPage = $request->get('limit', 10);
        $products = $query->with('creator')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is admin (via profile role or spatie role)
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');
        if ($user->profile?->role !== 'admin' && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'code' => 'FORBIDDEN',
                    'details' => 'Only admins can create products'
                ]
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'capacity' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'specifications' => 'nullable|array',
            'image_urls' => 'nullable|array',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $data = $request->only(['name', 'price', 'capacity', 'category', 'specifications', 'image_urls', 'is_active']);
        $data['created_by'] = Auth::id();

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('uploads/products', $imageName, 'public');
            // image_urls is JSONB array in database
            $data['image_urls'] = [Storage::url($imagePath)];
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('creator')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Product with the specified ID does not exist'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check if user is admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');
        if ($user->profile?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'code' => 'FORBIDDEN',
                    'details' => 'Only admins can update products'
                ]
            ], 403);
        }
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Product with the specified ID does not exist'
                ]
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'capacity' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'specifications' => 'nullable|array',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $data = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('uploads/products', $imageName, 'public');
            $data['image_urls'] = [Storage::url($imagePath)];
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check if user is admin
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');
        if ($user->profile?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'error' => [
                    'code' => 'FORBIDDEN',
                    'details' => 'Only admins can delete products'
                ]
            ], 403);
        }
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Product with the specified ID does not exist'
                ]
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
