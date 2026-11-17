<?php

namespace App\Services;

use App\Models\DiningSession;
use App\Models\Invoice;
use App\Models\Restaurant;

class InvoiceService
{
    /**
     * Tính toán bảng kê chi tiết hóa đơn theo công thức chuẩn Golden Case 145k
     */
    public function calculateForSession(DiningSession $session, int $discount = 0, ?string $discountNote = null): array
    {
        $session->loadMissing(['orderItems.dish', 'table.area.restaurant']);
        
        $restaurant = $session->table?->area?->restaurant ?? Restaurant::first();
        $vatPercent = (float) ($restaurant->vat_percent ?? 8.0);
        $servicePercent = (float) ($restaurant->service_charge_percent ?? 0.0);

        // 1. Chỉ tính tiền các món không bị hủy
        $validItems = $session->orderItems->where('status', '!=', 'cancelled');

        $subtotal = 0;
        $itemsBreakdown = [];

        foreach ($validItems as $item) {
            $lineTotal = (int) $item->unit_price * (int) $item->quantity;
            $subtotal += $lineTotal;

            $itemsBreakdown[] = [
                'order_item_id' => $item->id,
                'dish_name' => $item->dish?->name ?? 'Món',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $lineTotal,
            ];
        }

        // 2. Chiết khấu / Giảm giá (không vượt quá subtotal)
        $discountAmount = min($subtotal, max(0, $discount));
        $afterDiscount = $subtotal - $discountAmount;

        // 3. Phí phục vụ
        $serviceCharge = (int) round($afterDiscount * ($servicePercent / 100));

        // 4. Cơ sở tính thuế VAT (gồm tiền hàng sau giảm + phí phục vụ)
        $vatBase = $afterDiscount + $serviceCharge;
        $vatAmount = (int) round($vatBase * ($vatPercent / 100));

        // 5. Tổng thanh toán
        $totalAmount = $vatBase + $vatAmount;

        return [
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'discount_note' => $discountNote,
            'afterDiscount' => $afterDiscount,
            'serviceCharge' => $serviceCharge,
            'vatBase' => $vatBase,
            'vat' => $vatAmount,
            'total' => $totalAmount,
            'items' => $itemsBreakdown,
        ];
    }
}
