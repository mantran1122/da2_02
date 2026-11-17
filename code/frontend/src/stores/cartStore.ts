import { create } from "zustand";

export interface CartItem {
  dish_id: number;
  name: string;
  price: number;
  quantity: number;
  note?: string;
  image_url?: string;
}

interface CartStore {
  items: CartItem[];
  addToCart: (dish: { id: number; name: string; price: number; image_url?: string }, note?: string) => void;
  updateQuantity: (dishId: number, quantity: number) => void;
  updateNote: (dishId: number, note: string) => void;
  removeFromCart: (dishId: number) => void;
  clearCart: () => void;
  getTotalItems: () => number;
  getTotalAmount: () => number;
}

export const useCartStore = create<CartStore>((set, get) => ({
  items: [],

  addToCart: (dish, note) => {
    set((state) => {
      const existing = state.items.find((i) => i.dish_id === dish.id);
      if (existing) {
        return {
          items: state.items.map((i) =>
            i.dish_id === dish.id ? { ...i, quantity: i.quantity + 1, note: note || i.note } : i
          ),
        };
      }
      return {
        items: [
          ...state.items,
          {
            dish_id: dish.id,
            name: dish.name,
            price: dish.price,
            quantity: 1,
            note: note || "",
            image_url: dish.image_url,
          },
        ],
      };
    });
  },

  updateQuantity: (dishId, quantity) => {
    set((state) => {
      if (quantity <= 0) {
        return { items: state.items.filter((i) => i.dish_id !== dishId) };
      }
      return {
        items: state.items.map((i) => (i.dish_id === dishId ? { ...i, quantity } : i)),
      };
    });
  },

  updateNote: (dishId, note) => {
    set((state) => ({
      items: state.items.map((i) => (i.dish_id === dishId ? { ...i, note } : i)),
    }));
  },

  removeFromCart: (dishId) => {
    set((state) => ({
      items: state.items.filter((i) => i.dish_id !== dishId),
    }));
  },

  clearCart: () => set({ items: [] }),

  getTotalItems: () => {
    return get().items.reduce((sum, item) => sum + item.quantity, 0);
  },

  getTotalAmount: () => {
    return get().items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  },
}));
