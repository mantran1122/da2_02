const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export async function apiFetch<T = any>(
  endpoint: string,
  options: RequestInit & { sessionToken?: string; staffToken?: string } = {}
): Promise<{ success: boolean; data?: T; message?: string; errors?: any }> {
  const { sessionToken, staffToken, headers, ...restOptions } = options;

  const reqHeaders: Record<string, string> = {
    "Content-Type": "application/json",
    Accept: "application/json",
    ...(headers as Record<string, string>),
  };

  if (sessionToken) {
    reqHeaders["X-Session-Token"] = sessionToken;
  }

  if (staffToken) {
    reqHeaders["Authorization"] = `Bearer ${staffToken}`;
  } else if (typeof window !== "undefined") {
    const savedStaffToken = localStorage.getItem("staff_token");
    if (savedStaffToken) {
      reqHeaders["Authorization"] = `Bearer ${savedStaffToken}`;
    }
  }

  try {
    const res = await fetch(`${API_BASE_URL}${endpoint}`, {
      headers: reqHeaders,
      ...restOptions,
    });

    const data = await res.json();
    return data;
  } catch (error: any) {
    console.error("API error:", error);
    return {
      success: false,
      message: error?.message || "Lỗi kết nối tới máy chủ API.",
    };
  }
}
