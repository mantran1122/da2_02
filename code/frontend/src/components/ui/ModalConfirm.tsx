"use client";

import { ReactNode } from "react";
import { AlertCircle, X } from "lucide-react";

interface ModalConfirmProps {
  isOpen: boolean;
  title: string;
  message?: string;
  children?: ReactNode;
  confirmLabel?: string;
  cancelLabel?: string;
  isDanger?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
}

export default function ModalConfirm({
  isOpen,
  title,
  message,
  children,
  confirmLabel = "Xác nhận",
  cancelLabel = "Hủy bỏ",
  isDanger = false,
  onConfirm,
  onCancel,
}: ModalConfirmProps) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-200">
      <div className="bg-white rounded-2xl w-full max-w-sm p-5 shadow-2xl space-y-4 border border-slate-100">
        <div className="flex items-start justify-between">
          <div className="flex items-center gap-2.5">
            {isDanger ? (
              <div className="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <AlertCircle className="w-5 h-5" />
              </div>
            ) : (
              <div className="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                <AlertCircle className="w-5 h-5" />
              </div>
            )}
            <h3 className="font-bold text-slate-900 text-base">{title}</h3>
          </div>
          <button
            onClick={onCancel}
            className="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {message && <p className="text-sm text-slate-600">{message}</p>}
        {children}

        <div className="flex gap-2 pt-2">
          <button
            type="button"
            onClick={onCancel}
            className="flex-1 py-2.5 rounded-xl border border-slate-200 font-semibold text-xs text-slate-700 hover:bg-slate-50 transition-all"
          >
            {cancelLabel}
          </button>
          <button
            type="button"
            onClick={onConfirm}
            className={`flex-1 py-2.5 rounded-xl font-semibold text-xs text-white shadow-md transition-all active:scale-95 ${
              isDanger
                ? "bg-rose-600 hover:bg-rose-700 shadow-rose-200"
                : "bg-orange-600 hover:bg-orange-700 shadow-orange-200"
            }`}
          >
            {confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
