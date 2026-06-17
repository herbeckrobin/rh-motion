/**
 * RH Motion, Frontend-Script.
 *
 * Reveals ([data-rhm-anim] in REVEAL_TYPES): IntersectionObserver setzt
 * .is-rhm-in beim ersten Schnittpunkt, CSS macht den Rest. One-shot.
 *
 * Loops ([data-rhm-loop]): laufen erst mit .is-rhm-loop, damit der Reveal-
 * Transform nicht durch die Loop-Animation überschrieben wird:
 *   - nur Loop:            sofort aktiv
 *   - Reveal + Loop:       aktiv nach Reveal-transitionend (Fallback-Timeout)
 *
 * Scroll-Scrub (scroll-rotate, scroll-parallax) läuft rein per CSS via
 * animation-timeline: view(), kein JS-Trigger.
 *
 * prefers-reduced-motion: kein Ready-Marker (siehe Head-Snippet), kein IO,
 * keine Loops, alles sofort voll sichtbar.
 */
(function () {
	'use strict';

	var doc = document;

	if (!('IntersectionObserver' in window)) {
		return;
	}
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	var REVEAL_TYPES = {
		'fade-in': 1,
		'fly-left': 1,
		'fly-right': 1,
		'fly-up': 1,
		'expand': 1,
		'stagger-up': 1
	};

	var observer = new IntersectionObserver(
		function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}
				entry.target.classList.add('is-rhm-in');
				observer.unobserve(entry.target);
			});
		},
		{ threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
	);

	function activateLoopAfterReveal(node) {
		var done = false;
		var activate = function () {
			if (done) {
				return;
			}
			done = true;
			node.classList.add('is-rhm-loop');
		};
		var onEnd = function (ev) {
			if (ev.target !== node) {
				return;
			}
			node.removeEventListener('transitionend', onEnd);
			activate();
		};
		node.addEventListener('transitionend', onEnd);
		setTimeout(activate, 1200);
	}

	function bind() {
		var nodes = doc.querySelectorAll('[data-rhm-anim], [data-rhm-loop]');
		for (var i = 0; i < nodes.length; i++) {
			var node = nodes[i];
			var revealType = node.getAttribute('data-rhm-anim');
			var loopType = node.getAttribute('data-rhm-loop');

			if (revealType && REVEAL_TYPES[revealType]) {
				observer.observe(node);
			}

			if (loopType) {
				if (revealType) {
					activateLoopAfterReveal(node);
				} else {
					node.classList.add('is-rhm-loop');
				}
			}
		}
	}

	if (doc.readyState === 'loading') {
		doc.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}
})();
