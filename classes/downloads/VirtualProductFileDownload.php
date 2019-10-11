<?php

namespace OFFLINE\Mall\Classes\Downloads;

use Auth;
use Cms\Classes\Page;
use File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\ProductFileGrant;

class VirtualProductFileDownload
{
    public function handle(string $key)
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

        // Increase the download counter, then send the file as a response.
        $grant->increment('download_count');
        $product->latest_file->increment('download_count');

        // If the grant has a custom storage path, send this file.
        if ($grant->storage_path) {
            if ( ! File::exists($grant->storage_path) || ! File::isFile($grant->storage_path)) {
                Log::error('[OFFLINE.Mall] Product file could not be found',
                    ['path' => $grant->storage_path, 'grant' => $grant]);

                return response($this->trans('not_found'), 500);
            }

            return File::get($grant->storage_path);
        }

        // If no custom storage path is specified, return the file uploaded via the backend.
        return $product->latest_file->file->output();
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

    protected function trans(string $key)
    {
        return trans('offline.mall::lang.product_file.errors.' . $key);
    }
}