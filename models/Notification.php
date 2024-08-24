<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Illuminate\Support\Facades\Cache;
use LogicException;
use Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use Schema;

class Notification extends Model
{
    use Validation;
    use Sortable;

    public const CACHE_KEY = 'mall.enabled.notifications';

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
        if (! Schema::hasTable('offline_mall_notifications')) {
            return collect([]);
        }

        return Cache::rememberForever(self::CACHE_KEY, fn () => Notification::where('enabled', true)->get()->pluck('template', 'code'));
    }

    public function afterSave()
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function beforeDeleting()
    {
        throw new LogicException('OFFLINE.Mall: Notifications cannot be deleted.');
    }
}
