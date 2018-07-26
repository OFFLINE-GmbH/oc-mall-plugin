<?php namespace OFFLINE\Mall\Components;

use Auth;
use October\Rain\Exception\ValidationException;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use RainLab\User\Models\UserGroup;
use Validator;

class CustomerProfile extends MallComponent
{
    public $user;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.customerProfile.details.name',
            'description' => 'offline.mall::lang.components.customerProfile.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        $this->user = Auth::getUser();
    }

    public function onSubmit()
    {
        $data    = post();
        $handler = app(SignUpHandler::class);

        $neededRules = array_only($handler::rules(false), [
            'firstname',
            'lastname',
            'email',
            'password',
            'password_repeat',
        ]);
        if ($data['password'] === '') {
            // The password is unchanged so we don't need to validate it.
            unset($neededRules['password'], $neededRules['password_repeat']);
        }

        $validation = Validator::make($data, $neededRules, $handler::messages());
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        \DB::transaction(function () use ($data) {
            $this->user->customer->firstname = $data['firstname'];
            $this->user->customer->lastname  = $data['lastname'];
            $this->user->email               = $data['email'];
            if ($data['password']) {
                $this->user->password              = $data['password'];
                $this->user->password_confirmation = $data['password_repeat'];
                $this->user->customer->is_guest    = false;

                $this->user->groups()->detach(UserGroup::getGuestGroup());
            }
            $this->user->save();
            $this->user->customer->save();
        });

        // Re-authenticate the user with his new credentials
        Auth::login($this->user);

        Flash::success(trans('offline.mall::lang.common.saved_changes'));
    }
}
