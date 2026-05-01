---
name: Event Pulse
colors:
  surface: '#f5faf8'
  surface-dim: '#d6dbd9'
  surface-bright: '#f5faf8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f0f5f2'
  surface-container: '#eaefed'
  surface-container-high: '#e4e9e7'
  surface-container-highest: '#dee4e1'
  on-surface: '#171d1c'
  on-surface-variant: '#3d4947'
  inverse-surface: '#2c3130'
  inverse-on-surface: '#edf2f0'
  outline: '#6d7a77'
  outline-variant: '#bcc9c6'
  surface-tint: '#006a61'
  primary: '#00685f'
  on-primary: '#ffffff'
  primary-container: '#008378'
  on-primary-container: '#f4fffc'
  inverse-primary: '#6bd8cb'
  secondary: '#695d46'
  on-secondary: '#ffffff'
  secondary-container: '#efdec0'
  on-secondary-container: '#6d614a'
  tertiary: '#924628'
  on-tertiary: '#ffffff'
  tertiary-container: '#b05e3d'
  on-tertiary-container: '#fffbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#89f5e7'
  primary-fixed-dim: '#6bd8cb'
  on-primary-fixed: '#00201d'
  on-primary-fixed-variant: '#005049'
  secondary-fixed: '#f2e0c3'
  secondary-fixed-dim: '#d5c5a8'
  on-secondary-fixed: '#231a08'
  on-secondary-fixed-variant: '#504530'
  tertiary-fixed: '#ffdbce'
  tertiary-fixed-dim: '#ffb59a'
  on-tertiary-fixed: '#370e00'
  on-tertiary-fixed-variant: '#773215'
  background: '#f5faf8'
  on-background: '#171d1c'
  surface-variant: '#dee4e1'
typography:
  display-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 64px
    fontWeight: '800'
    lineHeight: '1.1'
    letterSpacing: -0.04em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Manrope
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '500'
    lineHeight: '1.5'
  stats-num:
    fontFamily: Manrope
    fontSize: 32px
    fontWeight: '600'
  label-caps:
    fontFamily: Manrope
    fontSize: 12px
    fontWeight: '600'
    letterSpacing: 0.1em
rounded:
  sm: 0.5rem
  DEFAULT: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  full: 9999px
spacing:
  unit: 8px
  container-padding: 64px
  gutter: 32px
  widget-gap: 40px
  section-margin: 80px
---

## Brand & Style

The design system is anchored in the "Living Canvas" philosophy—a concept where the interface acts as a silent, premium gallery for dynamic event data. It prioritizes content over chrome, utilizing an editorial layout that feels more like a high-end magazine than a traditional dashboard. 

The personality is sophisticated, calm, and authoritative. It draws heavily from Apple-esque minimalism, focusing on intentionality in every pixel. By stripping away non-essential decorations, the design system ensures that "Event Pulse" feels lightweight yet powerful. The emotional response should be one of "effortless control," where the user feels they are orchestrating complex logistics through a serene, tactile medium.

## Colors

The palette for this design system is architectural and restrained. The background is a crisp, cool gray that provides a neutral foundation for pure white surfaces to "float" upon. 

- **Primary Teal (#0D9488):** Used sparingly for primary actions and critical status indicators. It represents vitality and "live" data.
- **Accent Platinum (#B5A68B):** A sophisticated metallic tone used for secondary accents, high-end borders, or decorative elements that require a premium feel without the weight of black or the vibrancy of teal.
- **Text Hierarchy:** Established through opacity rather than distinct hex codes, ensuring a harmonious tonal relationship with the primary brand black (#1A1A1A).

## Typography

Typography in the design system follows an editorial hierarchy. **Plus Jakarta Sans** provides a modern, slightly rounded geometric feel for headlines, conveying a welcoming yet professional tone. For body text, **Manrope** is utilized for its exceptional legibility and balanced proportions (substituting for Urbanist to ensure a more systematic SaaS feel while maintaining the required weights).

Large data points and metrics must utilize `tabular-nums` to ensure vertical alignment in dashboards. Leading is generous across all levels to prevent visual crowding, supporting the "breathing room" core principle.

## Layout & Spacing

The layout philosophy is based on a **Fixed-Max Fluid Grid**. While the content can stretch, it is constrained by significant horizontal margins (64px+) to maintain an editorial "columnar" feel. 

Spacing is intentionally oversized. Instead of standard 16px or 24px gaps, this design system pushes widget spacing to 40px to create distinct "islands of information." This creates a sense of luxury—space is treated as a premium commodity. Elements should never feel cramped; if in doubt, increase the white space.

## Elevation & Depth

Depth is achieved through **Ambient Shadows** and tonal layering. The design system rejects harsh outlines in favor of multi-layered soft shadows that mimic natural light.

Surfaces (#FFFFFF) sit atop the cool gray background (#F6F8FA). To indicate elevation, use a triple-stack shadow:
1. A very broad, low-opacity blur (e.g., 40px blur at 2% opacity).
2. A mid-range shadow to define the object (e.g., 20px blur at 4% opacity).
3. A tight, slightly darker "anchor" shadow (e.g., 4px blur at 4% opacity).

This creates a "Living Canvas" effect where widgets appear to be subtly hovering just millimeters above the background.

## Shapes

The shape language is defined by extreme softness. All primary containers and widgets must use a minimum corner radius of **24px**. This high roundedness removes "sharpness" from the data, making the platform feel approachable and high-end.

Secondary elements like buttons or small chips should follow a **Pill-shaped** (fully rounded) convention. This contrast between large 24px+ squircle-like widgets and pill-shaped interactive elements helps users intuitively distinguish between "content containers" and "actionable triggers."

## Components

### Buttons
Primary buttons use the Accent Teal (#0D9488) with white text, featuring a pill-shaped silhouette. Secondary buttons use a subtle Platinum (#B5A68B) tint or are entirely ghost-styled with a subtle 5% opacity fill. Hover states should involve a gentle upward "lift" (increased shadow) rather than a color shift.

### Cards & Widgets
The fundamental unit of the design system. Cards must have a 24px+ radius, white fill, and the multi-layered shadow described in the Elevation section. Internal padding should be a minimum of 32px.

### Input Fields
Inputs are borderless. They utilize a slightly darker tint of the background color or a very thin 1px Platinum border at 20% opacity. Focus states are indicated by a subtle glow of the Primary Teal.

### Chips & Tags
Used for event categories or status. These are always pill-shaped. Use the Platinum color at low opacities (10-15%) for the background with Secondary Text for the label to keep them "muted" and secondary to the primary content.

### Living Metrics
Large-scale numerical displays using the `stats-num` typography. These should be placed at the top of editorial layouts with ample margin-bottom to emphasize their importance.