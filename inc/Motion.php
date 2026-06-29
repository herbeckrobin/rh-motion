<?php

declare(strict_types=1);

namespace RhMotion;

use RhMotion\Admin\MotionGroup;

/**
 * Kern von rh-motion: Animationen als per-Block-Attribut.
 *
 * Eine Quelle für Block-Whitelist und Animations-Set (hier in PHP), in den
 * Editor gespiegelt via wp_localize_script, sodass Editor-Auswahl und
 * Frontend-Render nie divergieren. Beides per Filter erweiterbar.
 *
 * Frontend: render_block setzt data-rhm-anim / data-rhm-loop aufs erste Tag,
 * ein synchrones Head-Script setzt html.rhm-anim-ready VOR dem ersten Paint
 * (FOUC-frei), CSS + view.js erledigen den Rest. Editor: nur die Inspector-JS
 * lädt, Frontend-CSS NICHT, darum bleiben Blöcke im Editor sichtbar.
 *
 * prefers-reduced-motion: der Ready-Marker wird nicht gesetzt, alle Initial-
 * States (opacity:0/Transform) greifen nicht, alles ist sofort voll sichtbar.
 */
final class Motion
{
    public const ATTR_REVEAL = 'rhmReveal';
    public const ATTR_LOOP = 'rhmLoop';

    /**
     * Alt-Werte-Aliase für sanfte Migrationen (z.B. aus Theme-Reveal-Systemen).
     * Gemappt auf den kanonischen rh-motion-Wert vor dem Whitelist-Check.
     *
     * @var array<string, string>
     */
    private const REVEAL_ALIASES = [
        'zoom' => 'expand',
        'slide-up' => 'fly-up',
    ];

