"use client";

import { Clock, Users } from "lucide-react";

interface TableInfo {
  id: number;
  table_number: string;
  capacity: number;
  area_name?: string;
  status: "empty" | "occupied" | "payment_pending";
  session_token?: string;
  opened_at?: string;
  duration_minutes?: number;
  item_count?: number;
}

interface TableBadgeProps {
  table: TableInfo;
  onClick?: (table: TableInfo) => void;
}

export default function TableBadge({ table, onClick }: TableBadgeProps) {
  const isOccupied = table.status === "occupied";
  const isPending = table.status === "payment_pending";

  const statusColor = isPending
    ? "bg-amber-50 border-amber-300 hover:border-amber-400"
    : isOccupied
    ? "bg-emerald-50 border-emerald-300 hover:border-emerald-400"
    : "bg-white border-slate-200 hover:border-slate-300";

  const badgeColor = isPending
    ? "bg-amber-100 text-amber-800"
    : isOccupied
    ? "bg-emerald-100 text-emerald-800"
    : "bg-slate-100 text-slate-600";

  const statusText = isPending
    ? "Chờ thanh toán"
    : isOccupied
    ? "Đang dùng bữa"
    : "Bàn trống";

  return (
    <div
      onClick={() => onClick && onClick(table)}
      className={`p-4 rounded-2xl border-2 shadow-sm transition-all cursor-pointer flex flex-col justify-between ${statusColor}`}
    >
      <div className="flex justify-between items-start">
        <h4 className="text-xl font-black text-slate-900 tracking-tight">
          {table.table_number}
        </h4>
        <span className={`text-[11px] font-bold px-2 py-0.5 rounded-full ${badgeColor}`}>
          {statusText}
        </span>
      </div>

      <div className="mt-4 pt-3 border-t border-slate-100/80 flex items-center justify-between text-xs text-slate-500">
        <div className="flex items-center gap-1">
          <Users className="w-3.5 h-3.5" />
          <span>{table.capacity} chỗ</span>
        </div>

        {isOccupied && table.duration_minutes !== undefined && (
          <div className="flex items-center gap-1 text-slate-600 font-mono">
            <Clock className="w-3.5 h-3.5 text-slate-400" />
            <span>{table.duration_minutes}p</span>
          </div>
        )}
      </div>
    </div>
  );
}
