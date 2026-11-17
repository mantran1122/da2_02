<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Ghi nhận thanh toán hóa đơn: nếu đủ tiền -> đổi status invoice = paid, đóng phiên ăn
     */
    public function store(Request $request, int $invoiceId): JsonResponse
    {
        $invoice = Invoice::with('payments')->findOrFail($invoiceId);

        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qr_transfer,card',
            'transaction_ref' => 'nullable|string|max:100',
        ]);

        $payment = DB::transaction(function () use ($invoice, $validated, $request) {
            $p = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_ref' => $validated['transaction_ref'] ?? null,
                'status' => 'success',
                'received_by_staff_id' => $request->user()?->id,
            ]);

            $totalPaid = $invoice->payments()->sum('amount') + $validated['amount'];

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
                // Đóng phiên ăn và giải phóng bàn
                $invoice->session()->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);
            }

            return $p;
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã ghi nhận thanh toán thành công.',
            'data' => $payment
        ], 201);
    }
}
