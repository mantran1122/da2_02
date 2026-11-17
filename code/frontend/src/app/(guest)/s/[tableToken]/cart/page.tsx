"use client";

import { useState, use } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useCartStore } from "@/stores/cartStore";
import { useSessionStore } from "@/stores/sessionStore";
import { apiFetch } from "@/lib/api";
import { formatVND } from "@/lib/utils";
import { ArrowLeft, Trash2, Plus, Minus, Send, AlertCircle } from "lucide-react";

export default function CartPage({ params }: { params: Promise<{ tableToken: string }> }) {
  const { tableToken } = use(params);
  const router = useRouter();
  const { items, updateQuantity, updateNote, clearCart, getTotalAmount } = useCartStore();
  const { sessionToken, table } = useSessionStore();

  const [generalNote, setGeneralNote] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const totalAmount = getTotalAmount();

  const handleSendOrder = async () => {
    if (items.length === 0) return;
    setSubmitting(true);
    setErrorMsg(null);

    const payload = {
      items: items.map((i) => ({
        dish_id: i.dish_id,
        quantity: i.quantity,
        note: i.note || null,
      })),
      note: generalNote || null,
    };

    const res = await apiFetch("/s/orders", {
      method: "POST",
      body: JSON.stringify(payload),
      sessionToken: sessionToken || undefined,
    });

    setSubmitting(false);

    if (res.success) {
      clearCart();
      router.push(`/s/${tableToken}/orders`);
    } else {
      setErrorMsg(res.message || "Gửi đơn hàng thất bại. Vui lòng thử lại.");
    }
  };

  return (
    <div className="max-w-md mx-auto min-h-screen bg-slate-50 flex flex-col pb-32">
      {/* Header */}
      <header className="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <Link href={`/s/${tableToken}`} className="p-1.5 text-slate-600 rounded-full hover:bg-slate-100">
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <h1 className="font-bold text-base text-slate-900">
            Giỏ hàng của bạn {table?.code ? `(Bàn ${table.code})` : ""}
          </h1>
        </div>

        {items.length > 0 && (
          <button
            onClick={() => clearCart()}
            className="text-xs text-red-600 font-medium hover:underline flex items-center gap-1"
          >
            <Trash2 className="w-3.5 h-3.5" />
            Xóa hết
          </button>
        )}
      </header>

      {/* Thông báo lỗi nếu có */}
      {errorMsg && (
        <div className="m-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2 text-xs text-red-800">
          <AlertCircle className="w-4 h-4 shrink-0 text-red-600" />
          <span>{errorMsg}</span>
        </div>
      )}

      {/* Danh sách món trong giỏ */}
      <main className="p-4 flex-1 space-y-3">
        {items.length === 0 ? (
          <div className="text-center py-24 space-y-3">
            <div className="text-4xl">🛒</div>
            <p className="text-slate-600 text-sm">Giỏ hàng của bạn đang trống.</p>
            <Link
              href={`/s/${tableToken}`}
              className="inline-block px-5 py-2.5 bg-orange-600 text-white font-medium text-xs rounded-xl shadow-sm hover:bg-orange-700"
            >
              Xem thực đơn gọi món ngay
            </Link>
          </div>
        ) : (
          <>
            {items.map((item) => (
              <div key={item.dish_id} className="p-3.5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-2">
                <div className="flex items-start justify-between">
                  <div>
                    <h3 className="font-semibold text-sm text-slate-900">{item.name}</h3>
                    <p className="text-xs font-bold text-orange-600 mt-0.5">
                      {formatVND(item.price)}
                    </p>
                  </div>

                  {/* Nút tăng giảm */}
                  <div className="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
                    <button
                      onClick={() => updateQuantity(item.dish_id, item.quantity - 1)}
                      className="w-6 h-6 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50"
                    >
                      <Minus className="w-3 h-3" />
                    </button>
                    <span className="w-5 text-center text-xs font-bold">{item.quantity}</span>
                    <button
                      onClick={() => updateQuantity(item.dish_id, item.quantity + 1)}
                      className="w-6 h-6 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50"
                    >
                      <Plus className="w-3 h-3" />
                    </button>
                  </div>
                </div>

                {/* Ô nhập ghi chú riêng cho món */}
                <input
                  type="text"
                  value={item.note || ""}
                  onChange={(e) => updateNote(item.dish_id, e.target.value)}
                  placeholder="Ghi chú món này (VD: ít đá, không hành...)"
                  className="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-orange-500"
                />
              </div>
            ))}

            {/* Ghi chú chung toàn đơn */}
            <div className="p-3.5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-1.5">
              <label className="text-xs font-semibold text-slate-700">Ghi chú chung cho toàn bàn:</label>
              <textarea
                value={generalNote}
                onChange={(e) => setGeneralNote(e.target.value)}
                placeholder="Ví dụ: Xin trước chén đũa, lên món cùng lúc..."
                rows={2}
                className="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-orange-500 resize-none"
              />
            </div>
          </>
        )}
      </main>

      {/* Thanh footer gửi đơn */}
      {items.length > 0 && (
        <div className="fixed bottom-0 inset-x-0 max-w-md mx-auto p-4 bg-white border-t border-slate-200 shadow-xl space-y-3">
          <div className="flex items-center justify-between text-sm">
            <span className="text-slate-600">Tổng tiền tạm tính ({items.length} món):</span>
            <span className="font-bold text-base text-slate-900">{formatVND(totalAmount)}</span>
          </div>

          <button
            disabled={submitting}
            onClick={handleSendOrder}
            className="w-full py-3.5 bg-orange-600 text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 hover:bg-orange-700 active:scale-[0.98] shadow-lg transition-all disabled:opacity-50"
          >
            <Send className="w-4 h-4" />
            {submitting ? "Đang gửi xuống bếp..." : "XÁC NHẬN GỬI MÓN XUỐNG BẾP"}
          </button>
        </div>
      )}
    </div>
  );
}
