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
        $fields = $this->mapToCustomFields($values);

        $rules = $fields->mapWithKeys(function (array $data) {
            $field = $data['field'];

            $rules = collect();
            if ($field->required) {
                $rules->push('required');
            }
            if (\in_array($field->type, ['dropdown', 'image', 'color'], true)) {
                if ( ! $field->required) {
                    $rules->push('nullable');
                }
                // If this is a color field without any predefined options we accept any hex value.
                if ($field->type === 'color' && $field->custom_field_options->count() === 0) {
                    $rules->push('regex:/\#[0-9a-f]{6}/i');
                } else {
                    $rules->push('in:' . $field->custom_field_options->pluck('id')->implode(','));
                }
            }

            return [$field->hashId => $rules];
        })->filter();

        $data  = $fields->mapWithKeys(function (array $data) {
            return [$data['field']->hashId => $data['value']];
        });
        $names = $fields->mapWithKeys(function (array $data) {
            return [$data['field']->hashId => $data['field']->name];
        })->toArray();

        $v = Validator::make($data->toArray(), $rules->toArray(), [], $names);
        if ($v->fails()) {
            throw new ValidationException($v);
        }

        return $this->mapToCustomFieldValues($fields);
    }

    /**
     * Map input data to CustomField models.
     *
     * @param array $values
     *
     * @return Collection<CustomField>
     */
    protected function mapToCustomFields(array $values)
    {
        $values = collect($values)->mapWithKeys(function ($value, $id) {
            return [$this->decode($id) => $value];
        });

        return CustomField::with('custom_field_options')
                          ->whereIn('id', $values->keys())
                          ->get()
                          ->mapWithKeys(function (CustomField $field) use ($values) {
                              $value = $values->get($field->id);
                              if (\in_array($field->type, ['dropdown', 'image', 'color'], true)) {
                                  if ($field->type !== 'color' || $field->custom_field_options->count() > 0) {
                                      $value = $this->decode($value);
                                  }
                              }

                              return [$field->id => ['field' => $field, 'value' => $value]];
                          });
    }

    /**
     * Map CustomField input collection to CustomFieldValue models.
     *
     * @param Collection $fields
     *
     * @return Collection<CustomFieldValue>
     */
    protected function mapToCustomFieldValues(Collection $fields)
    {
        return $fields->filter(function ($data) {
            return $data['value'];
        })->map(function (array $data) {
            $option = $data['field']->custom_field_options->find($data['value']);

            $value                         = new CustomFieldValue();
            $value->value                  = $data['value'];
            $value->custom_field_id        = $data['field']->id;
            $value->custom_field_option_id = $option ? $option->id : null;
            $value->price                  = $value->priceForFieldOption($data['field'], $option);

            return $value;
        });
    }
}
