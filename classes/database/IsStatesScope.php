<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Checks and implements the both states `is_default` and `is_enabled`.
 *
 * `is_default` ensures that there is (only) one as default marked model.
 * `is_enabled` adds additional scopes to only include `is_enabled` marked models.
 */
class IsStatesScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (method_exists($model, 'isEnabledQuery')) {
            $model->isEnabledQuery($builder);
        } elseif (($column = $model->getIsEnabledColumnName()) != null) {
            $builder->where($column, 1);
        }

        return $builder;
    }
    
    /**
     * Extend the query builder with the needed functions.
     * @param Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $this->addWithDisabled($builder);
        $this->addWithoutDisabled($builder);
    }

    /**
     * Add the with-disabled extension to the builder.
     * @param $builder
     * @return void
     */
    protected function addWithDisabled(Builder $builder)
    {
        $builder->macro('withDisabled', function (Builder $builder, $withDisabled = true) {
            if (!$withDisabled) {
                return $builder->addWithoutDisabled();
            } else {
                return $builder->withoutGlobalScope($this);
            }
        });
    }

    /**
     * Add the without-disabled extension to the builder.
     * @param Builder $builder
     * @return void
     */
    protected function addWithoutDisabled(Builder $builder)
    {
        $builder->macro('withoutDisabled', function (Builder $builder) {
            $builder->withoutGlobalScope($this);

            // Fetch Model
            $model = $builder->getModel();

            if (!$model->getUseEnabledConstant()) {
                return;
            }

            // Add Global Builder function
            if (method_exists($model, 'isEnabledQuery')) {
                $model->isEnabledQuery($builder);
            } elseif (($column = $model->getIsEnabledColumnName()) != null) {
                $builder->where($column, 1);
            }

            return $builder;
        });
    }
}
