"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { getEcho } from "@/lib/echo";
import ConnectionBanner from "@/components/common/ConnectionBanner";
import { Clock, ChefHat, Volume2, VolumeX, AlertCircle, CheckCircle2, RotateCcw } from "lucide-react";

interface KdsItem {
  id: number;
  dish: {
    name: string;
    estimated_prep_time: number;
  };
  quantity: number;
  note?: string;
  status: "pending" | "cooking";
  created_at: string;
  session: {
    table: {
      code: string;
    };
  };
}

export default function KitchenKdsPage() {
  const [items, setItems] = useState<KdsItem[]>([]);
  const [soundEnabled, setSoundEnabled] = useState(true);
  const [currentTime, setCurrentTime] = useState("");

  const fetchQueue = async () => {
    const res = await apiFetch<KdsItem[]>("/kitchen/queue");
    if (res.success && res.data) {
      setItems(res.data);
    }
  };

  useEffect(() => {
    fetchQueue();

    // Cập nhật đồng hồ mỗi giây
    const timer = setInterval(() => {
      setCurrentTime(new Date().toLocaleTimeString("vi-VN"));
    }, 1000);

    // Lắng nghe WebSocket channel 'kitchen'
    const echo = getEcho();
    if (echo) {
      const channel = echo.channel("kitchen");

      channel.listen(".OrderCreated", () => {
        fetchQueue();
        if (soundEnabled && typeof window !== "undefined") {
          // Âm thanh chuông báo order mới
          try {
            const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
            const osc = ctx.createOscillator();
            osc.type = "sine";
            osc.frequency.setValueAtTime(880, ctx.currentTime); // 880Hz (A5)
            osc.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
          } catch (e) {
            console.error("Audio error", e);
          }
        }
      });

      channel.listen(".ItemCancelled", () => {
        fetchQueue();
      });

      return () => {
        echo.leaveChannel("kitchen");
        clearInterval(timer);
      };
    }
  }, [soundEnabled]);

  const handleUpdateStatus = async (itemId: number, nextStatus: "cooking" | "ready") => {
    const res = await apiFetch(`/kitchen/items/${itemId}/status`, {
      method: "PATCH",
      body: JSON.stringify({ status: nextStatus }),
    });

    if (res.success) {
      if (nextStatus === "ready") {
        // Món đã hoàn thành, bỏ khỏi hàng đợi
        setItems((prev) => prev.filter((i) => i.id !== itemId));
      } else {
        setItems((prev) =>
          prev.map((i) => (i.id === itemId ? { ...i, status: nextStatus } : i))
        );
      }
    }
  };

  const getWaitMinutes = (createdAt: string) => {
    const diffMs = Date.now() - new Date(createdAt).getTime();
    return Math.max(0, Math.floor(diffMs / 60000));
  };

  const getBorderColor = (minutes: number) => {
    if (minutes < 5) return "border-l-4 border-l-emerald-500 bg-emerald-50/10";
    if (minutes <= 10) return "border-l-4 border-l-amber-500 bg-amber-50/10";
    return "border-l-4 border-l-red-500 bg-red-50/20 animate-pulse";
  };

  return (
    <div className="min-h-screen bg-slate-900 text-white flex flex-col">
      <ConnectionBanner onReconnect={fetchQueue} />

      {/* 1. Header KDS cho tablet */}
      <header className="bg-slate-800 border-b border-slate-700 px-6 py-4 flex items-center justify-between">
        <div className="flex items-center gap-4">
          <div className="p-2.5 bg-orange-600 text-white rounded-xl">
            <ChefHat className="w-6 h-6" />
          </div>
          <div>
            <h1 className="font-bold text-lg text-white">MÀN HÌNH BẾP (KDS)</h1>
            <p className="text-xs text-slate-400 font-mono">Đồng hồ: {currentTime || "--:--:--"}</p>
          </div>
        </div>

        {/* Thống kê nhanh */}
        <div className="flex items-center gap-4 text-xs">
          <div className="bg-slate-700/60 px-3 py-1.5 rounded-lg border border-slate-600">
            <span className="text-slate-400">Chờ nấu: </span>
            <span className="font-bold text-amber-400">
              {items.filter((i) => i.status === "pending").length}
            </span>
          </div>
          <div className="bg-slate-700/60 px-3 py-1.5 rounded-lg border border-slate-600">
            <span className="text-slate-400">Đang nấu: </span>
            <span className="font-bold text-blue-400">
              {items.filter((i) => i.status === "cooking").length}
            </span>
          </div>

          <button
            onClick={() => setSoundEnabled(!soundEnabled)}
            className={`p-2 rounded-lg border flex items-center gap-1.5 transition-colors ${
              soundEnabled
                ? "bg-slate-700 border-slate-600 text-emerald-400"
                : "bg-red-900/40 border-red-800 text-red-400"
            }`}
          >
            {soundEnabled ? <Volume2 className="w-4 h-4" /> : <VolumeX className="w-4 h-4" />}
            <span>{soundEnabled ? "Chuông: BẬT" : "Chuông: TẮT"}</span>
          </button>

          <Link
            href="/kitchen/stock"
            className="px-3 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-xs font-semibold"
          >
            Báo hết món
          </Link>

          <button
            onClick={fetchQueue}
            className="p-2 text-slate-400 hover:text-white rounded-lg bg-slate-700"
          >
            <RotateCcw className="w-4 h-4" />
          </button>
        </div>
      </header>

      {/* 2. Lưới thẻ món ăn FIFO */}
      <main className="p-6 flex-1 overflow-auto">
        {items.length === 0 ? (
          <div className="h-full flex flex-col items-center justify-center text-slate-500 py-32 space-y-3">
            <CheckCircle2 className="w-12 h-12 text-slate-600" />
            <p className="text-base font-medium">Bếp đang trống. Chưa có món nào cần nấu.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {items.map((item) => {
              const waitMins = getWaitMinutes(item.created_at);
              const isCooking = item.status === "cooking";

              return (
                <div
                  key={item.id}
                  className={`bg-slate-800 border border-slate-700 rounded-2xl p-4 shadow-md flex flex-col justify-between ${getBorderColor(
                    waitMins
                  )}`}
                >
                  <div className="space-y-2">
                    <div className="flex items-center justify-between pb-2 border-b border-slate-700">
                      <span className="font-extrabold text-lg text-white">
                        BÀN {item.session?.table?.code || "VIP"}
                      </span>
                      <span
                        className={`text-xs font-bold px-2 py-0.5 rounded-full ${
                          waitMins >= 10
                            ? "bg-red-500/20 text-red-400"
                            : waitMins >= 5
                            ? "bg-amber-500/20 text-amber-400"
                            : "bg-emerald-500/20 text-emerald-400"
                        }`}
                      >
                        ⏱ {waitMins} phút
                      </span>
                    </div>

                    <div>
                      <div className="flex items-start justify-between gap-2">
                        <h2 className="font-bold text-base text-slate-100">{item.dish?.name}</h2>
                        <span className="text-lg font-black text-orange-400 bg-orange-950/60 px-2 rounded-lg">
                          ×{item.quantity}
                        </span>
                      </div>
                      {item.note && (
                        <p className="mt-1 text-xs font-semibold text-amber-300 bg-amber-950/40 p-1.5 rounded-lg border border-amber-900/50">
                          ⚠️ {item.note}
                        </p>
                      )}
                    </div>
                  </div>

                  {/* Nút thao tác 1 chạm */}
                  <div className="mt-4 pt-3 border-t border-slate-700">
                    {!isCooking ? (
                      <button
                        onClick={() => handleUpdateStatus(item.id, "cooking")}
                        className="w-full py-2.5 bg-orange-600 hover:bg-orange-500 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow"
                      >
                        BẮT ĐẦU NẤU 🔥
                      </button>
                    ) : (
                      <button
                        onClick={() => handleUpdateStatus(item.id, "ready")}
                        className="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow"
                      >
                        NẤU XONG (SẴN SÀNG) ✅
                      </button>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </main>
    </div>
  );
}
