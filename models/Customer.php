<?php namespace OFFLINE\Mall\Models;

use Carbon\Carbon;
use DB;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use RainLab\User\Models\User;

class Customer extends Model
{
    use Validation;
    use SoftDelete;

    protected $dates = ['deleted_at'];
    protected $casts = [
        'is_guest' => 'boolean',
    ];
    public $rules = [
        'firstname' => 'required',
        'lastname'  => 'required',
        'is_guest'  => 'boolean',
        'user_id'   => 'required|exists:users,id',
    ];
    public $table = 'offline_mall_customers';
    public $belongsTo = [
        'user' => User::class,
    ];
    public $hasMany = [
        'addresses' => Address::class,
        'orders'    => Order::class,
    ];

    public function getNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function afterDelete()
    {
        $this->addresses->each->delete();
        $this->orders->each->delete();
    }

    /**
     * Cleanup of old data using OFFLINE.GDPR.
     *
     * @see https://github.com/OFFLINE-GmbH/oc-gdpr-plugin
     *
     * @param Carbon $deadline
     * @param int    $keepDays
     */
    public function gdprCleanup(Carbon $deadline, int $keepDays)
    {
        User::where('last_seen', '<', $deadline)->get()->each(function (User $user) {
            DB::transaction(function () use ($user) {
                Customer::withTrashed()->where('user_id', $user->id)->forceDelete();
                $user->delete();
            });
        });
    }
}
