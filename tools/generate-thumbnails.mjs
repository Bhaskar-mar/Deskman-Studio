#!/usr/bin/env node
/**
 * Generates the designed "coming soon" industry thumbnails (SVG website
 * mock-ups) used on the Templates page.
 *
 *   node tools/generate-thumbnails.mjs
 *
 * Add an entry to INDUSTRIES to create a thumbnail for a new category. Live
 * templates use a real screenshot instead (see tools/screenshot-thumbnail.mjs).
 */
import { writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const OUT = join( dirname( fileURLToPath( import.meta.url ) ), '../wp-content/plugins/deskman-studio-manager/assets/site-templates/img/thumbs' );

// 24×24 stroke icons.
const ICONS = {
	hospital: '<rect x="3.5" y="3.5" width="17" height="17" rx="4"/><path d="M12 7.5v9M7.5 12h9"/>',
	school: '<path d="M3 10.5 12 5l9 5.5"/><path d="M5 9.5V20h14V9.5"/><path d="M10 20v-5h4v5"/><path d="M12 5V2.5h3"/>',
	college: '<path d="M2.5 9 12 4.5 21.5 9 12 13.5Z"/><path d="M6.5 11v4.5c0 1.5 2.5 3 5.5 3s5.5-1.5 5.5-3V11"/><path d="M21.5 9v5.5"/>',
	tutor: '<path d="M3 5.5c3-1 6-1 9 1 3-2 6-2 9-1V19c-3-1-6-1-9 1-3-2-6-2-9-1Z"/><path d="M12 6.5V20"/>',
	home: '<path d="M3.5 11 12 4l8.5 7"/><path d="M5.5 9.5V20h13V9.5"/><circle cx="12" cy="14" r="2"/><path d="M12 16v3"/>',
	solar: '<circle cx="7" cy="7" r="2.6"/><path d="M7 1.8v1.2M7 11v1.2M1.8 7h1.2M11 7h1.2M3.3 3.3l.9.9M9.8 9.8l.9.9M3.3 10.7l.9-.9M9.8 4.2l.9-.9"/><path d="M10 21l2-8h9l-2 8Z"/><path d="M11 17h9M15.5 13l-1.5 8"/>',
	salon: '<circle cx="6" cy="6.5" r="2.8"/><circle cx="6" cy="17.5" r="2.8"/><path d="M8.2 8.2 20 18M8.2 15.8 20 6"/>',
	restaurant: '<path d="M4 9h12v4a6 6 0 0 1-12 0Z"/><path d="M16 10h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M7 3.5c-.8 1 .8 2 0 3M11 3.5c-.8 1 .8 2 0 3"/><path d="M3 21h14"/>',
	briefcase: '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7"/><path d="M3 12.5h18M10.5 12.5v2h3v-2"/>',
};

const INDUSTRIES = [
	{ slug: 'hospitals', icon: 'hospital', c1: '#2563EB', c2: '#06B6D4', bg: '#EAF2FF' },
	{ slug: 'schools', icon: 'school', c1: '#F59E0B', c2: '#EF4444', bg: '#FFF4E5' },
	{ slug: 'colleges-universities', icon: 'college', c1: '#4F46E5', c2: '#9333EA', bg: '#EFEDFF' },
	{ slug: 'teachers-tutors', icon: 'tutor', c1: '#059669', c2: '#0EA5E9', bg: '#E7F8F2' },
	{ slug: 'real-estate', icon: 'home', c1: '#0F766E', c2: '#65A30D', bg: '#E8F5F1' },
	{ slug: 'solar-energy', icon: 'solar', c1: '#EA8A00', c2: '#16A34A', bg: '#FFF7E0' },
	{ slug: 'salons-beauty', icon: 'salon', c1: '#DB2777', c2: '#A855F7', bg: '#FDEEF6' },
	{ slug: 'restaurants-cafes', icon: 'restaurant', c1: '#C2410C', c2: '#B45309', bg: '#FFF1E6' },
	{ slug: 'professional-services', icon: 'briefcase', c1: '#1E293B', c2: '#3B82F6', bg: '#EDF1F7' },
];

function svg( { slug, icon, c1, c2, bg } ) {
	const id = slug.replace( /[^a-z]/g, '' );
	const cards = [ 0, 1, 2 ].map( ( i ) => {
		const x = 92 + i * 156;
		return `<rect x="${ x }" y="300" width="140" height="120" rx="14" fill="#FFFFFF" stroke="#E7EBF1"/>
		<circle cx="${ x + 26 }" cy="328" r="12" fill="${ i === 1 ? c2 : c1 }" fill-opacity=".16"/>
		<rect x="${ x + 16 }" y="352" width="86" height="8" rx="4" fill="#1F2937" fill-opacity=".75"/>
		<rect x="${ x + 16 }" y="368" width="108" height="6" rx="3" fill="#94A3B8" fill-opacity=".55"/>
		<rect x="${ x + 16 }" y="380" width="92" height="6" rx="3" fill="#94A3B8" fill-opacity=".55"/>`;
	} ).join( '' );

	return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 400" width="640" height="400">
	<defs>
		<linearGradient id="${ id }-bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${ bg }"/><stop offset="1" stop-color="#FFFFFF"/></linearGradient>
		<linearGradient id="${ id }-hero" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${ c1 }"/><stop offset="1" stop-color="${ c2 }"/></linearGradient>
		<filter id="${ id }-sh" x="-10%" y="-10%" width="120%" height="130%"><feDropShadow dx="0" dy="14" stdDeviation="16" flood-color="#0F172A" flood-opacity=".14"/></filter>
	</defs>
	<rect width="640" height="400" fill="url(#${ id }-bg)"/>
	<circle cx="590" cy="40" r="120" fill="${ c2 }" fill-opacity=".10"/>
	<circle cx="30" cy="380" r="110" fill="${ c1 }" fill-opacity=".08"/>
	<g filter="url(#${ id }-sh)">
		<rect x="64" y="40" width="512" height="400" rx="18" fill="#FFFFFF"/>
	</g>
	<rect x="64" y="40" width="512" height="30" rx="18" fill="#F3F5F9"/>
	<rect x="64" y="56" width="512" height="14" fill="#F3F5F9"/>
	<circle cx="84" cy="55" r="4.5" fill="#FF6159"/><circle cx="98" cy="55" r="4.5" fill="#FFBD2E"/><circle cx="112" cy="55" r="4.5" fill="#28C941"/>
	<rect x="236" y="48" width="168" height="14" rx="7" fill="#FFFFFF"/>
	<rect x="92" y="86" width="22" height="22" rx="7" fill="url(#${ id }-hero)"/>
	<rect x="122" y="92" width="70" height="10" rx="5" fill="#1F2937" fill-opacity=".85"/>
	<rect x="316" y="93" width="38" height="8" rx="4" fill="#94A3B8" fill-opacity=".6"/>
	<rect x="364" y="93" width="38" height="8" rx="4" fill="#94A3B8" fill-opacity=".6"/>
	<rect x="412" y="93" width="38" height="8" rx="4" fill="#94A3B8" fill-opacity=".6"/>
	<rect x="470" y="86" width="78" height="22" rx="11" fill="${ c1 }"/>
	<rect x="92" y="138" width="200" height="16" rx="8" fill="#0F172A" fill-opacity=".9"/>
	<rect x="92" y="162" width="168" height="16" rx="8" fill="#0F172A" fill-opacity=".9"/>
	<rect x="92" y="192" width="190" height="7" rx="3.5" fill="#94A3B8" fill-opacity=".6"/>
	<rect x="92" y="206" width="172" height="7" rx="3.5" fill="#94A3B8" fill-opacity=".6"/>
	<rect x="92" y="230" width="96" height="28" rx="14" fill="${ c1 }"/>
	<rect x="198" y="230" width="84" height="28" rx="14" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5"/>
	<rect x="322" y="126" width="226" height="148" rx="20" fill="url(#${ id }-hero)"/>
	<circle cx="512" cy="152" r="46" fill="#FFFFFF" fill-opacity=".12"/>
	<circle cx="352" cy="262" r="34" fill="#FFFFFF" fill-opacity=".10"/>
	<g transform="translate(395 160) scale(3.4)" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">${ ICONS[ icon ] }</g>
	${ cards }
</svg>
`;
}

mkdirSync( OUT, { recursive: true } );
for ( const industry of INDUSTRIES ) {
	writeFileSync( join( OUT, `${ industry.slug }.svg` ), svg( industry ) );
	console.log( 'wrote', `${ industry.slug }.svg` );
}
