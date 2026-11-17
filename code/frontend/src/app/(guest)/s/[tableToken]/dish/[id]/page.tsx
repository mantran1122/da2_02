"use client";

import { useEffect, useState, use } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useCartStore } from "@/stores/cartStore";
import { useSessionStore } from "@/stores/sessionStore";
import { apiFetch } from "@/lib/api";
import { formatVND } from "@/lib/utils";
import { ArrowLeft, Clock, Plus, Sparkles, Check } from "lucide-react";

interface Dish {
  id: number;
  name: string;
  description: string;
  price: number;
  image_url?: string;
  estimated_prep_time: number;
}

export default function DishDetailPage({
  params,
}: {
  params: Promise<{ tableToken: string; id: string }>;
}) {
  const { tableToken, id } = use(params);
  const router = useRouter();
  const [dish, setDish] = useState<Dish | null>(null);
  const [suggestions, setSuggestions] = useState<Dish[]>([]);
  const [note, setNote] = useState("");
  const [added, setAdded] = useState(false);

  const { addToCart } = useCartStore();
  const { sessionToken } = useSessionStore();

  const noteChips = ["Không hành", "Ít cay", "Nhiều ớt", "Để riêng nước chấm"];

  useEffect(() => {
    async function loadDishAndCombo() {
      // 1. Chi tiết món
      const res = await apiFetch<Dish>(`/s/menu/dishes/${id}`, {
        sessionToken: sessionToken || undefined,
      });
      if (res.success && res.data) {
        setDish(res.data);
      }

      // 2. Gợi ý combo món kèm FP-Growth
      const sugRes = await apiFetch<Dish[]>(`/s/menu/dishes/${id}/suggestions`, {
        sessionToken: sessionToken || undefined,
      });
      if (sugRes.success && sugRes.data) {
        setSuggestions(sugRes.data);
      }
    }

    loadDishAndCombo();
  }, [id, sessionToken]);

  if (!dish) {
    return (
      <div className="max-w-md mx-auto p-4 text-center py-20 text-slate-400">
        Đang tải thông tin món ăn...
      </div>
    );
  }

  const handleAddToCart = () => {
    addToCart(dish, note);
    setAdded(true);
    setTimeout(() => {
      router.push(`/s/${tableToken}`);
    }, 600);
  };

  return (
    <div className="max-w-md mx-auto min-h-screen bg-white flex flex-col pb-24">
      {/* Header back button */}
      <div className="relative h-64 bg-orange-100 flex items-center justify-center">
        {dish.image_url ? (
          <img src={dish.image_url} alt={dish.name} className="w-full h-full object-cover" />
        ) : (
          <span className="text-6xl">🍲</span>
        )}
        <Link
          href={`/s/${tableToken}`}
          className="absolute top-4 left-4 p-2 bg-white/80 backdrop-blur rounded-full text-slate-800 shadow"
        >
          <ArrowLeft className="w-5 h-5" />
        </Link>
      </div>

      <div className="p-5 flex-1 space-y-6">
        {/* Tên & giá */}
        <div>
          <div className="flex items-start justify-between gap-2">
            <h1 className="text-xl font-bold text-slate-900">{dish.name}</h1>
            <span className="text-lg font-bold text-orange-600 whitespace-nowrap">
              {formatVND(dish.price)}
            </span>
          </div>
          <div className="flex items-center gap-1.5 mt-1 text-xs text-slate-500">
            <Clock className="w-4 h-4 text-slate-400" />
            <span>Thời gian chế biến ước tính: ~{dish.estimated_prep_time} phút</span>
          </div>
          <p className="mt-3 text-sm text-slate-600 leading-relaxed">
            {dish.description || "Món ăn đậm đà bản sắc, chế biến từ nguyên liệu tươi ngon nhất trong ngày."}
          </p>
        </div>

        {/* Ghi chú nhanh bằng chips */}
        <div className="space-y-2">
          <label className="text-xs font-semibold text-slate-700">Yêu cầu đặc biệt cho bếp:</label>
          <div className="flex flex-wrap gap-2">
            {noteChips.map((chip) => (
              <button
                key={chip}
                onClick={() => setNote((prev) => (prev ? `${prev}, ${chip}` : chip))}
                className="px-3 py-1 bg-slate-100 text-slate-700 text-xs rounded-full hover:bg-orange-50 hover:text-orange-600 border border-transparent hover:border-orange-200 transition-all"
              >
                + {chip}
              </button>
            ))}
          </div>
          <input
            type="text"
            value={note}
            onChange={(e) => setNote(e.target.value)}
            placeholder="Ghi chú thêm (ví dụ: không lấy rau thơm...)"
            className="w-full mt-2 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
          />
        </div>

        {/* Khối gợi ý Combo FP-Growth */}
        {suggestions.length > 0 && (
          <div className="pt-4 border-t border-slate-100 space-y-3">
            <div className="flex items-center gap-1.5 text-slate-900">
              <Sparkles className="w-4 h-4 text-orange-500 fill-orange-500" />
              <h2 className="text-sm font-bold">Thường được gọi cùng món này:</h2>
            </div>

            <div className="grid grid-cols-3 gap-2">
              {suggestions.map((sug) => (
                <div
                  key={sug.id}
                  className="p-2.5 bg-orange-50/50 border border-orange-100 rounded-xl flex flex-col justify-between text-left"
                >
                  <p className="text-xs font-semibold text-slate-800 line-clamp-2">{sug.name}</p>
                  <div className="mt-2 flex items-center justify-between">
                    <span className="text-[11px] font-bold text-orange-600">
                      {formatVND(sug.price)}
                    </span>
                    <button
                      onClick={() => addToCart(sug)}
                      className="p-1 bg-orange-600 text-white rounded-full hover:bg-orange-700"
                      title="Thêm nhanh"
                    >
                      <Plus className="w-3 h-3" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Nút hành động đáy */}
      <div className="fixed bottom-0 inset-x-0 max-w-md mx-auto p-4 bg-white border-t border-slate-100">
        <button
          onClick={handleAddToCart}
          className={`w-full py-3.5 rounded-2xl font-semibold text-sm flex items-center justify-center gap-2 shadow-lg transition-all ${
            added
              ? "bg-emerald-600 text-white"
              : "bg-orange-600 text-white hover:bg-orange-700 active:scale-[0.98]"
          }`}
        >
          {added ? (
            <>
              <Check className="w-5 h-5" />
              Đã thêm vào giỏ hàng
            </>
          ) : (
            `Thêm vào giỏ hàng · ${formatVND(dish.price)}`
          )}
        </button>
      </div>
    </div>
  );
}
