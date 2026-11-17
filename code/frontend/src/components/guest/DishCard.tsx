"use client";

import Link from "next/link";
import { formatVND } from "@/lib/utils";
import { Clock, Plus } from "lucide-react";

interface Dish {
  id: number;
  name: string;
  description?: string;
  price: number;
  image_url?: string;
  estimated_prep_time: number;
  status: "available" | "out_of_stock" | "hidden";
  is_featured?: boolean;
}

interface DishCardProps {
  dish: Dish;
  tableToken: string;
  onQuickAdd?: (dish: Dish) => void;
}

export default function DishCard({ dish, tableToken, onQuickAdd }: DishCardProps) {
  const isOutOfStock = dish.status === "out_of_stock";

  return (
    <div
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

        {/* Giá & Thao tác */}
        <div className="flex items-center justify-between mt-2 pt-1 border-t border-slate-50">
          <span className="font-bold text-orange-600 text-sm">
            {formatVND(dish.price)}
          </span>

          {onQuickAdd && (
            <button
              disabled={isOutOfStock}
              onClick={() => onQuickAdd(dish)}
              className="w-8 h-8 rounded-full bg-orange-600 text-white flex items-center justify-center shadow-md shadow-orange-200 active:scale-95 disabled:bg-slate-300 disabled:shadow-none transition-all"
              aria-label="Thêm nhanh"
            >
              <Plus className="w-4 h-4" />
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
