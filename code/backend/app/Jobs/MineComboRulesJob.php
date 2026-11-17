<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\FpGrowthService;
use Illuminate\Support\Facades\Log;

class MineComboRulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FpGrowthService $fpGrowthService): void
    {
        Log::info('[FP-Growth] Bắt đầu chạy job khai phá luật kết hợp thực đơn...');
        $rulesCount = $fpGrowthService->mineAssociationRules(minSupport: 0.05, minConfidence: 0.30);
        Log::info("[FP-Growth] Hoàn tất khai phá. Đã sinh {$rulesCount} luật gợi ý combo.");
    }
}
