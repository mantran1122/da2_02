"use client";

import { useEffect } from "react";
import { AlertTriangle, RotateCcw } from "lucide-react";

export default function ErrorBoundary({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error("Application Error:", error);
  }, [error]);

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-slate-50 p-6 text-center">
      <div className="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
        <AlertTriangle className="w-8 h-8" />
      </div>
      <h2 className="text-xl font-bold text-slate-900">Đã xảy ra sự cố giao diện</h2>
      <p className="text-sm text-slate-500 mt-2 max-w-sm">
        {error.message || "Không thể kết nối hoặc tải dữ liệu giao diện lúc này."}
      </p>
      <button
        onClick={() => reset()}
        className="mt-6 px-5 py-2.5 bg-slate-900 text-white font-semibold text-xs rounded-xl shadow-md hover:bg-slate-800 flex items-center gap-2"
      >
        <RotateCcw className="w-4 h-4" />
        Thử tải lại trang
      </button>
    </div>
  );
}
