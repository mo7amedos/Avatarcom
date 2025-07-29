<?php

return [
    [
        'name' => 'Vig seos',
        'flag' => 'vig-seo.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'vig-seo.create',
        'parent_flag' => 'vig-seo.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'vig-seo.edit',
        'parent_flag' => 'vig-seo.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'vig-seo.destroy',
        'parent_flag' => 'vig-seo.index',
    ],
];
