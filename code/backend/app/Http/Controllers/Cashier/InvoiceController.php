<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\DiningSession;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Restaurant;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Xem hóa đơn nháp trước khi phát hành
     */
    public function preview(int $sessionId): JsonResponse
    {
        $session = DiningSession::findOrFail($sessionId);
        $data = $this->invoiceService->calculateForSession($session);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Phát hành hóa đơn chính thức
     */
    public function issue(Request $request, int $sessionId): JsonResponse
    {
        $session = DiningSession::findOrFail($sessionId);
        $discount = (int) $request->input('discount', 0);
        $discountNote = $request->input('discount_note');

        $calc = $this->invoiceService->calculateForSession($session, $discount, $discountNote);

        $invoice = DB::transaction(function () use ($session, $calc, $request) {
            $inv = Invoice::updateOrCreate(
                ['session_id' => $session->id],
                [
                    'invoice_code' => 'INV-' . date('Ymd') . '-' . Str::upper(Str::random(5)),
                    'subtotal' => $calc['subtotal'],
                    'discount_amount' => $calc['discount'],
                    'discount_note' => $calc['discount_note'],
                    'service_charge' => $calc['serviceCharge'],
                    'vat_amount' => $calc['vat'],
                    'total_amount' => $calc['total'],
                    'status' => 'draft',
                    'issued_by_staff_id' => $request->user()?->id,
                ]
            );

            // Xóa items cũ và chốt lại items mới
            InvoiceItem::where('invoice_id', $inv->id)->delete();
            foreach ($calc['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'order_item_id' => $item['order_item_id'],
                    'dish_name' => $item['dish_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $inv;
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã phát hành hóa đơn thành công.',
            'data' => $invoice->load('items')
        ], 201);
    }

    /**
     * Áp dụng giảm giá vào hóa đơn
     */
    public function applyDiscount(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $discount = (int) $request->input('discount', 0);
        $discountNote = $request->input('discount_note');

        $session = $invoice->session;
        $calc = $this->invoiceService->calculateForSession($session, $discount, $discountNote);

        $invoice->update([
            'discount_amount' => $calc['discount'],
            'discount_note' => $calc['discount_note'],
            'service_charge' => $calc['serviceCharge'],
            'vat_amount' => $calc['vat'],
            'total_amount' => $calc['total'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã áp dụng giảm giá thành công.',
            'data' => $invoice
        ]);
    }

    /**
     * Xuất hóa đơn định dạng PDF (hoặc view HTML nếu chưa cài extension PDF)
     */
    public function pdf(int $id)
    {
        $invoice = Invoice::with(['items', 'session.table', 'staff'])->findOrFail($id);
        $restaurant = Restaurant::first();

        // Kiểm tra xem class DomPDF có khả dụng không
        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'restaurant'));
            return $pdf->download("invoice-{$invoice->invoice_code}.pdf");
        }

        // Fallback trả về HTML để trình duyệt in trực tiếp
        return view('pdf.invoice', compact('invoice', 'restaurant'));
    }

    /**
     * Hủy hóa đơn (chỉ role admin)
     */
    public function cancel(int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy hóa đơn thành công.'
        ]);
    }
}
