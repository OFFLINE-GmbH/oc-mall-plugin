<?php namespace Offline\Mall\Models;

use Model;
use System\Models\File;

/**
 * SecureDownload Model
 */
class SecureDownload extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'offline_mall_secure_downloads';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'file' => [File::class], 
    ];
    public $attachMany = [];
}
