<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Editor;

/**
 * Нормализованные (общие для всех движков) опция редактора.
 *
 * Узкий набор того, что реально пересекается между реализациями. Всё специфичное для
 * конкретного движка задаётся через escape hatch {@see EditorWidget::$engineConfig}.
 * Значение null означает «использовать дефолт движка» — адаптер такую опцию пропускает.
 */
final readonly class EditorOptions
{
    public function __construct(
        /** Язык интерфейса редактора (ISO-код локали). */
        public ?string $language = null,
        /** Высота области редактирования (px или CSS-значение). Не все движки поддерживают. */
        public int|string|null $height = null,
        /** Текст-приглашение в пустом редакторе. */
        public ?string $placeholder = null,
        /** Стартовая директория файлового менеджера (mount-префиксный виртуальный путь). */
        public ?string $fmDefaultPath = null,
        /** Включать ли интеграцию файлового менеджера. */
        public ?bool $enableFileManager = null,
        /** Включать ли интеграцию пикера сниппетов. */
        public ?bool $enableSnippets = null,
    ) {
    }
}
