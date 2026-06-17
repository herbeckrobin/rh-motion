<?php

/**
 * Plugin Name:       RH Motion
 * Plugin URI:        https://github.com/herbeckrobin/rh-motion
 * Update URI:        https://github.com/herbeckrobin/rh-motion
 * Description:       Scroll-Animationen als per-Block-Attribut im Editor: Eingangs-Reveals, Dauer-Loops und Scroll-Effekte. FOUC-frei, respektiert prefers-reduced-motion. Teil der rh-blueprint Kollektion.
 * Version:           0.2.1
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Robin Herbeck
 * Author URI:        https://robinherbeck.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rh-motion
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('RHMOTION_VERSION', '0.2.1');
define('RHMOTION_PLUGIN_FILE', __FILE__);
define('RHMOTION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RHMOTION_PLUGIN_URL', plugin_dir_url(__FILE__));

$rhmotion_autoload = RHMOTION_PLUGIN_DIR . 'vendor/autoload.php';

if (! is_readable($rhmotion_autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p><strong>RH Motion:</strong> Composer-Dependencies fehlen. Bitte <code>composer install</code> im Plugin-Verzeichnis ausführen.</p></div>';
    });
    return;
}

require_once $rhmotion_autoload;

RhMotion\Plugin::boot();
