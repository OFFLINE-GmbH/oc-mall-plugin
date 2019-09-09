<?php namespace Offline\Mall\Controllers;

use BackendMenu;

use Cms\Classes\Controller;
use RainLab\User\Facades\Auth;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\SecureDownload;
use OFFLINE\Mall\Models\GeneralSettings;
use Flash;
use Redirect;
use Response;

use Carbon\Carbon;
use Carbon\CarbonInterval;
/**
 * Secure Download Controller Back-end Controller
 */
class SecureDownloadController extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    /**
     * Download secure file secure with auth.
     *
     * @param [int] $id_file
     * @param [int] $id_order
     * @return Response
     */
    public function download($id_file, $id_order)
    {
        
        if(!Auth::check())
        {
           return $this->redirectWithError();
        }

        $order = Order::findOrFail($id_order);
        $download = SecureDownload::findOrFail($id_file);

        $user = Auth::getUser();
        if($user->id != $order->customer_id)
        {
            return $this->redirectWithError();
        }
        if(!$order->isPaid)
        {
            return $this->redirectWithError();
        }

        $ids = $order->products->pluck('product_id')->all();

        if($ids && in_array($download->product_id,$ids))
        {
            // check limite date
            if($download->limite_date > 0)
            {
                $current_date   = Carbon::now();
                $order_date     = Carbon::parse($order->created_at);
                $date_limit     = $order_date->add(CarbonInterval::days($download->limite_date));
                // date is passed
                if($current_date->greaterThan($date_limit))
                {
                    return $this->redirectWithError('offline.mall::lang.common.date_passed');
                }
            }
            return Response::download($download->file->output());
        }
    }
    /**
     * Redirect message error to customer account
     *
     * @param string $message
     * @return Redirect
     */
    public function redirectWithError($message = 'offline.mall::lang.common.please_log_in')
    {
        Flash::error(trans($message));
        $accountPage    = GeneralSettings::get('account_page');
        $ctrl           = new Controller();
        return Redirect::to($ctrl->pageUrl($accountPage));
    }


}
