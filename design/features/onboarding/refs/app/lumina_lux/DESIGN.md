# Design System Specification: High-End Light Editorial

## 1. Overview & Creative North Star

### Creative North Star: "The Ethereal Architect"
This design system is built upon the philosophy of **Ethereal Architecture**. Unlike standard mobile apps that rely on rigid boxes and heavy borders, this system treats the screen as a gallery space. It is characterized by high-contrast focal points, vast amounts of "negative" breathing room, and a kinetic energy driven by a vibrant neon accent.

To move beyond a "template" look, we utilize **Intentional Asymmetry**. Key elements are often offset or layered with varying depths to guide the eye through a narrative rather than a grid. By prioritizing pure white (#FFFFFF) as a structural foundation and using neon green (#D4FF00) as a precision tool for interaction, we create a digital environment that feels premium, tech-forward, and unequivocally custom.

---

## 2. Colors

The palette is designed to maximize the brilliance of the primary accent against a clinical, sophisticated background.

### Core Palette
- **Primary:** `#516200` (for deep contrast)
- **Primary Accent:** `#D4FF00` (The Neon High-Light)
- **Background:** `#FFFFFF` (Pure white for maximum clarity)
- **Surface:** `#F5F6F7` (Soft gray for secondary logic)

### The "No-Line" Rule
**Explicit Instruction:** Use of 1px solid borders for sectioning is strictly prohibited. Boundaries must be defined through background color shifts. For example, a `surface-container-low` card sits on a `surface` background. If an element requires separation, use a tonal shift or a soft shadow—never a stroke.

### Surface Hierarchy & Nesting
Treat the UI as physical layers of frosted glass.
- **Surface Lowest (#FFFFFF):** The base level for main content areas.
- **Surface Low (#EFF1F2):** For nested containers or secondary modules.
- **Surface Highest (#DADDDF):** For tertiary elements or interactive states.

### The "Glass & Gradient" Rule
Floating elements (Modals, Navigation Bars) should utilize **Glassmorphism**. Apply a semi-transparent surface color with a `20px` to `40px` backdrop-blur. To add "visual soul," use subtle linear gradients for primary CTAs, transitioning from `primary` (#516200) to `primary-container` (#D1FC00) at a 135-degree angle.

---

## 3. Typography

The system utilizes **Plus Jakarta Sans** for its geometric clarity and contemporary rhythm.

| Role | Font Size | Weight | Letter Spacing | Case |
| :--- | :--- | :--- | :--- | :--- |
| **Display LG** | 3.5rem | Bold | -0.02em | Sentence |
| **Headline MD** | 1.75rem | Bold | -0.01em | Sentence |
| **Title LG** | 1.375rem | Medium | 0 | Sentence |
| **Body LG** | 1.0rem | Regular | 0 | Sentence |
| **Label MD** | 0.75rem | Bold | **0.1em** | ALL CAPS |

**Editorial Intent:** Use the Display scale aggressively for header titles to create an authoritative "Editorial" feel. Labels must always use the 0.1em tracking (letter spacing) to provide a premium, luxury-brand aesthetic.

---

## 4. Elevation & Depth

We eschew traditional drop shadows in favor of **Tonal Layering** and **Ambient Light**.

- **The Layering Principle:** Depth is achieved by stacking. Place a `surface-container-lowest` card on a `surface-container-low` section to create a soft, natural lift.
- **Ambient Shadows:** For floating action buttons or high-priority cards, use "Air-Light" shadows.
    - **Shadow Spec:** `0px 24px 48px rgba(44, 47, 48, 0.06)`
    - This shadow is large, diffused, and almost imperceptible, mimicking natural ambient light.
- **The "Ghost Border" Fallback:** If accessibility requires a container edge, use the `outline-variant` token at **10% opacity**. Never use 100% opaque lines.
- **Glassmorphism:** Use `backdrop-filter: blur(20px)` on any surface with <100% opacity to allow background colors to "bleed" through, softening the interface.

---

## 5. Components

### Buttons
- **Primary:** Full rounded (`9999px`), Background: `#D4FF00`, Text: `#2C2F30` (Bold).
- **Secondary:** Glass-style. Background: `rgba(255, 255, 255, 0.2)`, Backdrop Blur: `10px`, Ghost Border at 20% opacity.
- **Padding:** Vertical `1.2rem`, Horizontal `2.75rem` (Spacing 8).

### Cards & Lists
- **Rule:** Forbid divider lines.
- **Implementation:** Separate list items with `1rem` of vertical whitespace (Spacing 3). For cards, use `xl` (3rem) or `lg` (2rem) corner radii to create a friendly, high-tech silhouette.

### Input Fields
- **Style:** Pure white background with a subtle `surface-container-low` bottom-weighted shadow. 
- **Active State:** Instead of a border, the label shifts to the Primary Accent color (#D4FF00).

### Bottom Navigation (Signature Component)
- **Style:** Floating Glassmorphism bar. 
- **Design:** Centered, detached from the bottom edge by `spacing-5`. High backdrop blur with no border. Active icons utilize a small neon green dot indicator below the icon rather than changing the icon color entirely.

---

## 6. Do’s and Don’ts

### Do
- **DO** use generous white space. If you think there is enough space, add 20% more.
- **DO** use "Full" rounded corners for buttons and "Extra Large" (3rem) for main content cards.
- **DO** use the neon accent sparingly—like a laser pointer—to direct attention to the most critical action.

### Don’t
- **DON’T** use black text. Use `on-surface` (#2C2F30) for better readability against pure white.
- **DON’T** use hamburger menus. Keep navigation flat and accessible within the bottom tab bar.
- **DON’T** use standard 4px or 8px corner radii. It looks "off-the-shelf." Go large (32px+) or go full (999px).
- **DON’T** use dividers. If content needs to be separated, use a background color block or a spacing increase.

---

## 7. Spacing Scale

The system relies on a mathematical 0.35rem base for tight, editorial alignment.

- **Micro (1):** 0.35rem
- **Small (2):** 0.7rem
- **Standard (3):** 1.0rem (The "Gutter" default)
- **Large (6):** 2.0rem
- **Hero (10):** 3.5rem (For section vertical padding)