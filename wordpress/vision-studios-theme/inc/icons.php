<?php
/**
 * Inline SVG icons (24x24, stroke based) — no icon font, no external CSS.
 */
defined( 'ABSPATH' ) || exit;

function vs_icon( string $name, string $class = '' ): string {
	static $paths = [
		'arrow-up-right' => '<path d="M7 17 17 7M8 7h9v9"/>',
		'arrow-right'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'bars'           => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'xmark'          => '<path d="M6 6l12 12M18 6 6 18"/>',
		'check'          => '<path d="m5 12 5 5L20 7"/>',
		'circle-check'   => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
		'circle'         => '<circle cx="12" cy="12" r="9"/>',
		'tower-broadcast'=> '<circle cx="12" cy="9" r="2"/><path d="M12 11v10M7.8 4.8a6 6 0 0 0 0 8.4M16.2 4.8a6 6 0 0 1 0 8.4M5 2a10 10 0 0 0 0 14M19 2a10 10 0 0 1 0 14"/>',
		'camera-retro'   => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M3 11h18M8 7V5h5v2"/><circle cx="14" cy="15" r="3"/>',
		'people-group'   => '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M15 20v-1a4 4 0 0 1 6 0v1"/>',
		'pen-ruler'      => '<path d="M3 17l4 4 11-11-4-4L3 17zM14 6l4 4M7 13l4 4M10 10l2 2"/>',
		'satellite-dish' => '<path d="M4 10a8 8 0 0 0 10 10M4 4a14 14 0 0 1 16 16M13 17l-6-6M9 8l7 7M15 5l4 4"/>',
		'microphone-lines'=> '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3M9 21h6M9 8h6M9 11h6"/>',
		'vector-square'  => '<rect x="3" y="3" width="4" height="4"/><rect x="17" y="3" width="4" height="4"/><rect x="3" y="17" width="4" height="4"/><rect x="17" y="17" width="4" height="4"/><path d="M7 5h10M5 7v10M19 7v10M7 19h10"/>',
		'couch'          => '<path d="M5 11V8a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v3M3 13a2 2 0 0 1 4 0v3h10v-3a2 2 0 0 1 4 0v5H3v-5zM5 18v2M19 18v2"/>',
		'tv'             => '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8M12 17v4"/>',
		'sliders'        => '<path d="M4 6h10M18 6h2M4 12h2M10 12h10M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="8" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
		'video'          => '<rect x="3" y="7" width="13" height="10" rx="2"/><path d="m16 11 5-3v8l-5-3z"/>',
		'bolt'           => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/>',
		'arrows-up-down-left-right' => '<path d="M12 2v20M2 12h20M8 6l4-4 4 4M8 18l4 4 4-4M6 8l-4 4 4 4M18 8l4 4-4 4"/>',
		'lightbulb'      => '<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.7.6 1 1.3 1 2.5h6c0-1.2.3-1.9 1-2.5A6 6 0 0 0 12 3z"/>',
		'plus'           => '<path d="M12 5v14M5 12h14"/>',
		'map'            => '<path d="M12 21s7-6.5 7-11a7 7 0 0 0-14 0c0 4.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
	];
	$p = $paths[ $name ] ?? $paths['circle'];
	return '<svg class="vs-icon ' . esc_attr( $class ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $p . '</svg>';
}
