<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use Exception;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Tests\PluginTestCase;

class OrderStateTest extends PluginTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Return only enabled OrderStates.
     * @return void
     */
    public function test_return_only_enabled_order_states()
    {
        $count = OrderState::count();
        OrderState::whereNull('flag')->first()->update(['is_enabled' => false]);
        $this->assertEquals(OrderState::count(), $count-1);
    }

    /**
     * Test if you can delete a flagged order state.
     * @return void
     */
    public function test_throw_on_deleting_flagged_state()
    {
        $state = OrderState::whereNotNull('flag')->first();

        if (method_exists($this, 'assertThrows')) {
            $this->assertThrows(fn () => $state->delete());
        } else {
            try {
                $value = $state->delete();
            } catch (Exception $exc) {
                $value = false;
            }
            $this->assertFalse($value);
        }
    }
}
