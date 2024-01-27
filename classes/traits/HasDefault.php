<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Traits;

/**
 * HasDefault ensures that there is (only) one as default marked model.
 */
trait HasDefault
{
    /**
     * Initialize model trait
     */
    public function initializeHasDefault()
    {
        $this->bindEvent('model.beforeSave', function () {
            $defaultColumn = $this->getIsDefaultColumnName();
            $enabledColumn = $this->getIsEnabledColumnName();
            $isEnabled = $enabledColumn == null ? true : $this->$enabledColumn !== false;
            
            // Disabled models cannot be the default ones.
            if (!$isEnabled && $this->$defaultColumn) {
                $this->$defaultColumn = false;
            }

            // Enforce a single default model.
            if ($this->$defaultColumn) {
                static::where('id', '<>', $this->id)->update([$defaultColumn => false]);
            }

            // Enforce a default model.
            if ($this->exists && !$this->$defaultColumn && $this->isDirty($defaultColumn)) {
                $count = static::where('id', '<>', $this->id)->where($defaultColumn, true)->count();
                if ($count === 0) {
                    $default = static::enabled()->where('id', '<>', $this->id)->first();

                    if (empty($default)) {
                        throw new \Exception('No model could be defined as default one instead.');
                    } else {
                        $default->$defaultColumn = true;
                        $default->save();
                    }
                }
            }
        });
    }

    /**
     * Custom scope to retrieve only enabled currencies.
     * @return mixed
     */
    public function scopeEnabled($query)
    {
        if (($column = $this->getIsEnabledColumnName()) != null) {
            $query->where($column, 1);
        }
        return $query;
    }

    /**
     * Get the name of the 'is_default' column.
     * @return null|string
     */
    public function getIsDefaultColumnName(): string|null
    {
        /** @ignore @disregard model constant */
        return defined('static::IS_DEFAULT') ? static::IS_DEFAULT : 'is_default';
    }

    /**
     * Get the name of the 'is_enabled' column or null if disabled manually.
     * @return null|string
     */
    public function getIsEnabledColumnName(): string|null
    {
        /** @ignore @disregard model constant */
        return defined('static::IS_ENABLED') ? static::IS_ENABLED : 'is_enabled';
    }
}
