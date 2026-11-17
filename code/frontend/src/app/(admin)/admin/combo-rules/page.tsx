"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { ArrowLeft, Sparkles, Play, CheckCircle2 } from "lucide-react";

interface ComboRule {
  id: number;
  antecedent_dish?: { name: string };
  consequent_dish?: { name: string };
  support: string;
  confidence: string;
  lift: string;
  source: string;
}

export default function AdminComboRulesPage() {
  const [rules, setRules] = useState<ComboRule[]>([]);
  const [loading, setLoading] = useState(true);
  const [mining, setMining] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  const fetchRules = async () => {
    const res = await apiFetch<any>("/admin/combo-rules");
    if (res.success && res.data) {
      setRules(res.data.data || res.data);
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchRules();
  }, []);

  const handleReMine = async () => {
    setMining(true);
    setMessage(null);
    const res = await apiFetch("/admin/combo-rules/re-mine", { method: "POST" });
    setMining(false);
    if (res.success) {
      setMessage("Đã đẩy job khai phá luật FP-Growth vào hàng đợi Redis!");
      setTimeout(fetchRules, 2000);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 p-6">
      <div className="max-w-5xl mx-auto space-y-6">
        <header className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link href="/admin/dishes" className="p-2 bg-white border border-slate-200 rounded-xl hover:bg-slate-100">
              <ArrowLeft className="w-5 h-5 text-slate-700" />
            </Link>
            <div>
              <h1 className="text-xl font-bold text-slate-900">Khai Phá Luật Gợi Ý Combo (FP-Growth)</h1>
              <p className="text-xs text-slate-500">Mô hình học máy không giám sát phát hiện món ăn thường gọi kèm</p>
            </div>
          </div>

          <button
            disabled={mining}
            onClick={handleReMine}
            className="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 shadow transition-all disabled:opacity-50"
          >
            <Play className="w-4 h-4 fill-white" />
            {mining ? "Đang chạy mô hình..." : "Chạy lại FP-Growth"}
          </button>
        </header>

        {message && (
          <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-2 text-xs text-emerald-800">
            <CheckCircle2 className="w-4 h-4 text-emerald-600" />
            <span>{message}</span>
          </div>
        )}

        {loading ? (
          <div className="text-center py-20 text-slate-400">Đang tải danh sách luật...</div>
        ) : (
          <div className="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <table className="w-full text-left text-xs text-slate-600">
              <thead className="bg-slate-50 text-slate-800 border-b border-slate-200 uppercase font-bold text-[10px]">
                <tr>
                  <th className="px-4 py-3">Món nguồn (A)</th>
                  <th className="px-4 py-3">➔ Món gợi ý kèm (B)</th>
                  <th className="px-4 py-3">Độ hỗ trợ (Support)</th>
                  <th className="px-4 py-3">Độ tin cậy (Confidence)</th>
                  <th className="px-4 py-3">Độ tương quan (Lift)</th>
                  <th className="px-4 py-3">Nguồn luật</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-mono text-[11px]">
                {rules.map((rule) => (
                  <tr key={rule.id} className="hover:bg-slate-50">
                    <td className="px-4 py-3 font-sans font-bold text-slate-900">
                      {rule.antecedent_dish?.name || "Món A"}
                    </td>
                    <td className="px-4 py-3 font-sans font-bold text-orange-600">
                      ➔ {rule.consequent_dish?.name || "Món B"}
                    </td>
                    <td className="px-4 py-3 text-slate-600">
                      {(parseFloat(rule.support) * 100).toFixed(1)}%
                    </td>
                    <td className="px-4 py-3 text-emerald-600 font-bold">
                      {(parseFloat(rule.confidence) * 100).toFixed(1)}%
                    </td>
                    <td className="px-4 py-3 text-purple-600 font-bold">{rule.lift}</td>
                    <td className="px-4 py-3">
                      <span
                        className={`px-2 py-0.5 rounded text-[10px] font-sans font-bold ${
                          rule.source === "fp_growth"
                            ? "bg-purple-100 text-purple-700"
                            : "bg-slate-100 text-slate-700"
                        }`}
                      >
                        {rule.source === "fp_growth" ? "AI FP-Growth" : "Thủ công"}
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
