/**
 * RH Motion, Editor-Script (buildless).
 *
 * Hängt zwei Attribute an die erlaubten Blöcke (Reveal + Loop) und zeigt rechts
 * im Inspector zwei SelectControls. Block-Whitelist und Optionen kommen aus PHP
 * (window.rhMotionConfig, via wp_localize_script), eine Quelle, keine Divergenz
 * zwischen Editor-Auswahl und Frontend-Render.
 *
 * Nutzt die window.wp.* UMD-Globals (Deps in inc/Motion.php registriert).
 */
(function (wp, config) {
	'use strict';

	if (!wp || !wp.hooks || !wp.element || !wp.blockEditor || !wp.components || !wp.compose) {
		return;
	}
	if (!config || !Array.isArray(config.blocks)) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : function (s) { return s; };

	var BLOCKS = config.blocks;
	var ATTR_REVEAL = config.attrReveal;
	var ATTR_LOOP = config.attrLoop;
	var REVEAL_OPTIONS = config.reveal || [];
	var LOOP_OPTIONS = config.loop || [];

	// 1. Attribute an allen erlaubten Blöcken anhängen.
	addFilter(
		'blocks.registerBlockType',
		'rh-motion/add-attributes',
		function (settings, name) {
			if (BLOCKS.indexOf(name) === -1) {
				return settings;
			}
			var added = {};
			added[ATTR_REVEAL] = { type: 'string', default: '' };
			added[ATTR_LOOP] = { type: 'string', default: '' };
			settings.attributes = Object.assign({}, settings.attributes, added);
			return settings;
		}
	);

	// 2. Inspector-Panel mit zwei SelectControls.
	var withMotionPanel = createHigherOrderComponent(
		function (BlockEdit) {
			return function (props) {
				if (BLOCKS.indexOf(props.name) === -1) {
					return el(BlockEdit, props);
				}
				var attrs = props.attributes || {};

				return el(
					Fragment,
					null,
					el(BlockEdit, props),
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: __('Animation', 'rh-motion'), initialOpen: false },
							el(SelectControl, {
								label: __('Eingangs-Animation', 'rh-motion'),
								help: __('Läuft einmal beim Scrollen ins Bild.', 'rh-motion'),
								value: attrs[ATTR_REVEAL] || '',
								options: REVEAL_OPTIONS,
								onChange: function (value) {
									var update = {};
									update[ATTR_REVEAL] = value || '';
									props.setAttributes(update);
								}
							}),
							el(SelectControl, {
								label: __('Dauer-Bewegung', 'rh-motion'),
								help: __('Läuft kontinuierlich. Mit Eingangs-Animation kombiniert: startet nach dem Einfliegen.', 'rh-motion'),
								value: attrs[ATTR_LOOP] || '',
								options: LOOP_OPTIONS,
								onChange: function (value) {
									var update = {};
									update[ATTR_LOOP] = value || '';
									props.setAttributes(update);
								}
							})
						)
					)
				);
			};
		},
		'withRhMotionPanel'
	);

	addFilter('editor.BlockEdit', 'rh-motion/inspector', withMotionPanel);
})(window.wp, window.rhMotionConfig);
