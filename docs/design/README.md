# Design

Latest 4 November correction: public/account entry always uses the original light palette; explicit account preferences apply after sign-in. Auth forms use one logo, corner Back control, wide sign-in/right and signup/left layout, floating labels, individual in-field password controls and progressive/reduced-motion-aware transitions. See [auth/theme evidence](../development/auth-theme-refinement.md). Fresh visual checks remain pending while viewing is disabled.

Teacher theme now shares the student palette, local fonts, logo and rounded control language while keeping Filament's native teacher administration tools. The new overview and code-based student pages prioritize programme/content/progress/enrolment access. See [teacher workspace evidence](../development/teacher-workspace-evidence.md); native teacher sidebar fully retracts on desktop.

- [Workspace navigation](workspace-navigation.md): retractable sidebar, full-width Sign out, narrow overlay behavior and live-browser evidence.

Shared logo: the landing-header hexagon/C and ChemTrack wordmark are the single brand source in `resources/views/components/brand.blade.php`. Reuse it in header, footer, login, registration and student workspace. Keep the same proportions/type; use inverse colours on the login background. Do not reintroduce the old periodic-table mark or a different footer wordmark.

Latest visual instruction: Pantas is a motion reference only. The user requested softer corners, a contained hero, an actual moving lesson/checkpoint demonstration and an animated oval menu, with all navigation in the menu/footer. See [landing refinement](landing-refinement.md) for implementation and verification limits; this overrides the earlier landing composition.

Earlier 30 October: the landing and student workspace implemented the agreed type/palette, circle/cycle/task progress and oval reader direction while browser viewing was disabled. See [update evidence](../development/site-update-evidence.md). Later ring/navigation fixes have their own live-browser evidence; real-phone and unrelated reader/landing checks remain pending.

Store proposed and approved visual directions, accessibility notes, and mockups here. The [first-build direction](first-build-direction.md) is implemented as a developer proposal for local review; user visual approval is pending. Prioritize readable mobile learning flows, visible progress, and clear save/error states.

The user is interested in references from multiple websites, including components and motion. Inspect supplied pages/interactions, record the source and the specific element being adapted, and propose one consistent ChemTrack direction. Do not claim an inaccessible animation was visually verified.

- [Pantas landing-page reference](pantas-landing-reference.md): source-inspected scroll/feature storytelling; user requests a normal header and a separate fixed top-right navigation button. Live playback was unavailable because local browser viewing is disabled. Asset strategy and proposed ChemTrack adaptations are recorded.
- [Typography and theme proposal](typography-and-theme.md): actual-font preview using Manrope headings and Inter text, a teal/off-white palette, responsive type hierarchy and calculated contrast checks. Now applied to landing/student screens with local WOFF2 files; visual approval remains pending.
- [Dashboard and reader progress](dashboard-and-reader-progress.md): single overall ring, Cycle 1/2 switch and per-task rings; custom PDF reading and bottom-centre oval stage tracker. Includes a static concept and separates viewing from completion; Google Forms return flow deferred.


3 November presentation polish shares the student overall-circle component/styles with teacher profiles, adds teacher cycle filtering/task rings and compact report rings, and removes redundant development prose. Long student programme descriptions remain available under a native disclosure. See [presentation review](../development/presentation-polish.md). Current desktop browser paths passed; fresh narrow-width/phone verification remains pending.


4 November: circles now show selected-cycle four-stage completion; teacher release/setup controls and profile/security/appearance settings use the existing identity. Light/dark/system, larger text and reduced motion cover shared/Filament/landing surfaces, with native Filament theme initialization synchronized. See [build evidence](../development/cycle-accounts-settings-evidence.md). Current browser viewing is disabled; source/automated/HTTP checks do not establish visual/mobile quality.
