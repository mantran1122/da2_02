"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { useSessionStore } from "@/stores/sessionStore";
import { apiFetch } from "@/lib/api";
import { getEcho } from "@/lib/echo";
import { formatVND } from "@/lib/utils";
import { ArrowLeft, Clock, CheckCircle2, Flame, Bell, XCircle, RotateCcw } from "lucide-react";

interface OrderItem {
  id: number;
  dish: {
    name: string;
  };
  quantity: number;
  unit_price: number;
  note?: string;
  status: "pending" | "cooking" | "ready" | "served" | "cancelled";
  created_at: string;
}

export default function GuestOrdersPage({ params }: { params: Promise<{ tableToken: string }> }) {
  const { tableToken } = use(params);
  const { sessionToken, table } = useSessionStore();
  const [items, setItems] = useState<OrderItem[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchItems = async () => {
    const res = await apiFetch<OrderItem[]>("/s/items", {
      sessionToken: sessionToken || undefined,
    });
    if (res.success && res.data) {
      setItems(res.data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchItems();

    // Lắng nghe sự kiện WebSocket cập nhật trạng thái món
    if (sessionToken) {
      const echo = getEcho();
      if (echo) {
        const channel = echo.channel(`table-session.${sessionToken}`);

        channel.listen(".ItemStatusChanged", (event: { item: OrderItem }) => {
          setItems((prev) =>
            prev.map((i) => (i.id === event.item.id ? { ...i, status: event.item.status } : i))
          );
        });

        channel.listen(".ItemCancelled", (event: { item: OrderItem }) => {
          setItems((prev) =>
            prev.map((i) => (i.id === event.item.id ? { ...i, status: "cancelled" } : i))
          );
        });

        return () => {
          echo.leaveChannel(`table-session.${sessionToken}`);
        };
      }
    }
  }, [sessionToken]);

  const getStatusBadge = (status: OrderItem["status"]) => {
    switch (status) {
      case "pending":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
            <Clock className="w-3.5 h-3.5 text-slate-500" />
            Đã gửi bếp
          </span>
        );
      case "cooking":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
            <Flame className="w-3.5 h-3.5 text-orange-600 animate-pulse" />
            Đang nấu
          </span>
        );
      case "ready":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
            <Bell className="w-3.5 h-3.5 text-blue-600" />
            Sắp ra bàn
          </span>
        );
      case "served":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
            <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
            Đã ra bàn
          </span>
        );
      case "cancelled":
        return (
          <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 line-through">
            <XCircle className="w-3.5 h-3.5 text-red-500" />
            Đã hủy
          </span>
        );
    }
  };

  return (
    <div className="max-w-md mx-auto min-h-screen bg-slate-50 flex flex-col pb-24">
      {/* Header */}
      <header className="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between shadow-sm">
        <div className="flex items-center gap-3">
          <Link href={`/s/${tableToken}`} className="p-1.5 text-slate-600 rounded-full hover:bg-slate-100">
            <ArrowLeft className="w-5 h-5" />
          </Link>
          <div>
            <h1 className="font-bold text-base text-slate-900">
              Tiến độ món ăn {table?.code ? `· Bàn ${table.code}` : ""}
            </h1>
            <p className="text-[11px] text-emerald-600 font-semibold">● Đang cập nhật Realtime</p>
          </div>
        </div>

        <button
          onClick={fetchItems}
          className="p-1.5 text-slate-500 hover:text-slate-800 rounded-full hover:bg-slate-100"
          title="Tải lại"
        >
          <RotateCcw className="w-4 h-4" />
        </button>
      </header>

      {/* Danh sách món */}
      <main className="p-4 flex-1 space-y-3">
        {loading ? (
          <div className="text-center py-20 text-slate-400 text-sm">Đang nạp trạng thái món...</div>
        ) : items.length === 0 ? (
          <div className="text-center py-20 text-slate-400 text-sm">
            Bàn của bạn chưa gọi món nào.
          </div>
        ) : (
          items.map((item) => (
            <div
              key={item.id}
              className={`p-3.5 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between ${
                item.status === "cancelled" ? "opacity-60" : ""
              }`}
            >
              <div>
                <div className="flex items-center gap-2">
                  <span className="font-bold text-sm text-slate-900">{item.dish?.name}</span>
                  <span className="text-xs font-bold text-orange-600">×{item.quantity}</span>
                </div>
                {item.note && <p className="text-xs text-amber-700 mt-0.5">Ghi chú: {item.note}</p>}
                <p className="text-xs text-slate-400 mt-1">
                  Đơn giá: {formatVND(item.unit_price)}
                </p>
              </div>

              <div>{getStatusBadge(item.status)}</div>
            </div>
          ))
        )}
      </main>

      {/* Nút gọi thêm món & xem hóa đơn */}
      <div className="fixed bottom-0 inset-x-0 max-w-md mx-auto p-4 bg-white border-t border-slate-200 flex gap-2">
        <Link
          href={`/s/${tableToken}`}
          className="flex-1 py-3 text-center bg-slate-100 text-slate-800 font-semibold text-xs rounded-xl hover:bg-slate-200"
        >
          + Gọi thêm món
        </Link>
        <Link
          href={`/s/${tableToken}/bill`}
          className="flex-1 py-3 text-center bg-orange-600 text-white font-semibold text-xs rounded-xl hover:bg-orange-700 shadow"
        >
          Xem hóa đơn & Tính tiền ➔
        </Link>
      </div>
    </div>
  );
}
