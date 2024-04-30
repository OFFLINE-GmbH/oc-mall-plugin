<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Database;

/**
 * Checks and implements the both states `is_default` and `is_enabled`.
 * 
 * `is_default` ensures that there is (only) one as default marked model.
 * `is_enabled` adds additional scopes to only include `is_enabled` marked models.
 */
trait IsStates
{
    /**
     * Initialize model trait
     * @return void
     */
    static public function bootIsStates()
    {
        static::addGlobalScope(new IsStatesScope);
    }

    /**
     * Initialize model trait
     * @return void
     */
    public function initializeIsStates()
    {
        $this->bindEvent('model.beforeSave', [$this, 'onCheckIsDefaultStateBeforeSave']);
    }

    /**
     * Receive default model
     * @return null|self
     */
    static public function default(): ?self
    {
        $defaultColumn = (new self)->getIsDefaultColumnName();
        if (empty($defaultColumn)) {
            return null; // is_default behavior has been disabled by the model.
        } else {
            return self::where('is_default', 1)->first();
        }
    }

    /**
     * Check 'is_default' state before saving model.
     * @return void
     */
    protected function onCheckIsDefaultStateBeforeSave()
    {
        $defaultColumn = $this->getIsDefaultColumnName();
        if (empty($defaultColumn)) {
            return; // is_default behavior has been disabled by the model.
        }

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
                $default = static::where('id', '<>', $this->id)->first();

                if (empty($default)) {
                    throw new \Exception('No model could be defined as default one instead.');
                } else {
                    $default->$defaultColumn = true;
                    $default->save();
                }
            }
        }
    }
    
    /**
     * Get the name of the 'is_default' column.
     * @return null|string
     */
    public function getIsDefaultColumnName(): ?string
    {
        /** @ignore @disregard model constant */
        return defined('static::IS_DEFAULT') ? static::IS_DEFAULT : 'is_default';
    }

    /**
     * Get the name of the 'is_enabled' column or null if disabled manually.
     * @return null|string
     */
    public function getIsEnabledColumnName(): ?string
    {
        /** @ignore @disregard model constant */
        return defined('static::IS_ENABLED') ? static::IS_ENABLED : 'is_enabled';
    }
}
