"use client";

import { Flame, CheckCircle2, XCircle, AlertTriangle } from "lucide-react";

interface KdsItem {
  id: number;
  order_no: string;
  table_name: string;
  dish_name: string;
  quantity: number;
  note?: string;
  status: "pending" | "cooking" | "ready";
  created_at: string;
  elapsed_minutes: number;
}

interface KdsTicketProps {
  item: KdsItem;
  onUpdateStatus: (id: number, nextStatus: "cooking" | "ready") => void;
  onCancelClick: (item: KdsItem) => void;
}

export default function KdsTicket({ item, onUpdateStatus, onCancelClick }: KdsTicketProps) {
  // Logic đổi màu viền theo thời gian chờ (Tuần 7)
  const isDanger = item.elapsed_minutes >= 10;
  const isWarning = item.elapsed_minutes >= 5 && item.elapsed_minutes < 10;

  const borderColor = isDanger
    ? "border-red-500 shadow-red-900/40"
    : isWarning
    ? "border-amber-500 shadow-amber-900/40"
    : "border-emerald-500/60 shadow-emerald-900/30";

  return (
    <div
      className={`bg-slate-800 rounded-2xl border-2 p-4 flex flex-col justify-between shadow-lg transition-all ${borderColor} ${
        isDanger ? "animate-pulse" : ""
      }`}
    >
      <div>
        {/* Header Ticket */}
        <div className="flex justify-between items-start border-b border-slate-700/60 pb-2 mb-3">
          <div>
            <span className="text-xl font-black text-amber-400 bg-amber-950/60 px-2 py-0.5 rounded-lg border border-amber-800">
              {item.table_name}
            </span>
            <span className="ml-2 text-xs font-mono text-slate-400">
              #{item.order_no}
            </span>
          </div>

          <div
            className={`px-2 py-1 rounded-md text-xs font-bold font-mono flex items-center gap-1 ${
              isDanger
                ? "bg-red-500 text-white"
                : isWarning
                ? "bg-amber-500 text-slate-900"
                : "bg-slate-700 text-emerald-400"
            }`}
          >
            {isDanger && <AlertTriangle className="w-3.5 h-3.5" />}
            <span>{item.elapsed_minutes}p</span>
          </div>
        </div>

        {/* Món ăn và định lượng */}
        <div className="flex items-start justify-between gap-2">
          <h3 className="text-lg font-bold text-white leading-tight">
            {item.dish_name}
          </h3>
          <span className="text-2xl font-black text-orange-400 bg-orange-950/80 px-2.5 py-0.5 rounded-xl border border-orange-800/80 shrink-0">
            x{item.quantity}
          </span>
        </div>

        {/* Ghi chú bếp */}
        {item.note && (
          <div className="mt-2 text-xs font-semibold text-rose-300 bg-rose-950/60 px-2 py-1.5 rounded-lg border border-rose-900/60">
            ⚠️ Lưu ý: {item.note}
          </div>
        )}
      </div>

      {/* Thao tác 1 chạm đổi trạng thái */}
      <div className="mt-4 pt-3 border-t border-slate-700/60 flex gap-2">
        <button
          onClick={() => onCancelClick(item)}
          className="p-3 bg-slate-700 hover:bg-rose-900/80 text-slate-300 hover:text-white rounded-xl transition-all"
          title="Hủy món"
        >
          <XCircle className="w-5 h-5" />
        </button>

        {item.status === "pending" && (
          <button
            onClick={() => onUpdateStatus(item.id, "cooking")}
            className="flex-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg active:scale-95 transition-all text-sm uppercase tracking-wide"
          >
            <Flame className="w-5 h-5 fill-current" />
            NẤU MÓN
          </button>
        )}

        {item.status === "cooking" && (
          <button
            onClick={() => onUpdateStatus(item.id, "ready")}
            className="flex-1 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg active:scale-95 transition-all text-sm uppercase tracking-wide"
          >
            <CheckCircle2 className="w-5 h-5" />
            NẤU XONG
          </button>
        )}
      </div>
    </div>
  );
}
