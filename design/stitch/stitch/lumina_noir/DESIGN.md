# Design System Strategy: High-End Event Experiences

## 1. Overview & Creative North Star
The Creative North Star for this design system is **"The Digital Concierge."** 

This system is designed to feel like an exclusive, high-end invitation. It breaks away from the "utility-first" look of standard event apps by embracing an editorial layout style—characterized by bold, asymmetrical typography and deep, cinematic layering. We reject the generic grid in favor of "breathable" compositions where whitespace (or "dark space") is treated as a premium element. The goal is to move the user through an event agenda not as a list of tasks, but as a curated journey of discovery.

### Design Principles:
*   **Intentional Asymmetry:** Use off-center alignments for display text to create a sense of movement.
*   **Cinematic Depth:** Utilize the "Glassmorphism" effect and tonal layering to create a 3D environment that feels immersive.
*   **Neon Precision:** Use the vibrant accent color sparingly, like a laser pointer guiding the user's attention to the most critical actions.

---

## 2. Colors
Our palette is rooted in the "Deep Dark" aesthetic, utilizing a sophisticated range of near-black tones and high-energy accents.

### Palette Highlights:
*   **Background (`#0e0e0e`):** The canvas. A pure, deep void that allows imagery and accents to pop.
*   **Primary (`#f4ffc6`) & Primary Container (`#d1fc00`):** Our signature "Lime Neon." Use this for high-impact CTAs and critical states.
*   **Surface Tiers:** Use `surface-container-low` (`#131313`) through `highest` (`#262626`) to define priority.

### The "No-Line" Rule
**Explicit Instruction:** Do not use 1px solid borders to separate sections. Boundaries must be defined through background color shifts. A `surface-container-low` section sitting on a `background` provides all the structural definition required. Lines create visual noise; tonal shifts create elegance.

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers.
*   **Level 0:** `background` (#0e0e0e)
*   **Level 1:** `surface-container-low` (#131313) for main content blocks.
*   **Level 2:** `surface-container-high` (#201f1f) for nested elements like cards inside a section.

### The "Glass & Gradient" Rule
For floating elements (like the navigation bar or "Book Now" footers), use a semi-transparent `surface` color with a `backdrop-blur` (20px–40px). To add "soul," apply a subtle linear gradient to main CTAs transitioning from `primary` (#f4ffc6) to `primary-container` (#d1fc00) at a 45-degree angle.

---

## 3. Typography
The typography system relies on a high-contrast pairing between **Plus Jakarta Sans** for expressive moments and **Inter** for functional clarity.

*   **Display (Plus Jakarta Sans):** Large, bold, and tightly tracked. Display-lg (3.5rem) should be used for event titles and "hero" moments.
*   **Headlines (Plus Jakarta Sans):** Used for section headers. These should feel authoritative.
*   **Titles & Body (Inter):** Inter provides the "readable" engine of the app. Title-lg is perfect for speaker names, while Body-md handles the descriptions.
*   **Labels:** Small-cap or high-letter-spacing labels using `label-md` should be used for metadata (e.g., "STARTING IN 10 MINS").

The hierarchy conveys the brand: The large headings feel like an event poster, while the clean body text feels like a luxury program guide.

---

## 4. Elevation & Depth
In this design system, depth is a feeling, not a drop-shadow.

### The Layering Principle
Achieve hierarchy by stacking surface tokens. A `surface-container-lowest` card placed on a `surface-container-low` section creates a natural "sunken" or "lifted" look without traditional shadows.

### Ambient Shadows
When a card must "float" (e.g., a speaker card in a horizontal slider):
*   **Shadow:** Use a blurred version of the background color or a 4% opacity `on-surface` color.
*   **Blur:** High (30px+). 
*   **Spread:** Minimal. This mimics natural, ambient light.

### The "Ghost Border" Fallback
If contrast is needed for accessibility, use a **Ghost Border**: The `outline-variant` (#494847) at **15% opacity**. This provides a hint of a container without breaking the "No-Line" rule.

### Glassmorphism
Apply to components that overlap content. 
*   **Fill:** `surface` at 70% opacity.
*   **Effect:** Backdrop blur 25px.
*   **Edge:** A 1px "Ghost Border" top-edge highlight to simulate light catching the glass.

---

## 5. Components

### Lateral Day Selector (Agenda)
*   **Structure:** A horizontal scrolling list of dates.
*   **Unselected:** `on-surface-variant` text, no background.
*   **Selected:** Large `xl` (3rem) rounded capsule with `primary` (#f4ffc6) background and `on-primary` (#546600) text.
*   **Interaction:** Active dates should "grow" slightly (1.1x scale) to provide haptic visual feedback.

### Speaker Sliders (Horizontal)
*   **Visuals:** Large cards with `lg` (2rem) corner radius. 
*   **Image:** Full-bleed speaker photos with a `surface-dim` gradient overlay at the bottom for text legibility.
*   **Typography:** Use `title-lg` for names, `label-sm` for their role, anchored to the bottom-left.

### Buttons
*   **Primary:** `full` (9999px) roundedness. `primary` background. No border.
*   **Secondary:** Glassmorphism style. Semi-transparent `surface-variant` with a Ghost Border.
*   **States:** On press, reduce opacity to 80%; do not change color.

### Cards & Lists
*   **Forbid dividers.** Use `spacing-6` (2rem) to separate items.
*   **Lists:** For agenda items, use a `surface-container-low` background for the active session and a transparent background for upcoming ones.

---

## 6. Do's and Don'ts

### Do:
*   **Do** use the `xl` (3rem) corner radius for main feature cards to create a friendly yet premium feel.
*   **Do** utilize the `spacing-16` (5.5rem) or `spacing-20` (7rem) for top-level section margins to emphasize the "High-End" aesthetic.
*   **Do** use `primary_dim` for icons to ensure they don't visually overwhelm the text.

### Don't:
*   **Don't** use pure white (#ffffff) for large blocks of text; use `on-surface-variant` (#adaaaa) for secondary info to maintain the "Deep Dark" mood.
*   **Don't** use standard Material Design 1px dividers. If separation is needed, use a `px` height `surface-variant` bar at 10% opacity.
*   **Don't** cram content. If a screen feels full, increase the spacing tokens and allow the user to scroll. Space is luxury.