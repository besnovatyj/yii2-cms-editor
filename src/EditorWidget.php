<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Editor;

use Besnovatyj\Editor\contracts\EditorAdapterInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\InputWidget;
use yii\helpers\ArrayHelper;

/**
 * Виджет-фасад WYSIWYG-редактора.
 *
 * Единая точка подключения редактора во всех формах проекта: модули вызывают этот виджет и
 * НЕ знают, какой движок под капотом. Активный движок берётся из настроек
 * (Yii::$app->getModule('Editor')->params['engine'], управляется yii2-cms-config), фасад
 * резолвит его адаптер по карте params['adapters'] и делегирует рендер конкретному виджету.
 *
 * Смена редактора по всему проекту = смена одной опции в админке. Добавление нового =
 * новый пакет с адаптером + строка в карте адаптеров; формы модулей не трогаются.
 *
 * Пример:
 * ```php
 * echo $form->field($model, 'content')->widget(EditorWidget::class, [
 *     'height' => 500,
 *     'fmDefaultPath' => '/static/origin/Blog/' . $post->id,
 *     // сырые переопределения конкретного движка (применится только активный ключ):
 *     'engineConfig' => ['jodit' => ['buttons' => [...]]],
 * ]);
 * ```
 */
class EditorWidget extends InputWidget
{
    /** Id модуля-фасада (источник настроек engine/adapters). */
    public const string MODULE_ID = 'Editor';

    /** Движок по умолчанию, если модуль/настройка недоступны. */
    public const string DEFAULT_ENGINE = 'jodit';

    // --- Нормализованные (общие) свойства. null — использовать дефолт движка. ---

    /** @see EditorOptions::$language */
    public ?string $language = 'ru';
    /** @see EditorOptions::$height */
    public int|string|null $height = null;
    /** @see EditorOptions::$placeholder */
    public ?string $placeholder = null;
    /** @see EditorOptions::$fmDefaultPath */
    public ?string $fmDefaultPath = null;
    /** @see EditorOptions::$enableFileManager */
    public ?bool $enableFileManager = null;
    /** @see EditorOptions::$enableSnippets */
    public ?bool $enableSnippets = null;

    /**
     * Явное переопределение движка в обход настройки (id из карты адаптеров).
     * null — брать активный движок из настроек. Нужно редко (например, форма требует
     * конкретный редактор независимо от глобального выбора).
     */
    public ?string $engine = null;

    /**
     * Escape hatch: сырые, специфичные для движка переопределения конфига,
     * ключёванные по id движка — ['jodit' => [...], '<другой движок>' => [...]].
     * Применяется только секция активного движка (поверх результата адаптера).
     * @var array<string, array<string, mixed>>
     */
    public array $engineConfig = [];

    /**
     * Резолвит движок и его адаптер, собирает конфиг и делегирует рендер конкретному виджету.
     *
     * @throws InvalidConfigException если движок не сконфигурирован, адаптер не найден
     *                                или не реализует контракт.
     */
    public function run(): string
    {
        $engine  = $this->resolveEngine();
        $adapter = $this->resolveAdapter($engine);

        $options = new EditorOptions(
            language: $this->language,
            height: $this->height,
            placeholder: $this->placeholder,
            fmDefaultPath: $this->fmDefaultPath,
            enableFileManager: $this->enableFileManager,
            enableSnippets: $this->enableSnippets,
        );

        // Конфиг адаптера + сырые переопределения активного движка.
        $config = ArrayHelper::merge(
            $adapter->buildConfig($options),
            $this->engineConfig[$engine] ?? [],
        );

        $config['options'] = ArrayHelper::merge($config['options'] ?? [], $this->options);

        // Прокидываем привязку к данным ровно так, как её получил сам фасад.
        if ($this->hasModel()) {
            $config['model']     = $this->model;
            $config['attribute'] = $this->attribute;
        } else {
            $config['name']  = $this->name;
            $config['value'] = $this->value;
        }

        // Делегируем рендер конкретному виджету. ::widget() вызываем именно на его классе:
        // базовый Widget::widget() принудительно выставляет class = get_called_class(), поэтому
        // вызов на нужном классе даёт нужный тип (а не yii\base\Widget). Задавать $config['class']
        // бессмысленно — он всё равно будет перезаписан.
        $widgetClass = $adapter->widgetClass();

        return $widgetClass::widget($config);
    }

    /**
     * Активный движок: явное свойство > настройка модуля > дефолт.
     */
    private function resolveEngine(): string
    {
        if ($this->engine !== null && $this->engine !== '') {
            return $this->engine;
        }

        $engine = $this->moduleParams()['engine'] ?? null;

        return is_string($engine) && $engine !== '' ? $engine : self::DEFAULT_ENGINE;
    }

    /**
     * Инстанцирует адаптер движка по карте настроек (через DI-контейнер фреймворка).
     *
     * @throws InvalidConfigException
     */
    private function resolveAdapter(string $engine): EditorAdapterInterface
    {
        $map          = $this->moduleParams()['adapters'] ?? [];
        $adapterClass = $map[$engine] ?? null;

        if (!is_string($adapterClass) || $adapterClass === '') {
            throw new InvalidConfigException("Editor: для движка '{$engine}' не задан адаптер (params.adapters).");
        }

        if (!class_exists($adapterClass)) {
            throw new InvalidConfigException(
                "Editor: адаптер '{$adapterClass}' движка '{$engine}' не найден — пакет редактора не установлен?",
            );
        }

        $adapter = Yii::createObject($adapterClass);

        if (!$adapter instanceof EditorAdapterInterface) {
            throw new InvalidConfigException(
                "Editor: '{$adapterClass}' должен реализовывать " . EditorAdapterInterface::class . '.',
            );
        }

        return $adapter;
    }

    /**
     * params модуля-фасада (пусто, если модуль не подключён — тогда работают дефолты фасада).
     *
     * @return array<string, mixed>
     */
    private function moduleParams(): array
    {
        return Yii::$app->getModule(self::MODULE_ID)?->params ?? [];
    }
}
