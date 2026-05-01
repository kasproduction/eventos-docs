# Design System Strategy: The Nocturnal Concierge

## 1. Overview & Creative North Star
The Creative North Star for this design system is **"The Nocturnal Concierge."** 

This system is designed to transform a functional enterprise kiosk into a silent, high-end presence. It avoids the cluttered, utility-first appearance of standard office hardware, opting instead for the atmospheric depth of a luxury airport lounge or a Tesla dashboard. 

To break the "template" look, we utilize **intentional asymmetry**. Do not center-align everything; use generous, sweeping negative space to create a "gallery" feel. Information should feel "curated" rather than "displayed." We lean heavily on wide-tracking typography, varying opacities of white, and tonal layering to provide hierarchy without the noise of traditional UI elements.

---

## 2. Colors & Tonal Depth
The palette is rooted in the "Deep Noir" aesthetic. The core experience lives in the shadows, where visibility is earned through contrast and status is signaled through light.

### The "No-Line" Rule
Explicitly prohibit the use of 1px solid, high-contrast borders for sectioning. Boundaries must be defined through background color shifts or subtle tonal transitions. For example, a card should not have a stroke; it should sit as a `surface-container-lowest` (#0d0d17) element on a `surface` (#12121d) background.

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers of tinted glass.
- **Base Layer:** `surface` (#12121d)
- **Deep Recess:** `surface-container-lowest` (#0d0d17) — Use for inactive or "sunken" content areas.
- **Elevated Surfaces:** `surface-container-high` (#292934) or `highest` (#34343f) — Use for active interactive cards.

### The "Glass & Gradient" Rule
To achieve the Tesla-inspired polish, use Glassmorphism for floating modules. Apply `surface-container-highest` with a 40-60% opacity and a backdrop-blur of 20px–40px. 
**Signature Texture:** Main CTA areas or status headers may use a subtle linear gradient from `secondary` (#6edba7) to `secondary-container` (#30a374) at a low opacity (10-15%) to give a soft, emerald "soul" to the darkness.

---

## 3. Typography: The Editorial Voice
Typography is the primary driver of this system. With no icons allowed, the weight, scale, and tracking of your text must communicate everything.

*   **Display & Headlines (Plus Jakarta Sans):** These are your "Statement" pieces. For `display-lg` and `display-md`, use tighter letter-spacing (-0.02em) to create an authoritative, premium look.
*   **Body & Labels (Manrope):** Use Manrope for high readability in technical data. Increase letter-spacing for `label-sm` to 0.05em to evoke a "luxury watch" engraving feel.
*   **The Opacity Scale:**
    *   **High Emphasis:** `on-surface` (100% white)
    *   **Medium Emphasis:** `on-surface-variant` (70% white)
    *   **Disabled/Placeholder:** `outline` (38% white)

---

## 4. Elevation & Depth
We eschew traditional drop shadows for **Tonal Layering** and **Ambient Glows**.

*   **The Layering Principle:** Depth is achieved by "stacking." A `surface-container-low` button on a `surface` background creates a natural, soft lift.
*   **Ambient Status Glows:** For "Live" or "Occupied" states, do not just use a green dot. Use a large, diffused glow behind the text or at the edge of the container using `secondary` (#6edba7) with a 60px blur at 10% opacity.
*   **The "Ghost Border" Fallback:** If a container requires definition for accessibility, use the `outline-variant` token at 10% opacity. This "Ghost Border" provides a hint of structure without breaking the noir atmosphere.

---

## 5. Components

### Buttons
*   **Primary:** Fill with `primary` (#c6c6c7) and text in `on-primary` (#2f3131). Use `rounded-sm` (0.125rem) for a sharp, architectural look.
*   **Secondary/Status:** A glassmorphic button using `surface-container-high` with a subtle `secondary` (#6edba7) glow effect.
*   **States:** On hover/touch, increase the opacity of the background shift rather than changing the color entirely.

### Chips & Status Indicators
*   **Live Status:** Use `secondary` (#6edba7) text in all-caps `label-md`. No icons. Surround the text with a soft emerald ambient glow to indicate the room is "active."
*   **Selection Chips:** Use `surface-container-highest` with a `Ghost Border` for unselected states; fill with `primary` for selected.

### Cards & Lists
*   **Forbidden:** Divider lines. 
*   **Alternative:** Use vertical white space (32px or 48px from the spacing scale) to separate list items. For complex data, use alternating backgrounds of `surface` and `surface-container-low`.
*   **Glass Cards:** Use for the main meeting information. Apply `xl` (0.75rem) corner radius to floating cards to contrast against the `sm` radius of functional buttons.

### Input Fields
*   **High-End Minimalist:** No box. Only a bottom border using `outline-variant` at 20% opacity. Upon focus, the border transitions to 100% white (`on-surface`) with a slight upward "lift" of the label using `label-sm`.

---

## 6. Do's and Don'ts

### Do
*   **Do** use extreme contrast in typography sizes (e.g., a `display-lg` time next to a `label-sm` date).
*   **Do** use "Emerald Green" (`secondary`) sparingly. It is a beacon, not a decorative element.
*   **Do** allow elements to bleed off-center to create a modern, editorial layout.
*   **Do** use `manrope` for all numerical data to ensure technical clarity.

### Don't
*   **Don't** use icons. If you need to indicate "Settings," use the word "SETTINGS" in wide-tracked `label-sm`.
*   **Don't** use pure black (#000000). Always use the Deep Noir `surface` (#12121d) or `surface-container-lowest` (#0d0d17) to maintain tonal depth.
*   **Don't** use standard "Material Design" ripples. Use subtle opacity fades (200ms ease-in-out) for interactions.
*   **Don't** crowd the screen. If the room is empty, the screen should be 90% "Deep Noir" with only the most vital information visible.