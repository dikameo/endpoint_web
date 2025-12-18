<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = UserAddress::where('user_id', auth()->id())->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Addresses retrieved successfully',
            'data' => $addresses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alamat' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
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

        $address = UserAddress::create([
            'user_id' => auth()->id(),
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => $address
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $address = UserAddress::where('user_id', auth()->id())->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Address with the specified ID does not exist or does not belong to you'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Address retrieved successfully',
            'data' => $address
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $address = UserAddress::where('user_id', auth()->id())->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Address with the specified ID does not exist or does not belong to you'
                ]
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'alamat' => 'sometimes|required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
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

        $address->update($request->only(['alamat', 'latitude', 'longitude', 'accuracy']));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $address = UserAddress::where('user_id', auth()->id())->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Address with the specified ID does not exist or does not belong to you'
                ]
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }
}
