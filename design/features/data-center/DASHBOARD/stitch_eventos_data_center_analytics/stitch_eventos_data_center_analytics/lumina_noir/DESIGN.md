---
name: Lumina Noir
colors:
  surface: '#131313'
  surface-dim: '#131313'
  surface-bright: '#3a3939'
  surface-container-lowest: '#0e0e0e'
  surface-container-low: '#1c1b1b'
  surface-container: '#201f1f'
  surface-container-high: '#2a2a2a'
  surface-container-highest: '#353534'
  on-surface: '#e5e2e1'
  on-surface-variant: '#bbcac6'
  inverse-surface: '#e5e2e1'
  inverse-on-surface: '#313030'
  outline: '#859490'
  outline-variant: '#3c4947'
  surface-tint: '#47dcca'
  primary: '#5fefdc'
  on-primary: '#003731'
  primary-container: '#39d2c0'
  on-primary-container: '#00564e'
  inverse-primary: '#006a60'
  secondary: '#d5c5a8'
  on-secondary: '#392f1b'
  secondary-container: '#534832'
  on-secondary-container: '#c6b79b'
  tertiary: '#c9d8ff'
  on-tertiary: '#002e6b'
  tertiary-container: '#9fbcff'
  on-tertiary-container: '#0048a1'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#6af9e6'
  primary-fixed-dim: '#47dcca'
  on-primary-fixed: '#00201c'
  on-primary-fixed-variant: '#005048'
  secondary-fixed: '#f2e0c3'
  secondary-fixed-dim: '#d5c5a8'
  on-secondary-fixed: '#231a08'
  on-secondary-fixed-variant: '#504530'
  tertiary-fixed: '#d8e2ff'
  tertiary-fixed-dim: '#aec6ff'
  on-tertiary-fixed: '#001a43'
  on-tertiary-fixed-variant: '#004397'
  background: '#131313'
  on-background: '#e5e2e1'
  surface-variant: '#353534'
typography:
  display-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.1'
    letterSpacing: -0.04em
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.03em
  metric-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  body-lg:
    fontFamily: Urbanist
    fontSize: 18px
    fontWeight: '500'
    lineHeight: '1.5'
    letterSpacing: 0em
  body-md:
    fontFamily: Urbanist
    fontSize: 15px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: 0em
  body-sm:
    fontFamily: Urbanist
    fontSize: 13px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: 0.01em
  data-label:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1.4'
    letterSpacing: 0.05em
  data-mono:
    fontFamily: JetBrains Mono
    fontSize: 11px
    fontWeight: '400'
    lineHeight: '1.4'
    letterSpacing: 0em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  gutter: 16px
  margin: 24px
---

## Brand & Style

This design system is engineered for high-stakes enterprise intelligence, where clarity and focus are paramount. The aesthetic is rooted in **Linear-inspired Minimalism** with a cinematic, high-fidelity execution. It prioritizes data density and executive-level sophistication, ensuring that complex information remains the "hero" of the interface.

The personality is authoritative, precise, and unobtrusive. By stripping away extraneous shadows and decorative elements, the system relies on structured borders and tonal shifts to create a sense of depth and architectural order. The result is a workspace that feels like a precision instrument—dark, focused, and exceptionally performant.

## Colors

The color palette is built on a foundation of "True Black" and deep greys to maximize contrast for data visualization. Surfaces use incremental tonal shifts to denote hierarchy, moving from the base `#0A0A0A` to lighter elevations for interactive elements.

Borders serve as the primary structural tool. Use the `strong` border token for high-level layout containers and the `default` token for internal module separation. Accent colors are used sparingly for semantic feedback (Success, Warning, Critical) or to highlight primary actions. The `soft` variants (10% opacity) are designed for background fills in tags, badges, or selected states to maintain legibility without overwhelming the dark canvas.

## Typography

This system utilizes a tripartite typographic strategy:

1.  **Plus Jakarta Sans** is reserved for high-impact displays and numerical metrics. Its geometric clarity ensures that key performance indicators (KPIs) are immediately scannable.
2.  **Urbanist** handles all functional UI text and descriptive body copy. It provides a modern, warm balance to the technical environment.
3.  **JetBrains Mono** is used for all technical metadata, chart labels, and table values. The monospaced nature ensures that columns of numbers align perfectly for easy comparison.

Use `text-secondary` for body copy and `text-tertiary` for secondary labels to maintain visual hierarchy. All display text should favor tight letter spacing to enhance the premium, cinematic feel.

## Layout & Spacing

The layout follows a **Fluid Grid** model designed for high-density information displays. A standard 12-column grid is used for dashboard layouts, allowing widgets to span variable widths (typically 3, 4, 6, or 12 columns).

A strict 4px baseline rhythm is enforced across the system. Dashboards should utilize `spacing-md` (16px) for gutters between widgets and `spacing-lg` (24px) for page margins. To maintain the "Data as Hero" philosophy, internal padding within cards should be generous (`spacing-lg`) to prevent information crowding, while list density remains compact (`spacing-sm` vertical padding).

## Elevation & Depth

In this design system, depth is achieved through **Tonal Layering** and **Low-Contrast Outlines** rather than shadows. 

The background (`bg`) sits at the lowest level. Content containers (cards, widgets) use the `rgba(255,255,255,0.03)` fill and are defined by a `border-default`. To indicate a higher level of focus—such as a modal or a floating popover—the surface should shift to `bg-3` and utilize the `border-strong` token. 

Hover states are communicated through a subtle increase in surface opacity (`card-hover`) and a transition to `border-high`. This creates a sense of "illuminating" the UI from within, consistent with the cinematic theme.

## Shapes

The shape language is disciplined and professional. A standard radius of **8px** is applied to most interactive components (buttons, input fields, and widgets). 

Smaller elements like tags, checkboxes, and nested utility buttons use the **6px** radius. Larger structural containers, such as primary dashboard sections or modals, use the **12px** radius to provide a softer frame for the sharp data within. This "nested rounding" technique (smaller radii inside larger ones) maintains a cohesive, high-end aesthetic.

## Components

### Buttons
- **Primary:** Background of `--teal`, text of `--bg`. Bold, condensed typography.
- **Secondary:** Transparent background, `--b-st` border, `--t` text.
- **Ghost:** Transparent background, no border, `--t2` text. Shifts to `card-hover` on hover.

### Cards & Widgets
- Surface: `rgba(255,255,255,0.03)`.
- Border: `--b`.
- Padding: `24px` internal padding. 
- Header: Separated by a thin `--b` stroke with `JetBrains Mono` labels.

### Input Fields
- Background: `--bg-1`.
- Border: `--b`. 
- Focus State: Border transitions to `--teal` with no glow/shadow.
- Text: `--t` for input, `--t3` for placeholder.

### Data Tables
- Header: `--bg-2` background, `--t3` uppercase `JetBrains Mono` text.
- Row: `--bg` background with a bottom border of `--b`.
- Hover: Row background shifts to `card-hover`.

### Chips & Badges
- Style: Use the "Soft Variant" logic. For example, a "Success" badge uses `--green-soft` background and `--green` text. No borders.

### Chart Containers
- Charts must use the 8-color palette in sequence. 
- Grid lines within charts must use `--b` at 50% opacity. 
- Tooltips should use the `--bg-3` surface with a `--b-st` border.