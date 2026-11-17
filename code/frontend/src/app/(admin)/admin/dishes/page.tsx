"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { formatVND } from "@/lib/utils";
import { ArrowLeft, Plus, Edit, Trash2, Sparkles } from "lucide-react";

interface Dish {
  id: number;
  name: string;
  category?: { name: string };
  price: number;
  estimated_prep_time: number;
  status: string;
  is_featured: boolean;
}

export default function AdminDishesPage() {
  const [dishes, setDishes] = useState<Dish[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadDishes() {
      const res = await apiFetch<any>("/admin/dishes");
      if (res.success && res.data) {
        setDishes(res.data.data || res.data);
      }
      setLoading(false);
    }
    loadDishes();
  }, []);

  return (
    <div className="min-h-screen bg-slate-50 p-6">
      <div className="max-w-5xl mx-auto space-y-6">
        <header className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link href="/" className="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-100">
              <ArrowLeft className="w-5 h-5 text-slate-700" />
            </Link>
            <div>
              <h1 className="text-xl font-bold text-slate-900">Quản Lý Thực Đơn & Định Lượng</h1>
              <p className="text-xs text-slate-500">Danh mục món ăn, giá bán và thời gian chế biến</p>
            </div>
          </div>

          <div className="flex gap-2">
            <Link
              href="/admin/combo-rules"
              className="px-4 py-2 bg-orange-50 text-orange-600 border border-orange-200 rounded-xl text-xs font-semibold flex items-center gap-1.5 hover:bg-orange-100"
            >
              <Sparkles className="w-4 h-4" />
              Khai phá luật Combo (FP-Growth)
            </Link>
          </div>
        </header>

        {loading ? (
          <div className="text-center py-20 text-slate-400">Đang tải danh sách món...</div>
        ) : (
          <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <table className="w-full text-left text-xs text-slate-600">
              <thead className="bg-slate-50 text-slate-800 border-b border-slate-200 uppercase font-bold text-[10px]">
                <tr>
                  <th className="px-4 py-3">ID</th>
                  <th className="px-4 py-3">Tên món</th>
                  <th className="px-4 py-3">Danh mục</th>
                  <th className="px-4 py-3">Đơn giá</th>
                  <th className="px-4 py-3">Thời gian nấu</th>
                  <th className="px-4 py-3">Trạng thái</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {dishes.map((dish) => (
                  <tr key={dish.id} className="hover:bg-slate-50">
                    <td className="px-4 py-3 font-mono font-bold text-slate-400">#{dish.id}</td>
                    <td className="px-4 py-3 font-semibold text-slate-900">{dish.name}</td>
                    <td className="px-4 py-3">{dish.category?.name || "Món chính"}</td>
                    <td className="px-4 py-3 font-bold text-orange-600">{formatVND(dish.price)}</td>
                    <td className="px-4 py-3">~{dish.estimated_prep_time} phút</td>
                    <td className="px-4 py-3">
                      <span
                        className={`px-2 py-0.5 rounded text-[10px] font-bold ${
                          dish.status === "available"
                            ? "bg-emerald-100 text-emerald-700"
                            : "bg-red-100 text-red-700"
                        }`}
                      >
                        {dish.status === "available" ? "Sẵn sàng" : "Hết món"}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
