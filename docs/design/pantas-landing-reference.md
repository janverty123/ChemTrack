# Pantas landing-page reference

**30 October user correction:** Pantas is not the design authority. Retain only the idea of motion explaining features. The hero, softer shapes, reader choreography and expanding oval menu are now revised under [landing refinement](landing-refinement.md); the earlier composition below is historical.

**30 October implementation status:** coded invitation registration, cycle-filtered progress rings, private PDF/native reading observations, oval tracker, landing page and code-based completion reports are now implemented locally. Google Forms authorized response verification and real materials remain pending. See [current evidence](../development/site-update-evidence.md). Earlier proposal/mockup wording below records its original design stage and does not override this status.

29 October 2026 (Asia/Manila). Reference analysis and user requirements, not an implemented landing page or approval of a complete new visual system.

Source: [Pantas](https://www.pantas.study/). Scope: the landing page's explanation of product features through animation and scrolling. Its exam-preparation features are not being added to ChemTrack merely because they appear in the reference.

## What was actually inspected

The public HTML/CSS/inline JavaScript was retrieved successfully with HTTP 200. A local-only copy is in ignored `.local/pantas-reference.html`. The web reader failed to load the page, and the in-app browser reported that local browser use is disabled in Work settings. No browser setting was changed or bypassed. Therefore the following findings are source-based; live rendering, motion timing and visual quality have not been watched or verified.

The source defines viewport-triggered feature entrances, small animated product-interface scenes, a simulated lesson-reading/progress sequence, a question/result sequence and layered artwork with scroll-dependent movement. It uses inline SVG and CSS animation extensively. The header is sticky and changes state after scrolling; reduced-motion styles are present. Some scenes are time-driven after becoming visible, so not every effect is directly controlled by scroll position.

## User's requested navigation behaviour

- Normal header at the beginning of the page. It scrolls out with the page; do not make the full header sticky/fixed or follow Pantas's floating-header behaviour.
- A separate compact navigation button stays at the top right of the viewport throughout scrolling.
- Proposed button behaviour: labelled Menu control opens a compact navigation panel with page-section links and sign-in. Close on Escape/outside interaction, restore focus, support keyboard use, and preserve phone safe-area spacing. Exact label/icon, links and appearance remain design choices.
- Keep the fixed control from covering headings, authentication controls or page content. The module reader's requested bottom-centre progress indicator is a separate control on a different screen.

## Proposed ChemTrack adaptation

| Landing-page story | Original ChemTrack visual/motion proposal |
| --- | --- |
| What ChemTrack helps students do | A compact synthetic learning-path preview with the next available activity |
| How the two cycles work | Reveal the agreed sequence as the section enters view; content order must follow confirmed research rules |
| Read a module | A small reader demonstration with a bottom-centre progress indicator moving with the simulated reading position |
| Complete short assessments | A short interface demonstration showing answer saving and a confirmed checkpoint, using fictional questions |
| Take research tests | Show an original Google Form being opened conceptually and a later verified-completion state; avoid suggesting Google Forms uses our own test design |
| Monitor progress | Student-code rows and completion states using only synthetic data; show scores separately from completion |

These are explanatory demonstration proposals, not claims that unimplemented features exist. Marketing animation must never write learner progress. The real module tracker must use actual saved reading position and the agreed completion policy, not a decorative timer.

Use motion to explain one action at a time, with normal scrolling, readable static content, reduced-motion alternatives and simplified phone behaviour. Do not force scroll capture, add long waits before access to content, or carry decorative marketing loops into every daily learning interaction. Exact durations/easing and a full palette/type/layout proposal require the next design pass and visual review.

## Asset plan

The user does not need to supply stock images for preparation. Original app previews, progress indicators, diagrams, chemistry motifs and transitions can be authored directly as HTML/CSS/SVG. These are preferable for interface demonstrations because they can reflect the actual ChemTrack design and remain sharp/responsive.

If a bitmap illustration is useful, generate an original asset with the image-generation tool; if photography is useful, find appropriately licensed images and record sources/credits. No images have been generated, selected, purchased or downloaded in this reference review. Do not adopt Pantas's branding, illustrations or study copy as ChemTrack assets.

Only request user-supplied assets when a specific official school/department logo, approved photo, branded reference or actual teacher learning material is needed. No such asset is required to plan or prototype the landing-page interactions. Scientific teaching material still requires the teacher's approved content; decorative chemistry artwork is not a substitute.

## Status and next step

Confirmed: Pantas landing-page motion is a reference, the header scrolls away, and the separate top-right menu button stays visible. Pending: any other references, the full visual direction, final copy and selection/creation of assets. No landing page, header/menu or new tracker code was changed. Build a coherent ChemTrack design from the selected references and verify its animations in a browser when viewing is available.
