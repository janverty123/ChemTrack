# Landing refinement — 30 October 2026

User requested softer corners, a contained hero, meaningful app demonstrations and animated oval navigation. Pantas remains an optional motion reference, not the visual authority. Preserve ChemTrack's Manrope/Inter fonts and teal/off-white palette.

Implement a split hero with a fully contained rounded product preview; remove protruding/rotated sheets. The module demonstration moves a long native lesson inside its reader as the visitor scrolls normally, shows an illustrative explicit reading-completion action, then reveals the next checkpoint. Scrolling the marketing page never writes progress or establishes the actual completion rule.

The checkpoint demonstration illustrates selection, saving and confirmed submission with replay and manual interaction. It uses synthetic content and makes no backend requests. Reduced motion presents the content without scroll choreography or automatic playback. Keep essential content and footer navigation available without JavaScript.

Replace duplicate header navigation with a fixed oval Menu button. Expand the menu from that button with a vertical sliding reveal; animate closure while retaining native dialog focus/Escape behavior. Put every destination in the menu and footer. Apply the softer shape language to existing student surfaces where consistent; preserve the reader oval and circles.

References researched: [Codrops expanding rounded menu](https://tympanus.net/codrops/2022/03/15/expanding-rounded-menu-animation/), [Linear](https://linear.app/) for product-led explanation, and [Raycast](https://www.raycast.com/) for a clearly framed product demonstration. References supply ideas, not copied layouts, assets or product claims. Public source inspected; no live animation playback claimed.

Verification: meaningful timeline boundary/reverse-scroll tests, landing feature tests, production build, source review and local HTTP checks. Browser viewing is disabled in Work settings; rendered wrapping, actual scroll timing, keyboard/dialog and real-phone appearance remain unverified. No authentication/data/provider changes are required.

## Implementation evidence

- Rebuilt the hero as a split copy/product layout with the app preview fully contained; removed the rotating/protruding sheet treatment from the landing and final call to action.
- Added a long chemistry sample with actual document translation, saved-position/coverage illustration, explicit reading-completion action and next-checkpoint reveal. Normal page scrolling is retained, reverse scrolling restores the scene, and reduced motion uses a scrollable static preview.
- The native checkpoint example plays selection → saving → submitted confirmation once when visible, offers replay, and lets manual answering interrupt playback. Reduced motion uses manual controls. The demo makes no learner/backend requests.
- The fixed pill menu expands vertically from its top-right origin and slides its links into view. Native dialog focus/Escape behavior is retained in code; closure is interruptible, with immediate keyboard/destination closure. Header contains only the brand; menu/footer contain every destination.
- Softer corners are applied to shared student inputs, buttons, answer choices and notices as well as landing surfaces. Full-bleed login regions and administration structure retain their existing layout; progress circles and the oval tracker retain their shapes.

Fresh checks on 30 October: **17 JavaScript tests** passed (three timeline, seven event-handler stand-in and seven existing reader tests); **three landing PHP tests / 14 assertions** passed. JavaScript syntax, Pint and final Vite production build passed. Local HTTP landing, coded login, repeated authenticated refresh and logout passed. Source/HTTP checks verify no duplicate header navigation, matching menu/footer destinations, unique anchor IDs and current asset delivery. These are not native-browser interaction or visual passes. No full-suite rerun was needed for this presentation-only change; earlier 41/254 evidence remains historical.
