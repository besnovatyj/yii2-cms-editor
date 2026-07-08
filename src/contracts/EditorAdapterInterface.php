<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Editor\contracts;

use Besnovatyj\Editor\EditorOptions;

/**
 * Контракт адаптера конкретного WYSIWYG-движка.
 *
 * Адаптер — переводчик: превращает нормализованные (общие) опция редактора
 * {@see EditorOptions} в конфиг конкретного виджета и сообщает его класс. Реализация
 * живёт в пакете самого редактора, поэтому фасад yii2-cms-editor не зависит ни от одного
 * движка — редакторы остаются подключаемыми плагинами.
 */
interface EditorAdapterInterface
{
    /**
     * FQCN виджета-редактора, которому фасад делегирует рендер
     * (yii\widgets\InputWidget-совместимый: принимает model/attribute либо name/value).
     */
    public function widgetClass(): string;

    /**
     * Переводит нормализованные опция в конфиг конкретного виджета.
     *
     * Возвращает только свойства движка (без model/attribute/options — их подставит фасад).
     * Опция со значением null адаптер обязан пропустить, чтобы не затирать дефолт виджета.
     * Неприменимые к движку опция (например, height у CKEditor5) адаптер просто игнорирует.
     *
     * @return array<string, mixed>
     */
    public function buildConfig(EditorOptions $options): array;
}
