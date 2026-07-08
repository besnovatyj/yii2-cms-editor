<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Опция модуля настроек yii2-cms-config: выбор активного WYSIWYG-редактора для всего проекта.
// Значение применяется в Yii::$app->getModule('Editor')->params['engine'] (см. ConfigApplier),
// откуда его читает EditorWidget. range/items держать синхронно с картой 'adapters' в config.php.
return [
    'editor_engine' => [
        'path'        => 'modules.Editor.params.engine',
        'label'       => '[Editor] Активный WYSIWYG-редактор',
        'description' => "Yii::\$app->getModule('Editor')->params['engine']",
        'category'    => 'Editor',
        'rules'       => [
            ['required'],
            ['in', 'range' => ['jodit', 'ckeditor5']],
        ],
        'inputOptions' => [
            'type'  => 'dropdown',
            'items' => [
                'jodit'     => 'Jodit',
                'ckeditor5' => 'CKEditor 5',
            ],
        ],
    ],
];
