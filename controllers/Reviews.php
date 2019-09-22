<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\Backend;
use BackendMenu;
use Illuminate\Support\Facades\Redirect;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Models\Review;

class Reviews extends Controller
{
    public $implement = ['Backend\Behaviors\ListController', 'Backend\Behaviors\FormController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_reviews',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-reviews');
    }

    public function onApprove()
    {
        Review::findOrFail(post('id'))->approve();

        $next = Review::orderBy('created_at')->whereNull('approved_at')->first(['id']);

        if ($next) {
            return Redirect::to(Backend::url('offline/mall/reviews/update/' . $next->id));
        }

        Flash::success(trans('offline.mall::lang.reviews.no_more'));

        return Redirect::to(Backend::url('offline/mall/reviews'));
    }
}
