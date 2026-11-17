"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { ArrowLeft, Check, X } from "lucide-react";

interface Dish {
  id: number;
  name: string;
  category?: {
    name: string;
  };
  status: "available" | "out_of_stock" | "hidden";
}

export default function KitchenStockPage() {
  const [dishes, setDishes] = useState<Dish[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchDishes = async () => {
    const res = await apiFetch<any>("/admin/dishes");
    if (res.success && res.data) {
      setDishes(res.data.data || res.data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchDishes();
  }, []);

  const handleToggleStock = async (dish: Dish) => {
    const isOut = dish.status === "out_of_stock";
    const endpoint = isOut
      ? `/kitchen/dishes/${dish.id}/back-in-stock`
      : `/kitchen/dishes/${dish.id}/out-of-stock`;

    const res = await apiFetch(endpoint, { method: "POST" });
    if (res.success) {
      setDishes((prev) =>
        prev.map((d) => (d.id === dish.id ? { ...d, status: isOut ? "available" : "out_of_stock" } : d))
      );
    }
  };

  return (
    <div className="min-h-screen bg-slate-900 text-white p-6">
      <header className="flex items-center gap-4 mb-6 pb-4 border-b border-slate-800">
        <Link
          href="/kitchen"
          className="p-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl"
        >
          <ArrowLeft className="w-5 h-5" />
        </Link>
        <div>
          <h1 className="text-xl font-bold">Quản Lý Tồn Món Nhanh (Bếp)</h1>
          <p className="text-xs text-slate-400">Chạm 1 lần để bật/tắt báo hết món ngay lập tức</p>
        </div>
      </header>

      {loading ? (
        <div className="text-center py-20 text-slate-500">Đang tải danh sách món...</div>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          {dishes.map((dish) => {
            const isAvailable = dish.status === "available";

            return (
              <button
                key={dish.id}
                onClick={() => handleToggleStock(dish)}
                className={`p-4 rounded-2xl border text-left flex flex-col justify-between transition-all ${
                  isAvailable
                    ? "bg-slate-800 border-emerald-500/50 hover:border-emerald-500"
                    : "bg-red-950/40 border-red-800 opacity-75"
                }`}
              >
                <div>
                  <span className="text-[10px] uppercase font-bold text-slate-400">
                    {dish.category?.name || "Món ăn"}
                  </span>
                  <h3 className="font-bold text-sm text-white mt-1">{dish.name}</h3>
                </div>

                <div className="mt-4 flex items-center justify-between">
                  <span
                    className={`text-xs font-bold px-2.5 py-1 rounded-full flex items-center gap-1 ${
                      isAvailable
                        ? "bg-emerald-500/20 text-emerald-400"
                        : "bg-red-500/20 text-red-400"
                    }`}
                  >
                    {isAvailable ? <Check className="w-3 h-3" /> : <X className="w-3 h-3" />}
                    {isAvailable ? "CÒN HÀNG" : "BÁO HẾT"}
                  </span>
                </div>
              </button>
            );
          })}
        </div>
      )}
    </div>
  );
}
