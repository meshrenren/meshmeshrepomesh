<?php
return [
    [
        'pattern' => 'member/user/<state>',
        'route' => 'member/index',
        'encodeParams' => false,
    ],
    [
        'pattern' => 'member/view/<member_id>',
        'route' => 'member/view',
        'encodeParams' => false,
    ],
];