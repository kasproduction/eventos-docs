# Design System Specification: Cinematic Immersion

## 1. Overview & Creative North Star: "The Digital Aurora"
This design system is built to transcend the utility of a standard event app, moving into the realm of a premium digital concierge. The Creative North Star, **"The Digital Aurora,"** dictates an experience that feels atmospheric, deep, and illuminated from within. 

We break the "template" look by rejecting rigid grids in favor of **intentional asymmetry** and **overlapping glass planes**. By leveraging high-contrast typography scales and cinematic depth, we ensure that the "EventOS Summit" feels less like a database of schedules and more like an exclusive, high-tech gallery. Elements should feel as though they are floating in a dark, infinite void, held together by light and gravity rather than lines and boxes.

---

## 2. Colors & Atmospheric Depth
Our palette is rooted in the "Deepest Void" (#08080a), using gradients to simulate light catching on glass edges.

### The "No-Line" Rule
**Standard 1px solid borders are strictly prohibited for sectioning.** Boundaries must be defined through background tonal shifts. Use `surface-container-low` for secondary sections sitting on the `background` to create soft, organic separation.

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers. Depth is achieved by "nesting" tokens:
*   **Base Level:** `surface` (#0e0e10) - The infinite floor.
*   **Section Level:** `surface-container-low` (#131316) - Subtle grouping.
*   **Interactive Level:** `surface-container-high` (#1f1f22) - Primary cards/modules.
*   **Floating Level:** `surface-bright` (#2c2c2f) - Menus and tooltips.

### The "Glass & Gradient" Rule
To achieve the premium cinematic aesthetic:
*   **Gradients:** Use a linear transition from `primary` (#b79fff) to `secondary` (#8a95ff) at a 135-degree angle for all primary CTAs and hero highlights.
*   **Signature Textures:** Apply a subtle 20% opacity noise texture over `surface-container` elements to mimic high-end materials.

---

## 3. Typography: Editorial Authority
We pair the geometric confidence of **Plus Jakarta Sans** with the sleek, readable warmth of **Urbanist** (implemented here via the Manrope scale for technical consistency).

*   **Display & Headlines (Plus Jakarta Sans):** These are your "Statement" pieces. Use `display-lg` for keynote titles with tight letter-spacing (-0.02em). Headlines should command attention, often appearing in `on-primary-fixed` when used over gradient backgrounds.
*   **Body & Titles (Urbanist/Manrope):** All functional information lives here. `body-lg` is your workhorse for session descriptions.
*   **Labeling:** `label-sm` is reserved for metadata (e.g., timestamps, room numbers), always in uppercase with +0.05em tracking to maintain a high-end "technical" feel.

---

## 4. Elevation & Depth: Tonal Layering
Traditional drop shadows are too "web 2.0" for this system. We use light and transparency to define space.

*   **The Layering Principle:** Instead of a shadow, place a `surface-container-highest` card inside a `surface-container-low` wrapper. The delta in luminance creates "Natural Lift."
*   **Ambient Glows:** When a floating effect is required (e.g., a "Join Stream" FAB), use an extra-diffused shadow: `box-shadow: 0 20px 40px rgba(138, 149, 255, 0.15)`. The shadow color must be a tint of `secondary`, never pure black.
*   **The "Ghost Border":** For glass containers, use a 1px stroke. This is the **only** exception to the no-line rule. The stroke must use `outline-variant` at 20% opacity. It should look like light catching the edge of a lens.
*   **Backdrop Blur:** All floating modals must use a `backdrop-filter: blur(20px)` combined with a semi-transparent `surface-container-high` (80% opacity).

---

## 5. Components: The Premium Kit

### Buttons
*   **Primary:** Gradient (`primary` to `secondary`), `xl` (1.5rem) corner radius. No border. Text is `on-primary-fixed` (Black) for maximum contrast.
*   **Secondary (Glass):** `surface-variant` at 40% opacity, 20px backdrop blur, with a "Ghost Border."
*   **Tertiary:** No background. `primary` text with an underline that only appears on hover/active states.

### Cards & Lists
*   **The "No-Divider" Mandate:** Forbid the use of horizontal rules. Use 24px or 32px of vertical whitespace to separate list items. 
*   **Event Cards:** Use `surface-container-low`. On-press, transition to `surface-container-highest` and scale the card by 0.98 for a tactile, "pressing into glass" feel.

### Input Fields
*   **Visual Style:** Minimalist. Only a bottom "Ghost Border" that transforms into a full `primary` gradient border when focused. 
*   **Floating Labels:** Use `label-md` in `on-surface-variant`.

### Keynote Highlights (Custom Component)
*   A large-format `display-md` headline overlapping a high-resolution speaker image. The image should have a gradient mask transitioning from 0% opacity to 100% `background` (#08080a) to blend seamlessly into the UI.

---

## 6. Do's and Don'ts

### Do
*   **Do** allow elements to bleed off the edge of the screen (e.g., horizontal carousels) to imply a larger world.
*   **Do** use asymmetrical margins (e.g., 24px left, 16px right) for headline-heavy screens to create an editorial layout.
*   **Do** use `primary` for accent icons but keep the majority of secondary icons in `on-surface-variant`.

### Don't
*   **Don't** use pure white (#ffffff). Use `on-background` (#fffbfe) to avoid "retina burn" in dark mode.
*   **Don't** use standard Material Design "elevations" (dp1, dp2). Stick to the Surface Nesting tiers defined in Section 2.
*   **Don't** use sharp corners. Everything must adhere to the `md` (0.75rem) or `xl` (1.5rem) roundedness scale to feel "human" and high-end.
*   **Don't** cram content. If a screen feels "busy," increase the vertical spacing. Silence is as important as the content.