    public function boot(): void
    {
        if (! $this->enabled()) {
            return;
        }

        add_filter('register_block_type_args', [$this, 'registerBlockAttributes'], 10, 2);
        add_filter('render_block', [$this, 'addDataAttributes'], 10, 2);
        add_action('wp_head', [$this, 'renderReadyMarker'], 1);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontend']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditor']);
    }

    private function enabled(): bool
    {
        return (bool) rhbp_setting(MotionGroup::GROUP_ID, MotionGroup::FIELD_ENABLED, true);
    }

    private function scrollEffectsAllowed(): bool
    {
        return (bool) rhbp_setting(MotionGroup::GROUP_ID, MotionGroup::FIELD_SCROLL_EFFECTS, true);
    }

    private function allBlocksAllowed(): bool
    {
        return (bool) rhbp_setting(MotionGroup::GROUP_ID, MotionGroup::FIELD_ALL_BLOCKS, true);
    }

    /**
     * Greift die Animation für diesen Block? Mit dem All-Blocks-Schalter jeder
     * Block, sonst nur die Core-Whitelist.
     */
    private function matchesBlock(string $blockName): bool
    {
        if ($blockName === '') {
            return false;
        }

        return $this->allBlocksAllowed() || in_array($blockName, $this->blocks(), true);
    }

    /**
     * Block-Whitelist. Pro Theme erweiterbar.
     *
     * @return array<int, string>
     */
    public function blocks(): array
    {
        $blocks = [
            'core/image',
            'core/group',
            'core/columns',
            'core/column',
            'core/buttons',
            'core/button',
            'core/heading',
            'core/paragraph',
            'core/cover',
            'core/list',
            'core/list-item',
        ];

        /** @var array<int, string> $filtered */
        $filtered = (array) apply_filters('rh-blueprint/motion/blocks', $blocks);

        return $filtered;
    }

    /**
     * Eingangs-Animationen als value => label. Scroll-Effekte nur wenn erlaubt.
     *
     * @return array<string, string>
     */
    public function revealOptions(): array
    {
        $options = [
            '' => __('Keine', 'rh-motion'),
            'fade-in' => __('Eingeblendet', 'rh-motion'),
            'fly-left' => __('Von links eingeflogen', 'rh-motion'),
            'fly-right' => __('Von rechts eingeflogen', 'rh-motion'),
            'fly-up' => __('Von unten eingeflogen', 'rh-motion'),
            'expand' => __('Aufploppen', 'rh-motion'),
            'stagger-up' => __('Reihe gestaffelt (Container/Liste)', 'rh-motion'),
        ];

        if ($this->scrollEffectsAllowed()) {
            $options['scroll-rotate'] = __('Beim Scrollen drehen', 'rh-motion');
            $options['scroll-parallax'] = __('Beim Scrollen bewegen', 'rh-motion');
        }

        /** @var array<string, string> $filtered */
        $filtered = (array) apply_filters('rh-blueprint/motion/reveal_options', $options);

        return $filtered;
    }

    /**
     * Dauer-Loops als value => label.
     *
     * @return array<string, string>
     */
    public function loopOptions(): array
    {
        $options = [
            '' => __('Keine', 'rh-motion'),
            'loop-wobble' => __('Wackelt', 'rh-motion'),
            'loop-shake' => __('Zittert', 'rh-motion'),
            'loop-pulse' => __('Pulsiert', 'rh-motion'),
            'loop-bounce' => __('Bouncy', 'rh-motion'),
            'loop-drift' => __('Leichte Drift', 'rh-motion'),
        ];

        /** @var array<string, string> $filtered */
        $filtered = (array) apply_filters('rh-blueprint/motion/loop_options', $options);

        return $filtered;
    }

    /**
     * Hängt rhmReveal/rhmLoop serverseitig an die Block-Typen, die das Editor-JS
     * clientseitig bespielt. Ohne das verwirft der block-renderer-REST-Endpoint
     * die mitgeschickten Attribute (rest_additional_properties_forbidden, 400),
     * SSR-Blöcke rendern dann im Editor nicht. Bedingung deckungsgleich mit
     * matchesBlock(), damit Editor und Server dieselbe Block-Menge bespielen.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public function registerBlockAttributes(array $args, string $blockName): array
    {
        if (! $this->matchesBlock($blockName)) {
            return $args;
        }

        $attributes = isset($args['attributes']) && is_array($args['attributes']) ? $args['attributes'] : [];
        $attributes[self::ATTR_REVEAL] = ['type' => 'string', 'default' => ''];
        $attributes[self::ATTR_LOOP] = ['type' => 'string', 'default' => ''];
        $args['attributes'] = $attributes;

        return $args;
    }

    /**
     * Render-Filter: data-rhm-anim + data-rhm-loop aufs erste Opening-Tag.
     */
    public function addDataAttributes(string $blockContent, array $block): string
    {
        if (trim($blockContent) === '') {
            return $blockContent;
        }

        $blockName = $block['blockName'] ?? '';
        if (! is_string($blockName) || ! $this->matchesBlock($blockName)) {
            return $blockContent;
        }

        $reveal = $block['attrs'][self::ATTR_REVEAL] ?? '';
        $loop = $block['attrs'][self::ATTR_LOOP] ?? '';

        if (is_string($reveal) && isset(self::REVEAL_ALIASES[$reveal])) {
            $reveal = self::REVEAL_ALIASES[$reveal];
        }

        $attrs = '';
        if (is_string($reveal) && $reveal !== '' && array_key_exists($reveal, $this->revealOptions())) {
            $attrs .= ' data-rhm-anim="' . esc_attr($reveal) . '"';
        }
        if (is_string($loop) && $loop !== '' && array_key_exists($loop, $this->loopOptions())) {
            $attrs .= ' data-rhm-loop="' . esc_attr($loop) . '"';
        }

        if ($attrs === '') {
            return $blockContent;
        }

        // Erstes Opening-Tag finden und Attribute einsetzen (figure/div/p/h2/ul...).
        return (string) preg_replace(
            '/<([a-zA-Z][a-zA-Z0-9]*)(\s|>)/',
            '<$1' . $attrs . '$2',
            $blockContent,
            1
        );
    }

    /**
     * Synchroner Head-Marker: setzt html.rhm-anim-ready vor dem ersten Paint.
     * Reduced-Motion: Marker bleibt aus, CSS-Hidden-States greifen nicht.
     */
    public function renderReadyMarker(): void
    {
        echo '<script>(function(){if(window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches)return;document.documentElement.classList.add("rhm-anim-ready");})();</script>' . "\n";
        echo '<noscript><style>html.rhm-anim-ready [data-rhm-anim],html.rhm-anim-ready [data-rhm-loop]{opacity:1!important;transform:none!important;animation:none!important}</style></noscript>' . "\n";
    }

    /**
     * Frontend-Assets (CSS + view.js). Laden NICHT im Editor, darum bleiben
     * die Blöcke im Editor sichtbar (kein Hidden-State im Iframe).
     */
    public function enqueueFrontend(): void
    {
        $cssRel = 'assets/css/motion.css';
        $cssAbs = RHMOTION_PLUGIN_DIR . $cssRel;
        if (file_exists($cssAbs)) {
            wp_enqueue_style('rh-motion', RHMOTION_PLUGIN_URL . $cssRel, [], (string) filemtime($cssAbs));
        }

        $jsRel = 'assets/js/motion-view.js';
        $jsAbs = RHMOTION_PLUGIN_DIR . $jsRel;
        if (file_exists($jsAbs)) {
            wp_enqueue_script('rh-motion-view', RHMOTION_PLUGIN_URL . $jsRel, [], (string) filemtime($jsAbs), true);
        }
    }

    /**
     * Editor-Assets (Inspector-Panel). Die Block-Whitelist + Animations-Listen
     * kommen aus PHP (eine Quelle) und werden in den Editor gespiegelt.
     */
    public function enqueueEditor(): void
    {
        $jsRel = 'assets/js/motion-editor.js';
        $jsAbs = RHMOTION_PLUGIN_DIR . $jsRel;
        if (! file_exists($jsAbs)) {
            return;
        }

        wp_enqueue_script(
            'rh-motion-editor',
            RHMOTION_PLUGIN_URL . $jsRel,
            ['wp-hooks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-i18n'],
            (string) filemtime($jsAbs),
            true
        );

        wp_localize_script('rh-motion-editor', 'rhMotionConfig', [
            'blocks' => array_values($this->blocks()),
            'allBlocks' => $this->allBlocksAllowed(),
            'attrReveal' => self::ATTR_REVEAL,
            'attrLoop' => self::ATTR_LOOP,
            'reveal' => $this->toOptionList($this->revealOptions()),
            'loop' => $this->toOptionList($this->loopOptions()),
        ]);
    }

    /**
     * value=>label-Map in die {value,label}-Liste, die SelectControl erwartet.
     *
     * @param array<string, string> $options
     * @return array<int, array{value: string, label: string}>
     */
    private function toOptionList(array $options): array
    {
        $list = [];
        foreach ($options as $value => $label) {
            $list[] = ['value' => $value, 'label' => $label];
        }

        return $list;
    }
}
