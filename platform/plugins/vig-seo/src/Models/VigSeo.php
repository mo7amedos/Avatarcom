<?php

namespace VigStudio\VigSeo\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\EnumCastable;

class VigSeo extends BaseModel
{
    use EnumCastable;

    protected $table = 'vig_seos';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
}
