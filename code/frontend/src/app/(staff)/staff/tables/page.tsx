"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { getEcho } from "@/lib/echo";
import { LayoutGrid, Users, PlusCircle, BellRing, RotateCcw } from "lucide-react";

interface Table {
  id: number;
  code: string;
  capacity: number;
  active_session?: {
    id: number;
    customer_count: number;
    order_items_count: number;
    opened_at: string;
  };
}

interface Area {
  id: number;
  name: string;
  tables: Table[];
}

export default function StaffTablesPage() {
  const [areas, setAreas] = useState<Area[]>([]);
  const [activeAreaId, setActiveAreaId] = useState<number | "all">("all");
  const [loading, setLoading] = useState(true);

  const fetchTables = async () => {
    const res = await apiFetch<Area[]>("/staff/tables");
    if (res.success && res.data) {
      setAreas(res.data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchTables();

    // Lắng nghe chuông gọi thanh toán từ khách
    const echo = getEcho();
    if (echo) {
      const channel = echo.channel("cashier");
      channel.listen(".PaymentRequested", () => {
        fetchTables();
      });

      return () => {
        echo.leaveChannel("cashier");
      };
    }
  }, []);

  const allTables =
    activeAreaId === "all"
      ? areas.flatMap((a) => a.tables)
      : areas.find((a) => a.id === activeAreaId)?.tables || [];

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      <header className="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="p-2 bg-emerald-600 text-white rounded-xl">
            <LayoutGrid className="w-6 h-6" />
          </div>
          <div>
            <h1 className="font-bold text-lg text-slate-900">SƠ ĐỒ BÀN & PHIÊN ĂN</h1>
            <p className="text-xs text-slate-500">Quản lý phiên khách, chuyển bàn và phục vụ</p>
          </div>
        </div>

        <button
          onClick={fetchTables}
          className="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl flex items-center gap-1.5 text-xs font-semibold"
        >
          <RotateCcw className="w-4 h-4" />
          Làm mới
        </button>
      </header>

      {/* Bộ lọc khu vực */}
      <div className="px-6 py-3 bg-white border-b border-slate-100 flex gap-2 overflow-x-auto no-scrollbar">
        <button
          onClick={() => setActiveAreaId("all")}
          className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
            activeAreaId === "all"
              ? "bg-emerald-600 text-white"
              : "bg-slate-100 text-slate-600 hover:bg-slate-200"
          }`}
        >
          Tất cả khu vực
        </button>
        {areas.map((a) => (
          <button
            key={a.id}
            onClick={() => setActiveAreaId(a.id)}
            className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
              activeAreaId === a.id
                ? "bg-emerald-600 text-white"
                : "bg-slate-100 text-slate-600 hover:bg-slate-200"
            }`}
          >
            {a.name} ({a.tables.length})
          </button>
        ))}
      </div>

      {/* Lưới sơ đồ bàn */}
      <main className="p-6 flex-1">
        {loading ? (
          <div className="text-center py-20 text-slate-400">Đang tải sơ đồ bàn...</div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            {allTables.map((table) => {
              const isOccupied = !!table.active_session;

              return (
                <div
                  key={table.id}
                  className={`p-4 rounded-2xl border transition-all flex flex-col justify-between ${
                    isOccupied
                      ? "bg-emerald-50 border-emerald-300 shadow-sm"
                      : "bg-white border-slate-200 opacity-90 hover:border-slate-300"
                  }`}
                >
                  <div>
                    <div className="flex items-center justify-between pb-2 border-b border-slate-100">
                      <span className="font-bold text-base text-slate-900">Bàn {table.code}</span>
                      <span className="text-[11px] text-slate-500 flex items-center gap-0.5">
                        <Users className="w-3 h-3" />
                        {table.capacity} chỗ
                      </span>
                    </div>

                    <div className="mt-3">
                      {isOccupied ? (
                        <div className="space-y-1">
                          <span className="inline-block px-2 py-0.5 bg-emerald-600 text-white rounded text-[10px] font-bold">
                            🟢 ĐANG CÓ KHÁCH
                          </span>
                          <p className="text-xs text-slate-600 font-medium">
                            Số món: {table.active_session?.order_items_count || 0}
                          </p>
                        </div>
                      ) : (
                        <span className="inline-block px-2 py-0.5 bg-slate-200 text-slate-600 rounded text-[10px] font-bold">
                          ⚪ BÀN TRỐNG
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="mt-4 pt-2">
                    {isOccupied ? (
                      <Link
                        href={`/cashier/invoices/${table.active_session?.id}`}
                        className="block w-full py-1.5 text-center bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700"
                      >
                        Tính tiền ➔
                      </Link>
                    ) : (
                      <span className="text-[11px] text-slate-400 text-center block">
                        Chờ khách quét QR
                      </span>
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
