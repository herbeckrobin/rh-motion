<?php

declare(strict_types=1);

namespace RhMotion\Admin;

use RhBlueprint\Core\Settings\GroupInterface;
use RhBlueprint\Core\Settings\SettingField;

/**
 * Settings-Gruppe für die Animationen.
 *
 * Die eigentliche Steuerung passiert pro Block im Editor. Hier nur zwei
 * globale Schalter: ein Kill-Switch und das Erlauben der schwereren
 * Scroll-Effekte. Block-Whitelist und Animations-Set sind Entwickler-Sache
 * (PHP-Filter), darum nicht als Kunden-Setting.
 */
final class MotionGroup implements GroupInterface
{
    public const GROUP_ID = 'motion';

    public const FIELD_ENABLED = 'enabled';
    public const FIELD_SCROLL_EFFECTS = 'scroll_effects';

    public function id(): string
    {
        return self::GROUP_ID;
    }

    public function tab(): string
    {
        return 'motion';
    }

    public function title(): string
    {
        return __('Animationen', 'rh-motion');
    }

    public function description(): string
    {
        return __('Scroll-Animationen werden pro Block im Editor gewählt (rechts in der Seitenleiste). Hier nur die globalen Schalter.', 'rh-motion');
    }

    public function fields(): array
    {
        return [
            new SettingField(
                id: self::FIELD_ENABLED,
                type: SettingField::TYPE_BOOLEAN,
                label: __('Animationen aktivieren', 'rh-motion'),
                description: __('Globaler Schalter. Aus = keine Animationen im Frontend und keine Auswahl im Editor.', 'rh-motion'),
                default: true,
                keywords: ['animation', 'aktivieren', 'reveal', 'motion'],
            ),
            new SettingField(
                id: self::FIELD_SCROLL_EFFECTS,
                type: SettingField::TYPE_BOOLEAN,
                label: __('Scroll-Effekte erlauben', 'rh-motion'),
                description: __('Erlaubt die scroll-gekoppelten Effekte (Drehen, Parallax). Diese wirken kräftiger. Aus = nur Reveals und Loops.', 'rh-motion'),
                default: true,
                keywords: ['scroll', 'parallax', 'rotate', 'drehen'],
            ),
        ];
    }
}
