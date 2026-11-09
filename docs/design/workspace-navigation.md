# Workspace navigation

Implemented 30 October 2026 (Asia/Manila), following the user's request for a redesigned, retractable left navigation and styled Sign out control.

The shared student/teacher-report workspace has a hide button above the existing logo, icon-labelled Home and learning/dashboard links, a compact help area and a code/role profile. Sign out is a full-width outlined button with an exit icon. Its original CSRF-protected POST action remains intact; no authentication/role rules change. The separate Filament administration layout is not replaced.

Hiding the sidebar removes its page-width offset and leaves a sticky **Navigation** button in the main view. Clicking it reopens the sidebar. The current tab remembers the preference in session storage; unavailable storage does not block the controls. Hidden navigation is inert and removed from the focus order. Without JavaScript, navigation remains visible.

At window widths up to 900px, navigation starts closed and opens as an overlay drawer, with background content inert, keyboard focus contained, and Escape/outside-click dismissal. The original content remains mounted, preserving in-progress fields. Reduced motion disables the sidebar transition. The module tracker is centred against the available content when the sidebar is hidden.

## Evidence

- Final production build, JavaScript syntax and Pint passed.
- **23 frontend checks** passed: six new navigation tests plus 17 existing landing/reader checks. Navigation tests cover hide/show, focus, tab preference, blocked storage, narrow overlay, Escape, focus wrapping, navigation-link closure, breakpoint changes and public-page isolation.
- Existing access/learning feature tests: **13 tests / 57 assertions passed**. Local HTTP coded login, authenticated refresh and logout passed using a separate test session.
- Live browser checks at 320, 640, 900, 901, 1029 and 1440px verified main content has zero left offset when closed, desktop offset returns when open, narrow drawer makes the background inert, controls remain at least 44px high, and there is no horizontal page overflow.
- Actual pointer hide/show checked at the original window size and 375px; mobile keyboard focus wraps from Sign out to the hide button, Escape restores the page/reopener, and outside-click closes the drawer. Desktop collapsed state survived a reload. Fast scripted clicks during the slide were replaced with keyboard activation for deterministic multi-width checks; these do not establish a motion-performance audit.
- Screenshot/geometry evidence is ignored under `.local/previews/`, with `navigation-expanded-20261030.jpg`, `navigation-collapsed-20261030.jpg` and `workspace-navigation-responsive-20261030.json`. Original viewport restored; navigation left open so the user can see the hide control.

No learner answers/progress, database schema or authorization changed. Real-device touch and a full accessibility audit remain pending. Reader tracker centring is implemented in CSS; actual PDF rendering was not rerun for this navigation change.
