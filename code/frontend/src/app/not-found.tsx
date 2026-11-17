import Link from "next/link";
import { UtensilsCrossed } from "lucide-react";

export default function NotFound() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-slate-50 p-6 text-center">
      <div className="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mb-4">
        <UtensilsCrossed className="w-8 h-8" />
      </div>
      <h2 className="text-xl font-bold text-slate-900">Không tìm thấy trang yêu cầu</h2>
      <p className="text-sm text-slate-500 mt-2 max-w-sm">
        Mã QR hoặc đường dẫn bàn không tồn tại hoặc phiên ăn đã kết thúc.
      </p>
      <Link
        href="/"
        className="mt-6 px-5 py-2.5 bg-orange-600 text-white font-semibold text-xs rounded-xl shadow-md hover:bg-orange-700"
      >
        Về trang chủ điều hướng
      </Link>
    </div>
  );
}
