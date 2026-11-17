<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Events\PaymentRequested;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BillController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Xem bảng kê hóa đơn tạm tính của phiên
     */
    public function preview(Request $request): JsonResponse
    {
        $session = $request->get('current_session');
        $billData = $this->invoiceService->calculateForSession($session);

        return response()->json([
            'success' => true,
            'data' => $billData
        ]);
    }

    /**
     * Khách bấm yêu cầu thanh toán -> Phát sóng sự kiện tới quầy Thu ngân
     */
    public function requestPayment(Request $request): JsonResponse
    {
        $session = $request->get('current_session');
        broadcast(new PaymentRequested($session))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi yêu cầu thanh toán tới nhân viên thu ngân.'
        ]);
    }

    /**
     * Tải file PDF hóa đơn sau khi đã thanh toán
     */
    public function downloadPdf(Request $request)
    {
        $session = $request->get('current_session');
        $invoice = $session->invoice;

        if (! $invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa có hóa đơn nào được phát hành cho phiên này.'
            ], 404);
        }

        $restaurant = \App\Models\Restaurant::first();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('invoice', 'restaurant'));
            return $pdf->download("bill-{$invoice->invoice_code}.pdf");
        }

        return view('pdf.invoice', compact('invoice', 'restaurant'));
    }
}
