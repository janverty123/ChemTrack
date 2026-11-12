# Visual identity (November 2026 redesign)

Adopted 12 November 2026 (Asia/Manila). Supersedes the teal/off-white palette, Manrope headings and the hexagon-C mark in [typography and theme](typography-and-theme.md) and [landing refinement](landing-refinement.md). The user asked for a complete UI revision before deployment, with the assistant choosing the colours, fonts and logo.

## Logo

A hexagonal (benzene-style) ring whose outline is drawn as a **progress track**: the solid part is completed progress, the faint part is still to come, and a lime **atom marks where you are**. The inner circle is the aromatic ring, which reads as chemistry even at 16 px. The wordmark is "Chem**Track**" in Bricolage Grotesque, with "Track" in the primary blue.

- In-page mark: `resources/views/components/brand-mark.blade.php`. It is themed through the `--brand-*` CSS variables, so it works on light, dark and coloured panels.
- App icon and favicon: `public/favicon.svg` (white mark on a blue rounded square), plus `favicon.ico`, `apple-touch-icon.png`, `icon-192.png`, `icon-512.png` and `site.webmanifest`.

## Colour tokens

All colours live in `resources/css/tokens.css` and are shared by student, public and teacher (Filament) screens. Dark mode redefines the same tokens under `html[data-theme=dark]`; components never hard-code colours.

| Token | Light | Dark | Use |
| --- | --- | --- | --- |
| `--c-bg` | #F6F6F1 warm paper | #0E111A | Page background |
| `--c-surface` | #FFFFFF | #161A26 | Cards and panels |
| `--c-text` | #14171F | #ECEEF4 | Body text |
| `--c-muted` | #5B6070 | #A3A9BB | Secondary text |
| `--c-primary` | #3346D3 cobalt | #8F9DFF | Actions, progress, links |
| `--c-accent` | #C8F051 lime | #C8F051 | "You are here", highlights; always with dark ink |
| `--c-ink-panel` | #14171F | #1D2231 | Feature bands, sign-in story panel |

Checked contrast ratios (WCAG 2): text on background 16.5:1; muted on background 5.8:1; primary on white 7.1:1; white on primary 7.1:1; success, warning and danger on their soft backgrounds 5.0–5.8:1; dark-mode muted 7.4:1 and primary 7.0:1. Field borders are 3.3:1, above the 3:1 requirement for non-text UI. These figures are calculated from the token values, not measured from rendered pages.

## Type

- **Bricolage Grotesque** (self-hosted variable WOFF2) for headings, numbers and the wordmark: characterful but clear.
- **Inter** for body text, lessons, forms and Filament: high legibility for long reading on phones.

## Layout principles

- One shell for signed-in pages: a retractable sidebar on desktop and a modal drawer up to 900 px wide. The existing keyboard, Escape and backdrop behaviour is unchanged.
- The four required stages of a cycle (pre-test → module → video → post-test) appear as a numbered stage track. The overall ring and per-task rings keep their responsive container-query behaviour.
- The assessment progress bar and reader toolbar stay pinned while scrolling, so saving status is always visible.
- Error pages (403/404/419/429/500/503) are self-contained, with inline styles that still render if built assets are missing.
- Landing: the scroll-hijacking reader scene and separate oval menu were removed in favour of a sticky header, a product preview, the four-stage explanation, the interactive checkpoint example, student and teacher benefits, and a privacy note.
