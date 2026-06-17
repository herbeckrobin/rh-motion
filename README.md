# RH Motion

Scroll-Animationen als Auswahl pro Block im Editor. Teil der rh-blueprint Kollektion.

Der Endkunde wählt die Animation rechts in der Block-Seitenleiste, kein Theme-Code, keine Hardcodes. FOUC-frei und respektiert `prefers-reduced-motion`.

## Was es macht

- **Eingangs-Animationen** (einmal beim Scrollen ins Bild): Eingeblendet, von links/rechts/unten eingeflogen, Aufploppen, Reihe gestaffelt.
- **Scroll-Effekte** (an die Scroll-Position gekoppelt): Drehen, Parallax (per nativem `animation-timeline`, abschaltbar).
- **Dauer-Bewegungen** (Loop): Wackelt, Zittert, Pulsiert, Bouncy, Drift. Kombinierbar mit einer Eingangs-Animation (der Loop startet nach dem Einfliegen).
- **FOUC-frei**: ein synchrones Head-Script setzt den Bereitschafts-Zustand vor dem ersten Paint. Bei `prefers-reduced-motion` bleibt alles sofort sichtbar.
- **Editor-Parität**: das Versteck-CSS lädt nur im Frontend, im Editor bleiben Blöcke sichtbar.

## Einstellungen

Im Backend unter **RH Blueprint → Animationen**: globaler Schalter und ein Schalter, der die schwereren Scroll-Effekte erlaubt. Die eigentliche Auswahl passiert pro Block im Inspector-Panel „Animation".

## Für Entwickler

Block-Whitelist und Animations-Sets sind filterbar:

- `rh-blueprint/motion/blocks` (array): welche Blöcke die Auswahl bekommen.
- `rh-blueprint/motion/reveal_options` (array `wert => label`): Eingangs-Animationen erweitern.
- `rh-blueprint/motion/loop_options` (array `wert => label`): Loops erweitern.

## Installation

ZIP hochladen und aktivieren. Der geteilte Core ist gebündelt.

## Voraussetzungen

WordPress 6.5+, PHP 8.1+.
