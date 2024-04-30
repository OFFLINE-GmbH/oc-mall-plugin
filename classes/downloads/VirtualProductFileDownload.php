<?php declare(strict_types=1);

namespace OFFLINE\Mall\Classes\Downloads;

use Auth;
use Cms\Classes\Page;
use Flash;
use Request;
use Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\ProductFileGrant;

class VirtualProductFileDownload
{
    public function handle(string $key, ?string $idx = null)
    {
        $grant = $this->findGrant($key);
        if ( ! $grant) {
            return response($this->trans('invalid'), 404);
        }

        // Fetch the Product model via the grant relationships for easier access.
        $product = $grant->order_product->product;

        // Redirect the user to the login page if a session is required but
        // no user is logged in.
        if ($product->file_session_required && ! Auth::getUser()) {
            return $this->redirectToLogin();
        }

        // Make sure there are still download attempts left.
        if ($grant->max_download_count > 0 && $grant->download_count >= $grant->max_download_count) {
            return response($this->trans('too_many_attempts'), 403);
        }

        // Check the download link for expiration.
        if ($grant->expires_at && $grant->expires_at->lt(now())) {
            return response($this->trans('expired'), 403);
        }

        // Get File 
        if (!empty($idx)) {
            $file = $product->files->where('id', $idx)->first();
        } else {
            $file = $grant->file ? $grant->file : $product->latest_file;
        }

        // If no file is around, return and log an error. The site admin needs to fix this!
        if (empty($file)) {
            Log::error(
                '[OFFLINE.Mall] A virtual product without a file attachment has been purchased. You need to fix this!',
                ['grant' => $grant, 'product' => $product, 'user' => Auth::getUser()]
            );
            return response($this->trans('not_found'), 500);
        }

        // Increase the download counter, then send the file as a response.
        $grant->increment('download_count');
        $file->increment('download_count');

        // Download File
        $filename = sprintf('%s.%s', $file->display_name, $file->file->getExtension());
        $filename = urlencode($filename);
        return response()->download($file->file->getLocalPath(), $filename);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function findGrant(string $key)
    {
        $key = base64_decode(urldecode($key));

        return ProductFileGrant::where('download_key', $key)
                               ->with('order_product.product.latest_file')
                               ->first();
    }

    /**
     * Store the current request url in the session
     * and redirect the user to the login page.
     *
     * @return mixed
     */
    protected function redirectToLogin()
    {
        Session::put('mall.login.redirect', Request::url());
        Flash::warning(trans('offline.mall::frontend.session.login_required'));

        $url = Page::url(GeneralSettings::get('account_page'));

        return Redirect::to($url);
    }

    /**
     * Simple translation helper method.
     *
     * @param string $key
     *
     * @return string
     */
    protected function trans(string $key)
    {
        return trans('offline.mall::lang.product_file.errors.' . $key);
    }
}
