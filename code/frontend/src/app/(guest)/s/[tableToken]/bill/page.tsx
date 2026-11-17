"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { useSessionStore } from "@/stores/sessionStore";
import { apiFetch } from "@/lib/api";
import { formatVND } from "@/lib/utils";
import { ArrowLeft, Receipt, CheckCircle, BellRing } from "lucide-react";

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

export default function GuestBillPage({ params }: { params: Promise<{ tableToken: string }> }) {
  const { tableToken } = use(params);
  const { sessionToken, table, restaurant } = useSessionStore();
  const [bill, setBill] = useState<BillData | null>(null);
  const [loading, setLoading] = useState(true);
  const [requested, setRequested] = useState(false);

  useEffect(() => {
    async function loadBill() {
      const res = await apiFetch<BillData>("/s/bill", {
        sessionToken: sessionToken || undefined,
      });
      if (res.success && res.data) {
        setBill(res.data);
      }
      setLoading(false);
    }

    loadBill();
  }, [sessionToken]);

  const handleRequestPayment = async () => {
    const res = await apiFetch("/s/request-payment", {
      method: "POST",
      sessionToken: sessionToken || undefined,
    });
    if (res.success) {
      setRequested(true);
    }
  };

  return (
    <div className="max-w-md mx-auto min-h-screen bg-slate-50 flex flex-col pb-28">
      {/* Header */}
      <header className="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-3">
        <Link href={`/s/${tableToken}`} className="p-1.5 text-slate-600 rounded-full hover:bg-slate-100">
          <ArrowLeft className="w-5 h-5" />
        </Link>
        <h1 className="font-bold text-base text-slate-900">
          Hóa đơn tạm tính {table?.code ? `· Bàn ${table.code}` : ""}
        </h1>
      </header>

      <main className="p-4 flex-1 space-y-4">
        {loading ? (
          <div className="text-center py-20 text-slate-400 text-sm">Đang tải hóa đơn...</div>
        ) : !bill || bill.items.length === 0 ? (
          <div className="text-center py-20 text-slate-400 text-sm">
            Chưa có món ăn nào được ghi nhận cho bàn này.
          </div>
        ) : (
          <div className="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-4">
            <div className="text-center pb-3 border-b border-dashed border-slate-200">
              <h2 className="font-bold text-base text-slate-900">{restaurant?.name || "Quán Bếp Nhỏ"}</h2>
              <p className="text-xs text-slate-500 mt-0.5">Bàn: {table?.code} · Phiên đang mở</p>
            </div>

            {/* Bảng chi tiết món */}
            <div className="space-y-2">
              {bill.items.map((item) => (
                <div key={item.order_item_id} className="flex justify-between text-xs py-1">
                  <div>
                    <span className="font-medium text-slate-800">{item.dish_name}</span>
                    <span className="text-slate-400 ml-1.5">×{item.quantity}</span>
                  </div>
                  <span className="font-semibold text-slate-900">{formatVND(item.subtotal)}</span>
                </div>
              ))}
            </div>

            {/* Các dòng thuế phí */}
            <div className="pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
              <div className="flex justify-between">
                <span>Tiền món:</span>
                <span>{formatVND(bill.subtotal)}</span>
              </div>
              {bill.discount > 0 && (
                <div className="flex justify-between text-emerald-600 font-medium">
                  <span>Giảm giá:</span>
                  <span>-{formatVND(bill.discount)}</span>
                </div>
              )}
              {bill.serviceCharge > 0 && (
                <div className="flex justify-between">
                  <span>Phí phục vụ:</span>
                  <span>{formatVND(bill.serviceCharge)}</span>
                </div>
              )}
              {bill.vat > 0 && (
                <div className="flex justify-between">
                  <span>Thuế VAT:</span>
                  <span>{formatVND(bill.vat)}</span>
                </div>
              )}
              <div className="flex justify-between pt-2 border-t border-slate-200 text-sm font-bold text-slate-900">
                <span>TỔNG CẦN THANH TOÁN:</span>
                <span className="text-orange-600 text-base">{formatVND(bill.total)}</span>
              </div>
            </div>
          </div>
        )}
      </main>

      {/* Nút gọi thanh toán cố định đáy */}
      {bill && bill.items.length > 0 && (
        <div className="fixed bottom-0 inset-x-0 max-w-md mx-auto p-4 bg-white border-t border-slate-200">
          <button
            disabled={requested}
            onClick={handleRequestPayment}
            className={`w-full py-3.5 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg transition-all ${
              requested
                ? "bg-emerald-600 text-white"
                : "bg-orange-600 text-white hover:bg-orange-700 active:scale-[0.98]"
            }`}
          >
            {requested ? (
              <>
                <CheckCircle className="w-5 h-5" />
                Đã gửi yêu cầu · Nhân viên đang đến
              </>
            ) : (
              <>
                <BellRing className="w-5 h-5" />
                YÊU CẦU NHÂN VIÊN THANH TOÁN
              </>
            )}
          </button>
        </div>
      )}
    </div>
  );
}
