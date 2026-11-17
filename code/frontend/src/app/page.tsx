import Link from "next/link";
import { Utensils, ChefHat, LayoutGrid, DollarSign, Settings, QrCode } from "lucide-react";

export default function HomePage() {
  const demoTableToken = "a1b2c3d4-e5f6-7890-abcd-ef1234567890"; // Mã token bàn B12 mẫu

  return (
    <main className="max-w-4xl mx-auto px-4 py-12">
      <div className="text-center mb-10">
        <h1 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
          Hệ thống QR Order & Quản lý Bếp (KDS)
        </h1>
        <p className="mt-3 text-lg text-slate-600">
          Cổng điều hướng truy cập nhanh 4 phân hệ của đồ án
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* 1. KHÁCH HÀNG */}
        <Link
          href={`/s/${demoTableToken}`}
          className="group block p-6 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-orange-500 hover:shadow-md transition-all"
        >
          <div className="flex items-center gap-4 mb-3">
            <div className="p-3 bg-orange-100 text-orange-600 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-colors">
              <QrCode className="w-7 h-7" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-900">1. Khách Hàng (QR Order)</h2>
              <span className="text-sm text-orange-600 font-medium">Bàn B12 · Tầng 1</span>
            </div>
          </div>
          <p className="text-slate-600 text-sm">
            Quét mã tại bàn, xem thực đơn, nhận gợi ý combo thông minh, gửi đơn xuống bếp và theo dõi realtime.
          </p>
        </Link>

        {/* 2. BẾP (KDS) */}
        <Link
          href="/kitchen"
          className="group block p-6 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-blue-500 hover:shadow-md transition-all"
        >
          <div className="flex items-center gap-4 mb-3">
            <div className="p-3 bg-blue-100 text-blue-600 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
              <ChefHat className="w-7 h-7" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-900">2. Màn Hình Bếp (KDS)</h2>
              <span className="text-sm text-blue-600 font-medium">Tablet Landscape · Realtime</span>
            </div>
          </div>
          <p className="text-slate-600 text-sm">
            Nhận đơn FIFO có chuông báo, bộ đếm thời gian phân màu, đổi trạng thái món 1 chạm và báo hết món.
          </p>
        </Link>

        {/* 3. PHỤC VỤ & THU NGÂN */}
        <Link
          href="/staff/tables"
          className="group block p-6 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 hover:shadow-md transition-all"
        >
          <div className="flex items-center gap-4 mb-3">
            <div className="p-3 bg-emerald-100 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
              <LayoutGrid className="w-7 h-7" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-900">3. Phục Vụ & Sơ Đồ Bàn</h2>
              <span className="text-sm text-emerald-600 font-medium">Table Map & Sessions</span>
            </div>
          </div>
          <p className="text-slate-600 text-sm">
            Sơ đồ bàn trực quan theo khu vực, thời gian ngồi, chuyển bàn, gộp bàn và mở phiên thủ công.
          </p>
        </Link>

        {/* 4. QUẢN TRỊ ADMIN */}
        <Link
          href="/admin/dishes"
          className="group block p-6 bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-purple-500 hover:shadow-md transition-all"
        >
          <div className="flex items-center gap-4 mb-3">
            <div className="p-3 bg-purple-100 text-purple-600 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
              <Settings className="w-7 h-7" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-slate-900">4. Quản Trị Viên (Admin)</h2>
              <span className="text-sm text-purple-600 font-medium">FP-Growth & Thực đơn</span>
            </div>
          </div>
          <p className="text-slate-600 text-sm">
            Quản lý món, định lượng nguyên liệu, xem báo cáo doanh thu và kích hoạt khai phá FP-Growth.
          </p>
        </Link>
      </div>

      <div className="mt-12 p-4 bg-slate-100 rounded-xl text-center text-xs text-slate-500">
        Đồ án 2: Hệ thống Gọi món tại Bàn & Quản lý Bếp Nhà hàng · Stack: Next.js 15 + Laravel 11 + Reverb WebSocket
      </div>
    </main>
  );
}
