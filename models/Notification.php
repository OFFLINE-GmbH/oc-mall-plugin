<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Cache;
use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Facades\Schema;

class Notification extends Model
{
    use Validation;
    use Sortable;

    const CACHE_KEY = 'mall.enabled.notifications';
    public $table = 'offline_mall_notifications';
    public $rules = [
        'name'     => 'required',
        'code'     => 'required|unique:offline_mall_notifications,code',
        'template' => 'required',
    ];
    public $casts = [
        'enabled' => 'boolean',
    ];
    public $fillable = [
        'enabled',
        'code',
        'name',
        'description',
        'template',
    ];

    public static function getEnabled()
    {
        if ( ! Schema::hasTable('offline_mall_notifications')) {
            return collect([]);
        }

        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Notification::where('enabled', true)->get()->pluck('template', 'code');
        });
    }

    public function afterSave()
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function beforeDeleting()
    {
        throw new \LogicException('OFFLINE.Mall: Notifications cannot be deleted.');
    }
}
