<?php

namespace VigStudio\VigAutoTranslations\Http\Models;

use Botble\Base\Models\BaseModel;

class VigTranslate extends BaseModel
{
    protected $table = 'vig_translations';

    protected $fillable = [
        'id',
        'text_original',
        'text_translated',
        'lang_from',
        'lang_to',
    ];
}
