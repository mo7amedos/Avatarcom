<?php

namespace VigStudio\VigSeo\Models;

use Botble\Base\Models\BaseModel;

class VigSeoTranslation extends BaseModel
{
    protected $table = 'vig_seos_translations';

    protected $fillable = [
        'lang_code',
        'vig_seos_id',
        'name',
    ];

    public $timestamps = false;
}
