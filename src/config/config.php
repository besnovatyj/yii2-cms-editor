<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// Дефолты модуля-фасада редактора. Значение params.engine переопределяется через
// модуль настроек yii2-cms-config (опция объявлена в config/options.php) — иначе опции
// «не за что зацепиться» (см. комментарий в yii2-cms-file/config/options.php).
return [
    'id'     => 'Editor',
    'params' => [
        'iconClass' => 'bi bi-pencil-square',

        /**
         * Активный WYSIWYG-движок. Значение — ключ из карты 'adapters' ниже.
         * Читается фасадом: Yii::$app->getModule('Editor')->params['engine'].
         */
        'engine' => 'jodit',

        /**
         * Карта «движок => FQCN адаптера».
         *
         * Классы указаны строками намеренно: адаптеры живут в пакетах самих редакторов
         * (Jodit — в yii2-cms-jodit, CKEditor5 — в yii2-cms-file рядом с «батарейной» обёрткой).
         * Пока пакет редактора не установлен, строка безвредна (автозагрузку не триггерит),
         * а фасад проверяет class_exists перед инстанцированием. Добавить свой редактор =
         * дописать сюда строку и поставить пакет с адаптером — формы модулей не трогаются.
         */
        'adapters' => [
            'jodit'     => 'Besnovatyj\\Jodit\\adapters\\JoditEditorAdapter',
            'ckeditor5' => 'Besnovatyj\\File\\adapters\\CkeditorEditorAdapter',
        ],

        'directories' => false,
    ],
];
