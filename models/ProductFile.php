<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use System\Models\File;

class ProductFile extends Model
{
    use Validation;

    public $table = 'offline_mall_product_files';
    public $rules = [
        'display_name'       => 'required',
        'version'            => 'required',
        'max_download_count' => 'nullable|integer',
        'expires_after_days' => 'nullable|integer',
        'session_required'   => 'nullable|boolean',
        'file'               => 'required',
    ];
    public $casts = [
        'max_download_count' => 'integer',
        'download_count'     => 'integer',
        'expires_after_days' => 'integer',
        'session_required'   => 'boolean',
    ];
    public $attachOne = [
        'file' => File::class,
    ];
    public $belongsTo = [
        'product' => Product::class,
    ];
}
