<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');

        if ($user->profile?->role === 'admin' || $user->hasRole('admin')) {
            $query = Order::query();
        } else {
            $query = Order::where('user_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pagination
        $perPage = $request->get('limit', 10);
        $orders = $query->with('user')->latest('order_date')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ]);
    }

    /**
     * Display all orders (Admin only)
     */
    public function indexAll(Request $request)
    {
        $query = Order::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Pagination
        $perPage = $request->get('limit', 10);
        $orders = $query->with('user')->latest('order_date')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'All orders retrieved successfully',
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
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

        // Generate unique Order ID (CRITICAL: id is text, not auto increment!)
        $orderId = IdGenerator::generateOrderId();

        // Configure Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized', true);
        Config::$is3ds = config('midtrans.is_3ds', true);

        $orderData = [
            'id' => $orderId,              // REQUIRED: Manual ID for text PK
            'user_id' => (string) Auth::id(),     // UUID from auth.users
            'items' => $request->items,    // Will be cast to JSON
            'status' => 'pendingPayment',  // Match CHECK constraint
            'order_date' => now(),
            'subtotal' => $request->subtotal,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'total' => $request->total,
            'shipping_address' => $request->shipping_address,
            'payment_method' => $request->payment_method,
            'tracking_number' => IdGenerator::generateTrackingNumber(),
        ];

        // Generate Snap Token if not COD
        if ($request->payment_method !== 'cod' && $request->payment_method !== 'cash_on_delivery') {
            try {
                /** @var \App\Models\User $authUser */
                $authUser = Auth::user();
                
                $params = [
                    'transaction_details' => [
                        'order_id' => $orderId, // Use our generated Order ID
                        'gross_amount' => (int) $request->total,
                    ],
                    'customer_details' => [
                        'first_name' => $authUser->name ?? 'Customer',
                        'email' => $authUser->email,
                        'phone' => $authUser->phone ?? '',
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);
                // Note: snap_token column doesn't exist in schema
                // Remove this if column not added to Supabase
                // $orderData['snap_token'] = $snapToken;
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error',
                    'error' => [
                        'code' => 'PAYMENT_ERROR',
                        'details' => $e->getMessage()
                    ]
                ], 500);
            }
        }

        $order = Order::create($orderData);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');
        
        if ($user->profile?->role === 'admin' || $user->hasRole('admin')) {
            $order = Order::with('user')->find($id);
        } else {
            $order = Order::where('user_id', $user->id)->find($id);
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Order with the specified ID does not exist or does not belong to you'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');

        if ($user->profile?->role === 'admin' || $user->hasRole('admin')) {
            $order = Order::find($id);
        } else {
            $order = Order::where('user_id', $user->id)->find($id);
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Order with the specified ID does not exist or does not belong to you'
                ]
            ], 404);
        }

        // Admin can update status and tracking
        if ($user->profile?->role === 'admin' || $user->hasRole('admin')) {
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|string',
                'tracking_number' => 'sometimes|string',
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

            $order->update($request->only(['status', 'tracking_number']));
        } else {
            // User can only update if pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update order',
                    'error' => [
                        'code' => 'INVALID_STATUS',
                        'details' => 'Only pending orders can be updated'
                    ]
                ], 400);
            }
            // User updates items, address, payment method... (handled below)
        }

        $validator = Validator::make($request->all(), [
            'items' => 'sometimes|required|array',
            'shipping_address' => 'sometimes|required|string',
            'payment_method' => 'sometimes|required|string',
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

        $order->update($request->only(['items', 'shipping_address', 'payment_method']));

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('profile');

        if ($user->profile?->role === 'admin' || $user->hasRole('admin')) {
            $order = Order::find($id);
        } else {
            $order = Order::where('user_id', $user->id)->find($id);
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'details' => 'Order with the specified ID does not exist or does not belong to you'
                ]
            ], 404);
        }

        // Only allow deleting if order is still pending (unless admin)
        if (!($user->profile?->role === 'admin' || $user->hasRole('admin')) && $order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete order',
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'details' => 'Only pending orders can be deleted'
                ]
            ], 400);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }
}
