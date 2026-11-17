import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
  interface Window {
    Pusher: any;
    Echo: any;
  }
}

let echoInstance: Echo<any> | null = null;

export function getEcho(): Echo<any> | null {
  if (typeof window === "undefined") {
    return null;
  }

  if (!echoInstance) {
    window.Pusher = Pusher;

    echoInstance = new Echo({
      broadcaster: "reverb",
      key: process.env.NEXT_PUBLIC_REVERB_KEY || "qr_reverb_key",
      wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || window.location.hostname,
      wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT || 8080),
      wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT || 8080),
      forceTLS: (process.env.NEXT_PUBLIC_REVERB_SCHEME || "http") === "https",
      enabledTransports: ["ws", "wss"],
    });

    window.Echo = echoInstance;
  }

  return echoInstance;
}
