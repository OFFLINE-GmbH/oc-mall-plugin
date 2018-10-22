<?php

namespace OFFLINE\Mall\Classes\Traits;

use Illuminate\Support\Collection;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldValue;
use Validator;

trait CustomFields
{
    /**
     * Returns the product's base price with all CustomFieldValue
     * prices added.
     *
     * @param CustomFieldValue[] $values
     *
     * @return array
     */
    public function priceIncludingCustomFieldValues(?Collection $values = null): array
    {
        $currencies = Currency::get();
        if ( ! $values || count($values) < 1) {
            return $currencies->mapWithKeys(function (Currency $currency) {
                return [$currency->code => $this->price($currency)->integer];
            })->toArray();
        }

        $price = $this->price()->integer;

        return $currencies->mapWithKeys(function (Currency $currency) use ($values, $price) {
            return [
                $currency->code =>
                    $price + $values->sum(function (CustomFieldValue $value) use ($currency, $price) {
                        $prices = $value->priceForFieldOption();

                        return optional($prices->where('currency_id', $currency->id)->first())
                            ->integer;
                    }),
            ];
        })->toArray();
    }

    /**
     * Validate the entered custom field data.
     *
     * @param array $values
     *
     * @return array|Collection|static
     * @throws ValidationException
     */
    protected function validateCustomFields(array $values)
    {
        $values = collect($values)->mapWithKeys(function ($value, $id) {
            return [$this->decode($id) => $value];
        });

        $fields = CustomField::with('custom_field_options')
                             ->whereIn('id', $values->keys())
                             ->get()
                             ->mapWithKeys(function (CustomField $field) use ($values) {
                                 $value = $values->get($field->id);
                                if (\in_array($field->type, ['dropdown', 'image'], true)) {
                                    $value = $this->decode($value);
                                }

                                 return [$field->id => ['field' => $field, 'value' => $value]];
                             });

        $rules = $fields->mapWithKeys(function (array $data) {
            $field = $data['field'];

            $rules = collect();
            if ($field->required) {
                $rules->push('required');
            }
            if (\in_array($field->type, ['dropdown', 'image'], true)) {
                $rules->push('in:' . $field->custom_field_options->pluck('id')->implode(','));
            }
            if ($field->type === 'color') {
                if ($field->custom_field_options->count() < 1) {
                    $rules->push('size:7');
                    $rules->push('regex:/^\#[0-9A-Fa-f]{6}$/');
                } else {
                    $rules->push('in:' . $field->custom_field_options->map->value->pluck('color')->implode(','));
                }
            }

            return [$field->name => $rules];
        })->filter();

        $data = $fields->mapWithKeys(function (array $data) {
            return [$data['field']->name => $data['value']];
        });

        $v = Validator::make($data->toArray(), $rules->toArray());
        if ($v->fails()) {
            throw new ValidationException($v);
        }

        $values = $fields->map(function (array $data) {
            if ( ! $data['value']) {
                return;
            }

            $option = $data['field']->custom_field_options->find($data['value']);

            $value                         = new CustomFieldValue();
            $value->value                  = $data['value'];
            $value->custom_field_id        = $data['field']->id;
            $value->custom_field_option_id = $option ? $option->id : null;
            $value->price                  = $value->priceForFieldOption($data['field'], $option);

            return $value;
        });

        return $values;
    }
}
