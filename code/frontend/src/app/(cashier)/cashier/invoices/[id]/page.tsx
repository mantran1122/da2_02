"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { apiFetch } from "@/lib/api";
import { formatVND } from "@/lib/utils";
import { ArrowLeft, Check, Printer, CreditCard, Banknote } from "lucide-react";

interface BillData {
  subtotal: number;
  discount: number;
  serviceCharge: number;
  vat: number;
  total: number;
  items: Array<{
    order_item_id: number;
    dish_name: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
  }>;
}

export default function CashierInvoicePage({ params }: { params: Promise<{ id: string }> }) {
  const { id: sessionId } = use(params);
  const router = useRouter();

  const [bill, setBill] = useState<BillData | null>(null);
  const [discountInput, setDiscountInput] = useState<string>("0");
  const [paymentMethod, setPaymentMethod] = useState<"cash" | "qr_transfer">("cash");
  const [paying, setPaying] = useState(false);
  const [paidSuccess, setPaidSuccess] = useState(false);

  useEffect(() => {
    async function loadInvoiceDraft() {
      const res = await apiFetch<BillData>(`/cashier/sessions/${sessionId}/invoice`);
      if (res.success && res.data) {
        setBill(res.data);
      }
    }
    loadInvoiceDraft();
  }, [sessionId]);

  const handleApplyDiscount = async () => {
    const discountVal = parseInt(discountInput) || 0;
    const res = await apiFetch<BillData>(`/cashier/sessions/${sessionId}/invoice`, {
      method: "POST",
      body: JSON.stringify({ discount: discountVal }),
    });
    if (res.success && res.data) {
      alert("Đã cập nhật lại tổng hóa đơn sau chiết khấu!");
    }
  };

  const handleConfirmPayment = async () => {
    if (!bill) return;
    setPaying(true);

    // 1. Phát hành hóa đơn
    const issueRes = await apiFetch(`/cashier/sessions/${sessionId}/invoice`, {
      method: "POST",
      body: JSON.stringify({ discount: parseInt(discountInput) || 0 }),
    });

    if (issueRes.success && issueRes.data) {
      const invoiceId = issueRes.data.id;

      // 2. Ghi nhận thanh toán
      const payRes = await apiFetch(`/cashier/invoices/${invoiceId}/payments`, {
        method: "POST",
        body: JSON.stringify({
          amount: bill.total,
          payment_method: paymentMethod,
        }),
      });

      setPaying(false);

      if (payRes.success) {
        setPaidSuccess(true);
      } else {
        alert(payRes.message || "Lỗi thanh toán!");
      }
    } else {
      setPaying(false);
      alert(issueRes.message || "Lỗi phát hành hóa đơn!");
    }
  };

  return (
    <div className="min-h-screen bg-slate-100 p-6 flex justify-center">
      <div className="max-w-2xl w-full bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
        <header className="p-4 bg-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link href="/staff/tables" className="p-1.5 text-slate-400 hover:text-white rounded-lg">
              <ArrowLeft className="w-5 h-5" />
            </Link>
            <h1 className="font-bold text-base">THANH TOÁN THU NGÂN (POS)</h1>
          </div>
          <span className="text-xs bg-emerald-600 px-2.5 py-1 rounded-full font-bold">
            Phiên #{sessionId}
          </span>
        </header>

        {paidSuccess ? (
          <div className="p-12 text-center space-y-4 flex-1 flex flex-col items-center justify-center">
            <div className="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center">
              <Check className="w-8 h-8" />
            </div>
            <h2 className="text-xl font-bold text-slate-900">Thanh toán thành công!</h2>
            <p className="text-sm text-slate-500">Phiên ăn đã được đóng và bàn đã chuyển về trạng thái trống.</p>
            <div className="flex gap-3 pt-4">
              <button
                onClick={() => window.print()}
                className="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold flex items-center gap-1.5 hover:bg-slate-50"
              >
                <Printer className="w-4 h-4" />
                In phiếu tính tiền
              </button>
              <Link
                href="/staff/tables"
                className="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800"
              >
                Về sơ đồ bàn
              </Link>
            </div>
          </div>
        ) : !bill ? (
          <div className="p-12 text-center text-slate-400">Đang nạp thông tin...</div>
        ) : (
          <div className="p-6 flex-1 flex flex-col justify-between space-y-6">
            {/* Danh sách món */}
            <div className="space-y-3">
              <h3 className="font-bold text-sm text-slate-900 border-b pb-2">Danh sách món đã dùng:</h3>
              <div className="space-y-1.5 max-h-60 overflow-y-auto">
                {bill.items.map((item) => (
                  <div key={item.order_item_id} className="flex justify-between text-xs py-1">
                    <span className="text-slate-800 font-medium">
                      {item.dish_name} × {item.quantity}
                    </span>
                    <span className="font-bold text-slate-900">{formatVND(item.subtotal)}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Chiết khấu & Hình thức thanh toán */}
            <div className="p-4 bg-slate-50 rounded-xl space-y-3 border border-slate-200">
              <div className="flex items-center justify-between gap-4">
                <span className="text-xs font-semibold text-slate-700">Giảm giá / Voucher (VND):</span>
                <div className="flex gap-2">
                  <input
                    type="number"
                    value={discountInput}
                    onChange={(e) => setDiscountInput(e.target.value)}
                    className="w-32 px-2.5 py-1 text-xs bg-white border border-slate-300 rounded-lg text-right font-mono"
                  />
                  <button
                    onClick={handleApplyDiscount}
                    className="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-xs rounded-lg font-semibold"
                  >
                    Áp dụng
                  </button>
                </div>
              </div>

              <div className="flex items-center justify-between gap-4 pt-2 border-t border-slate-200">
                <span className="text-xs font-semibold text-slate-700">Phương thức:</span>
                <div className="flex gap-2">
                  <button
                    onClick={() => setPaymentMethod("cash")}
                    className={`px-3 py-1.5 text-xs rounded-lg font-semibold flex items-center gap-1.5 ${
                      paymentMethod === "cash"
                        ? "bg-slate-900 text-white"
                        : "bg-white border text-slate-600"
                    }`}
                  >
                    <Banknote className="w-3.5 h-3.5" />
                    Tiền mặt
                  </button>
                  <button
                    onClick={() => setPaymentMethod("qr_transfer")}
                    className={`px-3 py-1.5 text-xs rounded-lg font-semibold flex items-center gap-1.5 ${
                      paymentMethod === "qr_transfer"
                        ? "bg-slate-900 text-white"
                        : "bg-white border text-slate-600"
                    }`}
                  >
                    <CreditCard className="w-3.5 h-3.5" />
                    Chuyển khoản QR
                  </button>
                </div>
              </div>
            </div>

            {/* Khối tổng kết số tiền */}
            <div className="space-y-1.5 text-xs border-t pt-4">
              <div className="flex justify-between text-slate-600">
                <span>Tiền món:</span>
                <span>{formatVND(bill.subtotal)}</span>
              </div>
              <div className="flex justify-between text-slate-600">
                <span>Thuế VAT:</span>
                <span>{formatVND(bill.vat)}</span>
              </div>
              <div className="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t">
                <span>TỔNG TIỀN THU:</span>
                <span className="text-orange-600 text-xl">{formatVND(bill.total)}</span>
              </div>
            </div>

            <button
              disabled={paying}
              onClick={handleConfirmPayment}
              className="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold text-sm hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-lg disabled:opacity-50"
            >
              {paying ? "Đang xử lý..." : `XÁC NHẬN THU ${formatVND(bill.total)} & ĐÓNG BÀN`}
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
