# EventOS Data Center — Enterprise Analytics Dashboard

## Why this matters

EventOS competes against Cisco Webex Events ($88K/year), Cvent, Bizzabo, Swapcard. After researching every competitor's analytics dashboard, they ALL look like generic Salesforce/BI tools — functional but forgettable. Gray backgrounds, basic charts, Excel-style tables. Nobody has invested in making analytics feel premium.

Data Center is EventOS's chance to visually dominate. When an organizer opens this in a board room to show a sponsor their ROI from a $50K sponsorship, it needs to feel worth every dollar. Not "here's your spreadsheet" — "here's your intelligence briefing."

**Design philosophy**: Linear's restraint + Grafana's data density + Stripe's token discipline + the cinematic language of our own Event Pulse. Data is the hero. Everything else serves it.

---

## Design System: Lumina (already exists, must use exactly)

EventOS has a dual-theme system used across Mission Control, Event Pulse, and Kiosk. Data Center MUST use the same tokens.

### Lumina Noir (dark — design this first)

```
Surfaces:
  --bg:           #0A0A0A          Base background
  --bg-1:         #0E0E0E          Card/surface
  --bg-2:         #141414          Elevated
  --bg-3:         #1A1A1A          Highest elevation
  --card:         rgba(255,255,255,0.03)
  --card-hi:      rgba(255,255,255,0.05)   Hover state

Borders:
  --b:            rgba(255,255,255,0.06)    Default
  --b-hi:         rgba(255,255,255,0.08)    Hover
  --b-st:         rgba(255,255,255,0.14)    Strong/active

Text (opacity on white):
  --t:            #FFFFFF                    Primary
  --t2:           rgba(255,255,255,0.6)      Secondary
  --t3:           rgba(255,255,255,0.4)      Tertiary
  --t4:           rgba(255,255,255,0.25)     Quaternary
  --t5:           rgba(255,255,255,0.15)     Ghost

Semantic accents:
  --teal:         #39D2C0          Engagement, gamification, live indicators
  --green:        #3BBF7A          Success, check-in, positive delta
  --red:          #DC4A4A          Error, negative, danger
  --amber:        #E8A93E          Warning, processing, attention
  --blue:         #5B8DEF          Virtual, info, secondary accent
  --platinum:     #B5A68B          Premium, sponsors, gold/1st place, stars

Soft variants (for chart fills, badges, pill backgrounds):
  --teal-soft:    rgba(57,210,192, 0.10)
  --green-soft:   rgba(59,191,122, 0.10)
  --red-soft:     rgba(220,74,74, 0.10)
  --amber-soft:   rgba(232,169,62, 0.10)
  --blue-soft:    rgba(91,141,239, 0.10)
  --plat-soft:    rgba(181,166,139, 0.10)

Border radius:
  8px standard, 6px small, 12px large

Typography:
  Display/Numbers:  Plus Jakarta Sans  (weights: 600, 700, 800)
  Body:             Urbanist           (weights: 400, 500, 600)
  Data/Labels:      JetBrains Mono     (weights: 400, 500)
```

### Chart Color Palette (8 colors, ordered by priority)

Following the 8-color rule for data visualization accessibility:

```
1. #39D2C0   Teal        Primary chart color, engagement data
2. #B5A68B   Platinum    Sponsor data, premium highlights
3. #5B8DEF   Blue        Virtual/secondary series
4. #3BBF7A   Green       Positive/growth data
5. #E8A93E   Amber       Warning/attention data
6. #A78BFA   Violet      Gamification, tertiary series
7. #F472B6   Pink        Social/photo data
8. #94A3B8   Slate       Neutral/baseline/comparison
```

For chart fills: use the color at 15% opacity as gradient fill, solid for lines/bars.
Grid lines inside charts: rgba(255,255,255, 0.04)
Axis labels: JetBrains Mono 9px, --t4 color

---

## What to design

**3 frames, all at 1440x900, Noir theme:**

