import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Hệ thống Gọi món QR Order & Quản lý Bếp Nhà hàng (KDS)",
  description: "Giải pháp chuyển đổi số toàn diện cho nhà hàng tích hợp gợi ý combo thông minh",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="vi">
      <body className="antialiased bg-slate-50 text-slate-900 min-h-screen">
        {children}
      </body>
    </html>
  );
}
