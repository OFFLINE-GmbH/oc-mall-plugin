<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Factories;

use Illuminate\Database\Eloquent\Factory;
//use October\Rain\Database\Factories\Factory;
use OFFLINE\Mall\Models\Address;
use RainLab\Location\Models\Country;

/**
 * AddressFactory
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     * @var string
     */
    protected $model = Address::class;

    /**
     * Factory definition for the default state.
     * @return array<string, mixed>
     */
    public function definition()
    {
        $country = Country::inRandomOrder()->whereHas('states')->get()->first();
        $state = $country->states()->inRandomOrder()->get()->first();

        return [
            'company'       => $this->faker->company(),
            'name'          => $this->faker->name(),
            'lines'         => $this->faker->streetAddress(),
            'zip'           => $this->faker->postcode(),
            'city'          => $this->faker->city(),
            'state_id'      => $state->id,
            'country_id'    => $country->id,
            'details'       => null,
            'customer_id'   => 1,
            'created_at'    => $this->faker->iso8601(),
            'updated_at'    => $this->faker->iso8601(),
            'deleted_at'    => null,
        ];
    }
}
