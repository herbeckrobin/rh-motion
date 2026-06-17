<?php

declare(strict_types=1);

namespace RhMotion;

use RhBlueprint\Core\Core;
use RhBlueprint\Core\Settings\SettingsPage;
use RhMotion\Admin\MotionGroup;

/**
 * Bootstrap von rh-motion.
 *
 * Hängt am Core-Hook `rh-blueprint/core/booted` (feuert auf `init`). Registriert
 * die Settings im Tab "Animationen" und bootet die Motion-Mechanik. Braucht nur
 * den Core, keine db-engine.
 */
final class Plugin
{
    public static function boot(): void
    {
        if (class_exists(UpdateChecker::class)) {
            (new UpdateChecker())->boot();
        }

        add_action('rh-blueprint/core/booted', [self::class, 'onCoreBooted']);
    }

    public static function onCoreBooted(Core $core): void
    {
        $core->settings()->registerTab('motion', __('Animationen', 'rh-motion'), 50);
        $core->settings()->registerGroup(new MotionGroup());

        (new Motion())->boot();

        add_filter('rh-blueprint/dashboard/quick_links', static function (array $links): array {
            $links[] = [
                'label' => __('Animationen', 'rh-motion'),
                'url' => admin_url('admin.php?page=' . SettingsPage::MENU_SLUG . '&tab=motion'),
                'icon' => 'controls-play',
            ];
            return $links;
        });
    }
}
