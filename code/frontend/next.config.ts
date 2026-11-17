import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  reactStrictMode: false, // Để tránh double mount listener trong demo WebSocket
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "**",
      },
    ],
  },
};

export default nextConfig;
