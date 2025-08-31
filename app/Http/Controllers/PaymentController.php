<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['user:id,name,email']);

        // Role-based filtering
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            // Regular users can only see their own payments
            $query->where('user_id', $request->user()->id);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('payment_date', [$request->start_date, $request->end_date]);
        }

        // Search by transaction ID
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('transaction_id', 'ILIKE', "%{$search}%");
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'payments' => $payments
            ]
        ]);
    }

    /**
     * Store a newly created payment.
     */
    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $payment = Payment::create([
                'user_id' => $request->user_id,
                'transaction_id' => 'TXN_' . Str::upper(Str::random(12)),
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'PKR',
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'reference_id' => $request->reference_id,
                'status' => 'pending',
                'payment_gateway_response' => $request->payment_gateway_response,
            ]);

            $payment->load(['user:id,name,email']);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment created successfully',
                'data' => $payment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment): JsonResponse
    {
        // Check if user can view this payment
        if (!in_array(request()->user()->user_role, ['admin', 'owner']) &&
            $payment->user_id !== request()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $payment->load(['user:id,name,email']);

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    /**
     * Update the specified payment.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        // Only admins can update payments
        if ($request->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can update payments'
            ], 403);
        }

        try {
            $payment->update($request->only([
                'status',
                'failure_reason',
                'refund_amount',
                'refund_date',
                'payment_date',
                'payment_gateway_response'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(Payment $payment): JsonResponse
    {
        // Only admins can delete payments
        if (request()->user()->user_role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only admins can delete payments'
            ], 403);
        }

        try {
            $payment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = Payment::query();

        // Role-based filtering
        if (!in_array($request->user()->user_role, ['admin', 'owner'])) {
            $query->where('user_id', $request->user()->id);
        }

        $stats = [
            'total_payments' => (clone $query)->count(),
            'total_amount' => (clone $query)->sum('amount'),
            'completed_payments' => (clone $query)->where('status', 'completed')->count(),
            'completed_amount' => (clone $query)->where('status', 'completed')->sum('amount'),
            'pending_payments' => (clone $query)->where('status', 'pending')->count(),
            'failed_payments' => (clone $query)->where('status', 'failed')->count(),
            'refunded_amount' => (clone $query)->where('status', 'refunded')->sum('refund_amount'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
