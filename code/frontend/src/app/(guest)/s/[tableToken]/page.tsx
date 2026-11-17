"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { useCartStore } from "@/stores/cartStore";
import { useSessionStore } from "@/stores/sessionStore";
import { apiFetch } from "@/lib/api";
import { formatVND } from "@/lib/utils";
import ConnectionBanner from "@/components/common/ConnectionBanner";
import { ShoppingBag, Search, Plus, Clock, AlertTriangle } from "lucide-react";

interface Dish {
  id: number;
  name: string;
  description: string;
  price: number;
  image_url?: string;
  estimated_prep_time: number;
  status: "available" | "out_of_stock" | "hidden";
  is_featured: boolean;
}

interface Category {
  id: number;
  name: string;
  dishes: Dish[];
}

export default function GuestMenuPage({ params }: { params: Promise<{ tableToken: string }> }) {
  const { tableToken } = use(params);
  const [categories, setCategories] = useState<Category[]>([]);
  const [activeTab, setActiveTab] = useState<number | "all">("all");
  const [loading, setLoading] = useState(true);
  const [warningMsg, setWarningMsg] = useState<string | null>(null);

  const { addToCart, getTotalItems, getTotalAmount } = useCartStore();
  const { setSessionData, table, restaurant } = useSessionStore();

  useEffect(() => {
    async function initSession() {
      // 1. Quét mã QR mở phiên
      const sessionRes = await apiFetch(`/s/${tableToken}`);
      if (sessionRes.success && sessionRes.data) {
        setSessionData({
          token: sessionRes.data.session.token,
          table: sessionRes.data.table,
          restaurant: sessionRes.data.restaurant,
        });
        if (sessionRes.data.warning) {
          setWarningMsg(sessionRes.data.warning);
        }

        // 2. Tải danh mục thực đơn
        const menuRes = await apiFetch<Category[]>("/s/menu", {
          sessionToken: sessionRes.data.session.token,
        });
        if (menuRes.success && menuRes.data) {
          setCategories(menuRes.data);
        }
      }
      setLoading(false);
    }

    initSession();
  }, [tableToken, setSessionData]);

  const totalCartCount = getTotalItems();
  const totalCartAmount = getTotalAmount();

  const filteredDishes =
    activeTab === "all"
      ? categories.flatMap((c) => c.dishes)
      : categories.find((c) => c.id === activeTab)?.dishes || [];

  return (
    <div className="pb-24 max-w-md mx-auto min-h-screen bg-slate-50 flex flex-col">
      {/* Banner cảnh báo mất WebSocket */}
      <ConnectionBanner />

      {/* 1. Header cố định */}
      <header className="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 shadow-sm">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="font-bold text-lg text-slate-900 leading-tight">
              {restaurant?.name || "Quán Bếp Nhỏ"}
            </h1>
            <p className="text-xs text-orange-600 font-semibold">
              📍 {table?.code ? `Bàn ${table.code}` : "Đang kết nối..."} · {table?.area_name}
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Link
              href={`/s/${tableToken}/search`}
              className="p-2 text-slate-600 hover:text-orange-600 rounded-full hover:bg-slate-100 transition-colors"
            >
              <Search className="w-5 h-5" />
            </Link>
            <Link
              href={`/s/${tableToken}/orders`}
              className="text-xs px-2.5 py-1.5 rounded-full bg-slate-100 text-slate-700 font-medium hover:bg-slate-200"
            >
              Đơn của tôi
            </Link>
          </div>
        </div>

        {/* Cảnh báo bàn có khách */}
        {warningMsg && (
          <div className="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2 text-xs text-amber-800">
            <AlertTriangle className="w-4 h-4 shrink-0 text-amber-600" />
            <span>{warningMsg}</span>
          </div>
        )}

        {/* 2. Thanh tab danh mục vuốt ngang */}
        <div className="flex items-center gap-2 overflow-x-auto no-scrollbar pt-3 pb-1 -mx-4 px-4">
          <button
            onClick={() => setActiveTab("all")}
            className={`whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
              activeTab === "all"
                ? "bg-orange-600 text-white shadow-sm"
                : "bg-slate-100 text-slate-600 hover:bg-slate-200"
            }`}
          >
            Tất cả món
          </button>
          {categories.map((cat) => (
            <button
              key={cat.id}
              onClick={() => setActiveTab(cat.id)}
              className={`whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
                activeTab === cat.id
                  ? "bg-orange-600 text-white shadow-sm"
                : "bg-slate-100 text-slate-600 hover:bg-slate-200"
              }`}
            >
              {cat.name} ({cat.dishes.length})
            </button>
          ))}
        </div>
      </header>

      {/* 3. Danh sách món ăn */}
      <main className="p-4 flex-1 space-y-3">
        {loading ? (
          <div className="space-y-3">
            {[1, 2, 3, 4].map((i) => (
              <div key={i} className="h-24 bg-slate-200 animate-pulse rounded-2xl" />
            ))}
          </div>
        ) : filteredDishes.length === 0 ? (
          <div className="text-center py-12 text-slate-400 text-sm">
            Hiện chưa có món ăn trong danh mục này.
          </div>
        ) : (
          filteredDishes.map((dish) => {
            const isOutOfStock = dish.status === "out_of_stock";

            return (
              <div
                key={dish.id}
                className={`flex gap-3 p-3 bg-white rounded-2xl border border-slate-100 shadow-sm transition-all ${
                  isOutOfStock ? "opacity-60" : "hover:border-slate-300"
                }`}
              >
                {/* Ảnh món */}
                <div className="w-20 h-20 rounded-xl bg-orange-50 shrink-0 overflow-hidden relative flex items-center justify-center text-slate-400">
                  {dish.image_url ? (
                    <img
                      src={dish.image_url}
                      alt={dish.name}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <span className="text-2xl">🍲</span>
                  )}
                  {isOutOfStock && (
                    <span className="absolute inset-0 bg-black/50 text-white text-[10px] font-bold flex items-center justify-center uppercase tracking-wider">
                      Hết món
                    </span>
                  )}
                </div>

                {/* Thông tin món */}
                <div className="flex-1 flex flex-col justify-between">
                  <div>
                    <Link
                      href={`/s/${tableToken}/dish/${dish.id}`}
                      className="font-semibold text-slate-900 text-sm hover:text-orange-600 line-clamp-1"
                    >
                      {dish.name}
                    </Link>
                    <p className="text-xs text-slate-500 line-clamp-1 mt-0.5">
                      {dish.description || "Hương vị thơm ngon, phục vụ nóng hổi"}
                    </p>
                    <div className="flex items-center gap-1.5 mt-1 text-[11px] text-slate-400">
                      <Clock className="w-3 h-3" />
                      <span>~{dish.estimated_prep_time} phút</span>
                    </div>
                  </div>

                  <div className="flex items-center justify-between mt-2">
                    <span className="font-bold text-orange-600 text-sm">
                      {formatVND(dish.price)}
                    </span>

                    {/* Nút thêm nhanh */}
                    <button
                      disabled={isOutOfStock}
                      onClick={() => addToCart(dish)}
                      className={`w-8 h-8 rounded-full flex items-center justify-center transition-all ${
                        isOutOfStock
                          ? "bg-slate-200 text-slate-400 cursor-not-allowed"
                          : "bg-orange-600 text-white hover:bg-orange-700 active:scale-95 shadow-sm"
                      }`}
                      aria-label="Thêm vào giỏ"
                    >
                      <Plus className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            );
          })
        )}
      </main>

      {/* 4. Thanh Giỏ hàng nổi cố định ở đáy (Sticky Bottom Cart Bar) */}
      {totalCartCount > 0 && (
        <div className="fixed bottom-4 inset-x-4 max-w-md mx-auto z-40">
          <Link
            href={`/s/${tableToken}/cart`}
            className="flex items-center justify-between bg-orange-600 text-white px-5 py-3.5 rounded-2xl shadow-lg hover:bg-orange-700 active:scale-[0.98] transition-all"
          >
            <div className="flex items-center gap-3">
              <div className="relative">
                <ShoppingBag className="w-6 h-6" />
                <span className="absolute -top-1.5 -right-2 bg-white text-orange-600 font-bold text-xs rounded-full w-5 h-5 flex items-center justify-center shadow">
                  {totalCartCount}
                </span>
              </div>
              <div>
                <p className="text-xs font-medium text-orange-100">Tạm tính giỏ hàng</p>
                <p className="font-bold text-sm">{formatVND(totalCartAmount)}</p>
              </div>
            </div>

            <span className="text-sm font-semibold flex items-center gap-1">
              Xem giỏ hàng ➔
            </span>
          </Link>
        </div>
      )}
    </div>
  );
}