1. **Frame 1**: Full dashboard with tab "Asistentes y Acceso" active
2. **Frame 2**: Full dashboard with tab "Patrocinadores y Leads" active (THE most important for business)
3. **Frame 3**: Full dashboard with tab "Gamificacion" active (shows podium + unique charts competitors don't have)

---

## Layout Structure (all 3 frames share this shell)

```
┌─────────────────────────────────────────────────────────────────────┐
│  HEADER BAR (48px height, --bg-1 background, bottom --b border)    │
│                                                                     │
│  ┌───────────────────────────────────────────────────────────────┐  │
│  │ ● DATA CENTER   │  Summit Empresarial 2026                   │  │
│  │   (brand dot,      25-27 Abr · 1,247 registrados            │  │
│  │    breathing)      Bogota, Colombia                          │  │
│  │                                              [▼Evento] [🔔3]│  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  HERO METRICS (4 cards, single row, 16px gap)                      │
│  Each card: 48px icon area + value 32px + label 10px mono         │
│  + micro-visual (sparkline / progress bar / mini donut / stars)    │
│                                                                     │
│  TAB BAR (9 tabs, horizontal, icon + label + count badge)          │
│  Active tab: --teal underline 2px + --t color                      │
│  Inactive: --t3, hover --t2                                        │
│                                                                     │
│  TAB CONTENT (scrollable main area, the dashboard itself)          │
│  Charts in cards with --bg-1 background, --b border, 12px radius  │
│  Each chart card has a title area and an "Exportar" text link      │
│                                                                     │
│  FOOTER (24px, --t5 text: "EventOS Data Center · Cache: 42s")     │
└─────────────────────────────────────────────────────────────────────┘
```

### Header Details

- **Brand mark**: 6px circle, --teal color, subtle glow (box-shadow 0 0 8px teal-soft), breathing animation 2.4s
- **"DATA CENTER"**: JetBrains Mono, 11px, weight 500, uppercase, letter-spacing 0.16em, --t2 color
- **Vertical divider**: 1px, 24px height, --b color
- **Event name**: Plus Jakarta Sans, 15px, weight 700, --t color
- **Event meta**: Urbanist, 12px, --t3 color, dot separators
- **Event selector**: Small dropdown, --bg-2 background, --b border, 8px radius
- **Notification bell**: Material Symbols icon "notifications", 18px, --t3 color, with a teal badge "3" (9px mono, teal bg, white text, 14px circle, top-right of icon)

### Hero Metric Cards (always visible above tabs)

4 cards filling the width. Each card:
- Background: --bg-1
- Border: 1px solid --b, radius 12px
- Padding: 20px
- Left border accent: 3px solid (color varies by category)
- Layout: icon top-left (18px, --t4) → value center (32px, Plus Jakarta Sans 800, --t, tabular-nums) → label bottom (10px, JetBrains Mono 500, uppercase, 0.14em spacing, --t4) → micro-visual right or below value

| Card | Left border | Value | Label | Micro-visual |
|------|------------|-------|-------|-------------|
| 1 | --teal | 1,247 | REGISTRADOS | Sparkline: tiny area chart (40px wide, 20px tall) showing registration curve over 4 weeks. Fill teal-soft, line teal 1px. |
| 2 | --green | 892 | CHECK-INS | Progress bar: 4px tall, track --bg-3, fill --green, showing 71.5%. Small "71.5%" text right of bar in mono 9px --t4. |
| 3 | --platinum | 4.6 | RATING PROMEDIO | 5 mini stars (10px each) in --platinum, 5th star partially filled (60%). Below: "892 reviews" in mono 9px --t4. |
| 4 | --teal | 156 | LEADS CAPTURADOS | Delta badge: pill with "+8 hoy" in green-soft bg, green text, mono 9px. |

Hover: border-color transitions to --b-hi, translateY(-1px), very subtle.

### Tab Bar

- 9 tabs in a single row, horizontally scrollable
- Each tab: Material Symbols icon (16px, --t4) + label (Urbanist 13px, weight 500) + count badge (mono 9px, --bg-3 pill for inactive, teal-soft + teal text for active)
- Active tab: text --teal, icon --teal, bottom border 2px --teal
- Inactive: text --t3, hover text --t2
- Separator below tabs: 1px --b

Tab definitions:
```
group        Asistentes     6
calendar_month  Sesiones    5
chat         Engagement     5
storefront   Patrocinadores 7
emoji_events Gamificacion   10
photo_library Social        4
handshake    Networking     2
campaign     Comunicaciones 3
shield       Auditoria      2
```

### Chart Card Pattern (used for all charts)

Every chart lives in a card:
- Background: --bg-1
- Border: 1px solid --b
- Border-radius: 12px
- Padding: 20px 24px
- Title area: Plus Jakarta Sans 14px weight 700, --t color. Below title: JetBrains Mono 9px, --t4, uppercase — subtitle or data count.
- Export link: bottom-right corner, "Exportar ↓" text in Urbanist 11px weight 600 --t4, hover --t3. This is NOT a button — just a text link. The chart is the star, not the export action.
- Hover on card: border-color --b-hi

---

## Frame 1: Tab "Asistentes y Acceso"

Section eyebrow: "ASISTENTES Y ACCESO" in JetBrains Mono 10px, --t4, uppercase, 0.16em spacing. Thin line extends right. "6 datasets" at end.

### Row 1: Two charts, 50/50 split, 16px gap

**Chart A — Donut: "Estado de Check-in"**

Card with donut chart on the left half, legend on the right half.

Donut specs:
- 200px outer diameter, 30px stroke width
- 3 segments with 3px gap between each:
  - Checked-in: 71.5% — color --teal
  - Pendiente: 18.0% — color --t5 (ghost)
  - No-show: 10.5% — color --red at 40% opacity
- Center of donut: "892" in Plus Jakarta Sans 800, 32px, --t. Below: "de 1,247" in mono 10px --t4.

Legend (right of donut, vertically centered):
- Each row: colored dot 8px + label (Urbanist 12px --t2) + count (mono 12px --t) + percentage (mono 11px --t4)
  - ● Checked-in · 892 · 71.5%
  - ● Pendiente · 224 · 18.0%
  - ● No-show · 131 · 10.5%

**Chart B — Area Chart: "Registro en el Tiempo"**

Smooth area chart showing cumulative registrations over 6 weeks before event.

- X-axis: "Mar 1", "Mar 15", "Abr 1", "Abr 15", "Abr 25" — mono 9px --t4
- Y-axis: "0", "400", "800", "1,200" — mono 9px --t4
- Horizontal grid lines: rgba(255,255,255, 0.04) — barely visible
- Curve: smooth (cubic bezier), stroke --teal 2px
- Fill: gradient from teal-soft (top) to transparent (bottom)
- Vertical dashed line at "Abr 25" with tiny label "EVENTO" in mono 8px --t4
- Dot on final point with value "1,247" in mono 10px --t
- The curve shows organic growth accelerating as event approaches — S-curve shape

### Row 2: 60/40 split, 16px gap

**Chart C — Horizontal Bars: "Top Sesiones por Asistencia" (60% width)**

6 horizontal bars sorted highest to lowest.

Each row:
- Left: rank number (mono 12px --t4, width 20px) + session name (Urbanist 13px --t, truncated)
- Center: bar — 6px height, radius 3px, track --bg-3, fill --teal. Bar width proportional to percentage.
- Right: percentage (mono 13px --t) + absolute count (mono 10px --t4)

Data:
```
#1  Keynote: AI for Enterprise      ████████████████████ 89%  1,112
#2  Workshop: Data Strategy          ███████████████░░░░ 78%    975
#3  Panel: Leadership 2030           ████████████░░░░░░ 67%    838
#4  Networking Lunch                 ██████████░░░░░░░░ 54%    675
#5  Workshop: Cloud Native           ████████░░░░░░░░░░ 48%    600
#6  Closing Ceremony                 ██████░░░░░░░░░░░░ 38%    475
```

The bars visually create a descending staircase — immediately communicates which sessions won.

**Chart D — Quick Stats Grid (40% width)**

Single card with 6 mini-metrics in a 2x3 grid.

Each mini-metric:
- Icon: Material Symbols 16px, --t4
- Value: Plus Jakarta Sans 700, 22px, --t
- Label: mono 9px, --t4, uppercase
- Left border 2px colored by category

```
┌─────────────┬─────────────┐
│ 23 min      │ 4,312       │
│ AVG STAY    │ MENSAJES    │
│ (--teal)    │ (--teal)    │
├─────────────┼─────────────┤
│ 156         │ 89          │
│ LEADS       │ CONEXIONES  │
│ (--platinum)│ (--blue)    │
├─────────────┼─────────────┤
│ 267         │ 82%         │
│ FOTOS       │ PUSH OPEN   │
│ (--amber)   │ (--green)   │
└─────────────┴─────────────┘
```

### Row 3: Full width

**Chart E — Heatmap: "Ocupacion por Sala y Hora"**

The crown jewel. This chart tells the entire event story in one visual — where people were, when, and for how long. No competitor has this.

- X-axis: hours 08:00 to 18:00, mono 9px --t4
- Y-axis: 4 room names (Urbanist 12px --t2): "Sala Principal", "Sala A", "Sala B", "Lobby"
- Grid of rectangular cells (each = 1 hour block per room)
- Color intensity by occupancy:
  - 0 people: --bg-3 (barely visible)
  - 1-50: teal at 8%
  - 51-150: teal at 20%
  - 151-250: teal at 40%
  - 251-350+: teal at 70%
- Session labels overlaid on cells: tiny text (mono 8px, --t3), truncated — "Keynote", "Workshop A"
- Color scale legend top-right: horizontal gradient bar from --bg-3 to --teal, labels "0" and "350+"

---

## Frame 2: Tab "Patrocinadores y Leads"

This is the MOST IMPORTANT tab. A sponsor who paid $50K needs to see their ROI quantified visually. This is what the organizer shows in the post-event debrief meeting.

Section eyebrow: "PATROCINADORES Y LEADS · 7 DATASETS"

### Row 1: Sponsor Cards (4 cards, horizontal, equal width)

Each card represents one sponsor:

- Background: --bg-1, border --b, radius 12px
- Top section: Tier badge pill ("PLATINUM" / "GOLD" / "SILVER")
  - Platinum pill: --plat-soft bg, --platinum text
  - Gold pill: same but slightly different shade
  - Silver pill: rgba(192,192,192,0.1) bg, #C0C0C0 text
- Sponsor name: Plus Jakarta Sans 16px weight 700 --t
- Logo placeholder: 40px circle, colored by tier (--platinum, --teal, --blue, --t4)
- Lead count: Plus Jakarta Sans 800, 40px, --teal — this is the BIG number
- Mini progress bar: 3px, track --bg-3, fill colored by tier, showing % of total leads
- "Ultimo lead: hace 12min" — mono 9px --t4

```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ PLATINUM     │ │ GOLD         │ │ GOLD         │ │ SILVER       │
│ TC           │ │ ML           │ │ CB           │ │ DF           │
│ TechCorp     │ │ Meridian     │ │ CloudBase    │ │ DataFlow     │
│ International│ │ Labs         │ │ Solutions    │ │ Analytics    │
│              │ │              │ │              │ │              │
│    47        │ │    38        │ │    31        │ │    22        │
│   leads      │ │   leads      │ │   leads      │ │   leads      │
│ ████████░░░ │ │ ██████░░░░░ │ │ █████░░░░░░ │ │ ████░░░░░░░ │
│ hace 12min   │ │ hace 34min   │ │ hace 1h      │ │ hace 2h      │
└──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
```

### Row 2: 55/45 split

**Chart F — Horizontal Bars: "Leads por Patrocinador" (55%)**

Like Chart C but for sponsors. Bars use --platinum for #1, --teal for rest.

```
#1  TechCorp International  ████████████████████ 47
#2  Meridian Labs           ███████████████░░░░░ 38
#3  CloudBase Solutions     ████████████░░░░░░░░ 31
#4  DataFlow Analytics      █████████░░░░░░░░░░░ 22
#5  InnovateCO              ██████░░░░░░░░░░░░░░ 18
```

**Chart G — Donut: "Leads por Calificacion" (45%)**

- 3 segments: Hot (--teal, 35%), Warm (--amber, 45%), Cold (--t4, 20%)
- Center: "156" in 28px Jakarta 800, below "total leads" mono 10px
- Legend right of donut

### Row 3: 50/50

**Chart H — Area Chart: "Captura de Leads en el Tiempo" (50%)**

- X-axis: hours of event day (09:00 to 17:00)
- Area fill: --platinum at 15% opacity, line --platinum 2px
- Shows spikes after keynotes (labeled with tiny annotations)
- Peak labeled: "Peak: post-keynote 11:30"

**Chart I — Barras: "Visitas a Stands" (50%)**

- Vertical bars (not horizontal). Each bar = sponsor stand.
- Bar color: --platinum. Height proportional to visit count.
- Below each bar: sponsor initial + visit count
- Shows which stands attracted most foot traffic

### Row 4: Full width — THE MONEY SHOT

**Table: "Resumen ROI por Patrocinador"**

This is NOT a generic HTML table. It is a premium data table with:
- Header row: mono 9px, uppercase, --t4, letter-spacing 0.12em
- Data rows: alternating --bg-1 / --bg-2 subtle, 48px row height
- First column: sponsor logo circle (28px) + name (Urbanist 13px weight 600)
- Tier column: colored pill badge
- Numbers aligned right with tabular-nums
- Last column "Score ROI": horizontal mini-bar (40px wide, 4px tall) colored by score level
- Row #1 (highest ROI) has a subtle --plat-soft background tint
- Below the table: prominent button "Exportar Leads Master" — this one IS prominent (--platinum bg, dark text, 36px height, 14px weight 700). This is the exception where export is primary.

```
PATROCINADOR        TIER       LEADS  VISITAS  TRIVIA  CONTACTOS  ROI
────────────────────────────────────────────────────────────────────────
TC  TechCorp Int.   PLATINUM    47     312      89      12       ████
ML  Meridian Labs   GOLD        38     267      67       8       ███░
CB  CloudBase Sol.  GOLD        31     198      54       6       ██░░
DF  DataFlow Ana.   SILVER      22     145      32       4       ██░░
IN  InnovateCO      SILVER      18      98      21       2       █░░░
```

---

## Frame 3: Tab "Gamificacion y Juegos"

This is what NO competitor has. Gamification analytics are EventOS's unique differentiator.

Section eyebrow: "GAMIFICACION Y JUEGOS · 10 DATASETS"

### Row 1: Full width — Podium

Inspired by Event Pulse's leaderboard section (same visual DNA):

- 3 podium blocks: #2 left, #1 center (tallest), #3 right
- Each block: avatar circle (110px for #1, 76px for #2/#3) + name + points
- #1 avatar: platinum ring animation (box-shadow pulse between 6px and 10px platinum border), points in --platinum
- #2 avatar: teal ring, points in --teal
- #3 avatar: --bg-3 ring, points in --t2
- Pillar below each: gradient fill (#1: platinum gradient, #2: teal gradient, #3: ink gradient)
- Heights: #1 = 120px, #2 = 80px, #3 = 55px

To the right of podium: vertical list #4 through #10
- Each row: rank (mono --t4) + avatar 36px + name (13px) + dotted line + points (mono, --teal)

```
         ┌──────┐
         │  #1  │     #4  Ana M.        ·····  1,650
  ┌────┐ │Sofia │     #5  Carlos R.     ·····  1,520
  │ #2 │ │2,480 │     #6  Laura P.      ·····  1,380
  │Dan │ │ pts  │ ┌──┐#7  Miguel S.     ·····  1,240
  │2150│ │      │ │#3│#8  Valentina G.  ·····  1,100
  │    │ │      │ │Ma│#9  Andres F.     ·····    980
  │    │ │      │ │18│#10 Diana C.      ·····    870
  └────┘ └──────┘ └──┘
```

### Row 2: 50/50

**Chart J — Donut: "Distribucion de Puntos por Accion" (50%)**

- 6 segments (top 5 actions + "Otros"):
  - Check-in evento: --teal (22%)
  - Visitar stand: --platinum (18%)
  - Rate session: --amber (15%)
  - Chat en sesion: --blue (14%)
  - Subir foto: --pink (#F472B6, 12%)
  - Otros (8 acciones): --t5 (19%)
- Center: "48,920" in 24px, "puntos totales" below

**Chart K — Mixed: "Juegos del Evento" (50%)**

Card with 3 mini-cards inside (one per game type):

Each mini-card:
- Game type icon + label (mono 9px uppercase)
- Game name (Urbanist 14px weight 600)
- 2 metrics: Participantes + Ganador(es)
- Status badge: "Finalizado" in green-soft

```
┌────────────────────────────────────────────────────┐
│ 🎯 TRIVIA              🎰 RULETA         🎟️ JACKPOT │
│ Quiz: AI Edition        Spin & Win        Gran Sorteo│
│ 234 participantes       189 participantes  312 elegibl│
│ Ganador: Sofia R.       48,920 pts dados   Daniel O.  │
│ ✓ Finalizado            ✓ Finalizado       ✓ Finalizado│
└────────────────────────────────────────────────────┘
```

### Row 3: 50/50

**Chart L — Grid: "Premios Canjeados" (50%)**

4 mini-cards in 2x2 grid, each representing a reward:
- Reward icon placeholder + name (13px weight 600)
- "12 / 20 canjeados" with progress bar (fill --teal, track --bg-3)
- Sponsor badge if sponsored
- Status: stock remaining

**Chart M — Stacked Bar: "Passport Stamps Completados" (50%)**

Horizontal stacked bar showing:
- Green section: "Completados" (asistentes que terminaron el passport) — 34%
- Teal section: "En progreso" (parciales) — 48%
- Ghost section: "Sin empezar" — 18%

Below: "Requeridos: 5 stamps de 8 stands"
Mini list: top 3 stands mas visitados with stamp icon + name + count

---

## Notification Panel (Frame 1 only)

Peeking from the right edge — 120px visible of a 400px panel. Shows:

- Panel header: "EXPORTS" in mono, with close X button
- Visible notifications:

1. **Completed** (green left-border 3px):
   - Green dot (6px) + "Leads Master" (13px weight 600)
   - "156 registros · CSV · 12 KB" (12px --t3)
   - Button "Descargar" (--green bg, dark text, 24px height, 10px weight 700)
   - "Hace 2s" (mono 9px --t4)

2. **Processing** (amber left-border 3px):
   - Amber dot (6px, breathing animation) + "Asistentes Master" (13px)
   - Progress bar: 3px, fill --amber gradient, track --bg-3, at 67%
   - "Procesando 1,247 registros..." (12px --t3)

---

## Interactions & Animations

- Hero stat values: countUp animation 1.2s ease-out cubic on page load
- Chart cards: staggered entrance — cardIn (opacity 0 → 1, translateY 8px → 0), 0.4s, 0.05s delay between cards
- Tab switch: content fadeIn 0.3s
- Donut segments: draw animation (stroke-dashoffset) 0.8s ease-out on mount
- Horizontal bars: width grows from 0 to target over 0.8s ease-out, staggered
- Heatmap cells: opacity fade in, staggered left-to-right 0.02s per cell
- Hover on chart cards: border-color → --b-hi, transition 0.2s
- Sparkline in hero card: draws left to right on load, 1s
- Brand dot in header: breathing glow 2.4s infinite (same as Event Pulse)

---

## What NOT to do

1. Do NOT use any color not in the Lumina palette — no cyan, no neon, no electric blue, no lime
2. Do NOT make export buttons prominent — they are text links in card corners, not CTAs (exception: "Exportar Leads Master" in sponsors tab which IS prominent)
3. Do NOT use pie charts — only donuts with center value
4. Do NOT add gradients to card backgrounds — flat surfaces with subtle border
5. Do NOT use shadows in Noir mode — Noir uses borders for depth, NOT shadows (shadows are for Lux mode only)
6. Do NOT over-round corners — max 12px in Noir
7. Do NOT make it look like a Filament admin panel or Bootstrap dashboard
8. Do NOT use colored backgrounds on cards — ALL cards are --bg-1 with --b border
9. Do NOT forget tabular-nums on every number — alignment matters
10. Do NOT make a "dashboard template" — make something that feels like EventOS, with the same restraint and typography discipline as Mission Control and Event Pulse

---

## Demo Data

```
Event:          Summit Empresarial 2026
Dates:          25 - 27 Abril, 2026
Venue:          Centro de Convenciones, Bogota
Organizer:      Eventos Efectivos SAS

Global metrics:
  Registered:     1,247
  Checked-in:     892   (71.5%)
  Sessions:       24
  Rating avg:     4.6 / 5 (892 reviews)
  Leads:          156
  Connections:    89
  Chat messages:  4,312
  Photos:         267
  Total points:   48,920
  Rewards:        34 redeemed
  Push open:      82%
  Virtual:        127
  In-person:      634
  Games:          12
  Wall posts:     189

Sponsors:
  TechCorp International  — Platinum — 47 leads — 312 stand visits
  Meridian Labs           — Gold     — 38 leads — 267 stand visits
  CloudBase Solutions     — Gold     — 31 leads — 198 stand visits
  DataFlow Analytics      — Silver   — 22 leads — 145 stand visits
  InnovateCO              — Silver   — 18 leads —  98 stand visits

Leaderboard:
  #1  Sofia Ramirez     — 2,480 pts
  #2  Daniel Okonkwo    — 2,150 pts
  #3  Maria Torres      — 1,890 pts
  #4  Ana Martinez      — 1,650 pts
  #5  Carlos Rodriguez  — 1,520 pts

Sessions (sorted by attendance):
  Keynote: AI for Enterprise     — 89% — 4.8★ — 1,112 pax
  Workshop: Data Strategy        — 78% — 4.6★ —   975 pax
  Panel: Leadership 2030         — 67% — 4.5★ —   838 pax
  Networking Lunch               — 54% — N/A  —   675 pax
  Workshop: Cloud Native         — 48% — 4.3★ —   600 pax
  Closing Ceremony               — 38% — 4.1★ —   475 pax
```
