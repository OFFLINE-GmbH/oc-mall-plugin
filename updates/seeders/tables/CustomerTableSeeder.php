<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomerGroup;
use RainLab\Location\Models\Country;
use RainLab\Location\Models\State;
use RainLab\User\Models\User;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param bool $useDemo
     * @return void
     */
    public function run(bool $useDemo = false)
    {
        if (!$useDemo && config('app.env') != 'testing') {
            return;
        }

        config()->set('rainlab.user::minPasswordLength', 8);

        $this->createUser(
            'normal_customer@example.tld',
            trans('offline.mall::demo.customers.normal'),
        );

        $this->createUser(
            'gold_customer@example.tld',
            trans('offline.mall::demo.customers.gold'),
            CustomerGroup::where('code', 'gold')->first()->id
        );

        $this->createUser(
            'diamond_customer@example.tld',
            trans('offline.mall::demo.customers.diamond'),
            CustomerGroup::where('code', 'diamond')->first()->id
        );
    }

    /**
     * Create new user.
     * @param string $email
     * @param string $name
     * @param integer|null $customerGroupId
     * @return void
     */
    protected function createUser(string $email, string $name, ?int $customerGroupId = null)
    {
        [$firstname, $lastname] = explode(' ', $name);

        $args = [
            'password'                  => '12345678',
            'password_confirmation'     => '12345678',
        ];

        $fillable = (new User())->getFillable();

        if (in_array('surname', $fillable)) {
            $args['name'] = $firstname;
            $args['surname'] = $lastname;
        } else {
            $args['first_name'] = $firstname;
            $args['last_name'] = $lastname;
        }

        $user = User::firstOrCreate([
            'email'                     => $email,
            'username'                  => $email,
        ], $args);

        $user->offline_mall_customer_group_id = $customerGroupId;
        $user->save();

        $customer = Customer::create([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'user_id'   => $user->id,
        ]);

        $shippingAddress = Address::create([
            'name'          => $name,
            'lines'         => 'Street 12',
            'zip'           => '6000',
            'city'          => 'Lucerne',
            'state_id'      => State::where('name', 'Luzern')->first()->id,
            'country_id'    => Country::where('code', 'CH')->first()->id,
            'customer_id'   => $customer->id,
        ]);
        $customer->addresses()->save($shippingAddress);

        $billingAddress = Address::create([
            'name'          => $name,
            'lines'         => 'Street 12',
            'zip'           => '6000',
            'city'          => 'Lucerne',
            'state_id'      => State::where('name', 'Luzern')->first()->id,
            'country_id'    => Country::where('code', 'CH')->first()->id,
            'customer_id'   => $customer->id,
        ]);
        $customer->addresses()->save($billingAddress);
    }
}
