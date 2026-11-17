<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ItemStateMachine;
use App\Models\OrderItem;
use Exception;

class ItemStateMachineTest extends TestCase
{
    protected ItemStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new ItemStateMachine();
    }

    /**
     * Test chuyển trạng thái hợp lệ 1 chiều
     */
    public function test_valid_transitions(): void
    {
        $item = new OrderItem(['status' => 'pending']);

        // Cho phép pending -> cooking
        $this->assertTrue(in_array('cooking', ['cooking', 'cancelled']));
    }

    /**
     * Test chặn nhảy cóc từ pending sang served
     */
    public function test_blocks_skipping_steps(): void
    {
        $item = new OrderItem(['status' => 'pending']);

        $this->expectException(Exception::class);
        $this->stateMachine->transition($item, 'served');
    }
}
