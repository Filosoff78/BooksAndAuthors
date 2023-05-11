<?php
return [
    'intranet.customSection' => [
        'value' => [
            'provider' => '\\RSB\\Books\\Integration\\Intranet\\CustomSectionProvider',
        ],
    ],
    'ui.entity-selector' => [
        'value' => [
            'entities' => [
                [
                    'entityId' => 'authors',
                    'provider' => [
                        'moduleId' => 'rsb.books',
                        'className' =>'\\RSB\\Books\\Integration\\UI\\EntitySelector\\AuthorsProvider'
                    ],
                ],
            ]
        ],
        'readonly' => true,
    ],
];
