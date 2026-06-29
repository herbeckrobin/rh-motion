=== RH Motion ===
Contributors: robinherbeck
Tags: animation, scroll, reveal, motion, gutenberg
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Scroll animations as a per-block setting in the editor: entrance reveals, continuous loops and scroll-driven effects. FOUC-free and accessible.

== Description ==

RH Motion lets the editor pick an animation per block, right in the block sidebar. No code, no theme work. The customer keeps full control over which block animates how.

= Animations =

* Entrance reveals: fade in, fly from left/right/bottom, pop (expand), staggered children
* Continuous loops: wobble, shake, pulse, bounce, drift
* Scroll-driven effects: rotate and parallax (CSS animation-timeline, can be disabled)
* Reveal and loop combine: the loop starts after the entrance finishes

It is FOUC-free (a synchronous head marker sets the ready state before first paint) and fully respects prefers-reduced-motion (no marker, everything visible at once). The frontend CSS does not load in the editor, so blocks stay visible while editing.

The block whitelist and the animation set are extensible via PHP filters (rh-blueprint/motion/blocks, rh-blueprint/motion/reveal_options, rh-blueprint/motion/loop_options).

Part of the rh-blueprint collection. Settings live under RH Blueprint > Animationen.

== Changelog ==

= 0.2.2 =
* Fix: register the reveal/loop attributes server-side so ServerSideRender blocks no longer fail with a 400 (rest_additional_properties_forbidden) in the editor.

= 0.2.1 =
* Bundle core 2.4.1 (shared-library loader fix for mixed bundled versions).

= 0.2.0 =
* Animation choice available on all blocks by default; value aliases for smoother migrations from theme reveal systems.

= 0.1.1 =
* Bundle core 2.3.0 (suite expansion via ghost tabs).

= 0.1.0 =
* Initial release: per-block reveal and loop attributes, scroll-driven effects, FOUC-free, reduced-motion aware, single-source block/option lists mirrored to the editor.
