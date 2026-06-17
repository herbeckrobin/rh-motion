=== RH Motion ===
Contributors: robinherbeck
Tags: animation, scroll, reveal, motion, gutenberg
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.1.1
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

= 0.1.0 =
* Initial release: per-block reveal and loop attributes, scroll-driven effects, FOUC-free, reduced-motion aware, single-source block/option lists mirrored to the editor.
