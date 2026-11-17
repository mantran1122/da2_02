import { create } from "zustand";

interface TableInfo {
  id: number;
  code: string;
  capacity: number;
  area_name: string;
}

interface RestaurantInfo {
  name: string;
  vat_percent: number;
  service_charge_percent: number;
}

interface SessionStore {
  sessionToken: string | null;
  table: TableInfo | null;
  restaurant: RestaurantInfo | null;
  setSessionData: (data: { token: string; table: TableInfo; restaurant: RestaurantInfo }) => void;
  clearSession: () => void;
}

export const useSessionStore = create<SessionStore>((set) => ({
  sessionToken: null,
  table: null,
  restaurant: null,

  setSessionData: ({ token, table, restaurant }) => {
    if (typeof window !== "undefined") {
      sessionStorage.setItem("guest_session_token", token);
    }
    set({ sessionToken: token, table, restaurant });
  },

  clearSession: () => {
    if (typeof window !== "undefined") {
      sessionStorage.removeItem("guest_session_token");
    }
    set({ sessionToken: null, table, null: null, restaurant: null });
  },
}));
