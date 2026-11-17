"use client";

import { useEffect, useState } from "react";
import { getEcho } from "@/lib/echo";
import { WifiOff, RefreshCw } from "lucide-react";

interface ConnectionBannerProps {
  onReconnect?: () => void;
}

export default function ConnectionBanner({ onReconnect }: ConnectionBannerProps) {
  const [isDisconnected, setIsDisconnected] = useState(false);
  const [isReconnecting, setIsReconnecting] = useState(false);

  useEffect(() => {
    const echo = getEcho();
    if (!echo || !(echo as any).connector?.pusher) return;

    const pusher = (echo as any).connector.pusher;

    const handleStateChange = (states: { current: string; previous: string }) => {
      if (states.current === "disconnected" || states.current === "unavailable") {
        setIsDisconnected(true);
      } else if (states.current === "connected") {
        if (isDisconnected) {
          setIsDisconnected(false);
          setIsReconnecting(false);
          if (onReconnect) onReconnect();
        }
      }
    };

    pusher.connection.bind("state_change", handleStateChange);

    return () => {
      pusher.connection.unbind("state_change", handleStateChange);
    };
  }, [isDisconnected, onReconnect]);

  const handleManualReconnect = () => {
    setIsReconnecting(true);
    const echo = getEcho();
    if (echo && (echo as any).connector?.pusher) {
      (echo as any).connector.pusher.connect();
    }
    setTimeout(() => {
      setIsReconnecting(false);
    }, 3000);
  };

  if (!isDisconnected) return null;

  return (
    <div className="bg-amber-500 text-slate-950 px-4 py-2 text-xs font-semibold flex items-center justify-between sticky top-0 z-50 shadow-md animate-in slide-in-from-top duration-300">
      <div className="flex items-center gap-2">
        <WifiOff className="w-4 h-4 shrink-0 text-slate-900" />
        <span>Mất kết nối thời gian thực. Hệ thống đang tự động kết nối lại mỗi 3s...</span>
      </div>

      <button
        disabled={isReconnecting}
        onClick={handleManualReconnect}
        className="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg flex items-center gap-1 active:scale-95 disabled:opacity-50"
      >
        <RefreshCw className={`w-3 h-3 ${isReconnecting ? "animate-spin" : ""}`} />
        <span>{isReconnecting ? "Đang thử..." : "Thử lại ngay"}</span>
      </button>
    </div>
  );
}
