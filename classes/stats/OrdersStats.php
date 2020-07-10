<?php

namespace OFFLINE\Mall\Classes\Stats;


use DB;
use Illuminate\Support\Carbon;
use OFFLINE\Mall\Classes\PaymentState\PaidState;
use OFFLINE\Mall\Classes\PaymentState\PaymentState;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\OrderState;

class OrdersStats
{
    protected $ordersTable;
    protected $statesTable;
    protected $cancelledStateId;

    public function __construct()
    {
        $this->ordersTable      = (new Order())->table;
        $this->statesTable      = (new OrderState())->table;
        $this->cancelledStateId = optional(OrderState::where('flag', OrderState::FLAG_CANCELLED)->first())->id;
    }

    public function count(): int
    {
        return DB::table($this->ordersTable)
            ->whereNull('deleted_at')
            ->count();
    }

    public function perWeekCount(): float
    {
        $firstOrder = DB::table($this->ordersTable)
                        ->where('order_state_id', '<>', $this->cancelledStateId)
                        ->whereNull('deleted_at')
                        ->orderBy('created_at', 'ASC')
                        ->first(['created_at']);
        if ( ! $firstOrder) {
            return 0;
        }

        $weeks = Carbon::createFromFormat('Y-m-d H:i:s', $firstOrder->created_at)->diffInWeeks(today());
        if ($weeks < 1) {
            return $this->count();
        }

        return round($this->count() / $weeks, 2);
    }

    public function grandTotal(): int
    {
        return DB::table($this->ordersTable)
                 ->where('payment_state', PaidState::class)
                 ->where('order_state_id', '<>', $this->cancelledStateId)
                 ->whereNull('deleted_at')
                 ->sum('total_pre_payment');
    }

    public function byState(): array
    {
        return DB::table($this->ordersTable)
                 ->whereNull($this->ordersTable . '.deleted_at')
                 ->leftJoin($this->statesTable, "{$this->ordersTable}.order_state_id", '=', "{$this->statesTable}.id")
                 ->select(
                     "{$this->statesTable}.name as label",
                     "{$this->statesTable}.color",
                     DB::raw('count(order_state_id) as value')
                 )
                 ->groupBy("{$this->ordersTable}.order_state_id", "{$this->statesTable}.name", "{$this->statesTable}.color")
                 ->get()
                 ->toArray();

    }

    public function byPaymentState(): array
    {
        return DB::table($this->ordersTable)
                 ->whereNull($this->ordersTable . '.deleted_at')
                 ->select('payment_state', DB::raw('count(payment_state) as value'))
                 ->groupBy('payment_state')
                 ->get()
                 ->map(function ($row) {
                     /** @var PaymentState $inst */
                     $inst = $row->payment_state;

                     return (object)[
                         'color' => $inst::color(),
                         'value' => $row->value,
                         'label' => $inst::label(),
                     ];
                 })
                 ->toArray();
    }
}
