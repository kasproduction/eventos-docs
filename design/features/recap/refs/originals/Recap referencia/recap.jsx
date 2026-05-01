/* global React, ReactDOM, IOSDevice, useTweaks, TweaksPanel, TweakSection, TweakRadio, TweakToggle */

const { useState, useEffect, useRef, useMemo } = React;

// ---- Palettes ------------------------------------------------------------
const PALETTES = {
  aurora: {
    name: "Aurora",
    bg: "#050507",
    surface: "#0C0D12",
    elevated: "#161824",
    fg: "#F0F2F8",
    accent: "#7AB8FF",
    accentSoft: "#5B7FFF",
    accentWarm: "#9DD4FF",
    halo1: "rgba(91, 127, 255, 0.45)",
    halo2: "rgba(122, 184, 255, 0.35)",
    halo3: "rgba(180, 220, 255, 0.20)",
    muted: "rgba(240,242,248,0.55)",
    line: "rgba(240,242,248,0.10)",
    hasHalo: true,
  },
  ember: {
    name: "Ember",
    bg: "#070405",
    surface: "#120D0F",
    elevated: "#1F1518",
    fg: "#F5EDEA",
    accent: "#FF9E7A",
    accentSoft: "#FF6B5B",
    accentWarm: "#FFD0A8",
    halo1: "rgba(255, 107, 91, 0.38)",
    halo2: "rgba(255, 158, 122, 0.30)",
    halo3: "rgba(255, 208, 168, 0.18)",
    muted: "rgba(245,237,234,0.55)",
    line: "rgba(245,237,234,0.10)",
    hasHalo: true,
  },
  mono: {
    name: "Mono",
    bg: "#08090C",
    surface: "#12141A",
    elevated: "#1E2129",
    fg: "#E6EAF0",
    accent: "#E6EAF0",
    accentSoft: "#9AA5B1",
    accentWarm: "#FFFFFF",
    halo1: "rgba(230, 234, 240, 0.18)",
    halo2: "rgba(230, 234, 240, 0.12)",
    halo3: "rgba(230, 234, 240, 0.06)",
    muted: "rgba(230,234,240,0.50)",
    line: "rgba(230,234,240,0.10)",
    hasHalo: true,
  },
};

// ---- Hooks ---------------------------------------------------------------
function useInView(opts = {}) {
  const ref = useRef(null);
  const [inView, setInView] = useState(false);
  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const io = new IntersectionObserver(
      ([e]) => {
        if (e.isIntersecting) setInView(true);
      },
      { threshold: 0.35, ...opts }
    );
    io.observe(el);
    return () => io.disconnect();
  }, []);
  return [ref, inView];
}

function useCounter(target, run, duration = 1400) {
  const [v, setV] = useState(0);
  useEffect(() => {
    if (!run) return;
    const start = performance.now();
    let raf;
    const tick = (t) => {
      const p = Math.min(1, (t - start) / duration);
      const eased = 1 - Math.pow(1 - p, 3);
      setV(target * eased);
      if (p < 1) raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, [run, target, duration]);
  return v;
}

// ---- Shared atoms --------------------------------------------------------
const Eyebrow = ({ children, n }) => (
  <div className="eyebrow">
    <span className="eyebrow-num">{String(n).padStart(2, "0")}</span>
    <span className="eyebrow-line" />
    <span className="eyebrow-label">{children}</span>
  </div>
);

const SectionShell = ({ n, label, children, screenLabel }) => {
  const [ref, inView] = useInView();
  return (
    <section
      ref={ref}
      className={"sec " + (inView ? "in" : "")}
      data-screen-label={screenLabel}
    >
      <Eyebrow n={n}>{label}</Eyebrow>
      {children}
    </section>
  );
};

// ---- 1. Hero -------------------------------------------------------------
function Hero() {
  const [ref, inView] = useInView({ threshold: 0.2 });
  return (
    <section
      ref={ref}
      className={"sec hero " + (inView ? "in" : "")}
      data-screen-label="01 Hero"
    >
      <div className="halo halo-hero" aria-hidden="true" />
      <div className="hero-meta">
        <span>FUTURESTACK</span>
        <span className="dot" />
        <span>2026</span>
      </div>
      <h1 className="hero-title">
        <span>Tu</span>
        <span className="accent-italic">Recap</span>
        <span>edición</span>
        <span>06</span>
      </h1>
      <div className="hero-foot">
        <div>
          <div className="hero-foot-k">Asistente</div>
          <div className="hero-foot-v">María Salgado</div>
        </div>
        <div>
          <div className="hero-foot-k">Días</div>
          <div className="hero-foot-v">14 — 16 Abr</div>
        </div>
      </div>
      <div className="scroll-hint">
        <span>Desliza</span>
        <span className="arrow">↓</span>
      </div>
    </section>
  );
}

// ---- 2. Tiempo -----------------------------------------------------------
function Tiempo() {
  const [ref, inView] = useInView();
  const hours = useCounter(14, inView, 1600);
  const mins = useCounter(32, inView, 1800);
  return (
    <section ref={ref} className={"sec tiempo " + (inView ? "in" : "")} data-screen-label="02 Tiempo">
      <div className="halo halo-tiempo" aria-hidden="true" />
      <Eyebrow n={2}>Tiempo a bordo</Eyebrow>
      <div className="tiempo-row">
        <div className="tiempo-num">
          <span className="big">{Math.floor(hours)}</span>
          <span className="unit">h</span>
        </div>
        <div className="tiempo-num">
          <span className="big">{Math.floor(mins)}</span>
          <span className="unit">m</span>
        </div>
      </div>
      <div className="tiempo-side">
        <span>HORAS</span>
        <span className="line-v" />
        <span>EN VIVO</span>
      </div>
      <p className="tiempo-caption">
        Más que el 87% de los asistentes. Estuviste presente desde la apertura
        del primer keynote hasta el cierre del afterparty del jueves.
      </p>
    </section>
  );
}

// ---- 3. Top sesión -------------------------------------------------------
function TopSesion() {
  return (
    <SectionShell n={3} label="Tu sesión favorita" screenLabel="03 Top sesion">
      <div className="card-portrait">
        <div className="portrait">
          <div className="portrait-grain" />
          <div className="portrait-tag">KEYNOTE · 14:30</div>
          <div className="portrait-placeholder">
            <span>RETRATO</span>
            <span className="ph-sub">3:4 · ponente</span>
          </div>
        </div>
        <div className="card-meta">
          <div className="card-title">
            La arquitectura silenciosa de los sistemas distribuidos
          </div>
          <div className="card-row">
            <span>Idris Vélez</span>
            <span className="card-dot" />
            <span>Stripe</span>
          </div>
          <div className="card-row sub">
            <span>52 min</span>
            <span className="card-dot" />
            <span>Sala Magna</span>
          </div>
        </div>
      </div>
    </SectionShell>
  );
}

// ---- 4. Conexiones -------------------------------------------------------
const AVATARS = Array.from({ length: 24 }, (_, i) => ({
  id: i,
  hue: (i * 47) % 360,
  initials: ["MA", "JR", "LC", "TS", "EV", "NK", "BW", "PM", "QF", "DH", "OR", "YT"][i % 12],
}));

function Conexiones() {
  const [ref, inView] = useInView();
  return (
    <section ref={ref} className={"sec conex " + (inView ? "in" : "")} data-screen-label="04 Conexiones">
      <div className="halo halo-conex" aria-hidden="true" />
      <Eyebrow n={4}>Nuevas conexiones</Eyebrow>
      <div className="conex-stack">
        <div className="conex-num">42</div>
        <div className="conex-grid">
          {AVATARS.map((a, i) => (
            <div
              key={a.id}
              className="avatar"
              style={{
                transitionDelay: `${i * 28}ms`,
                background: `linear-gradient(135deg, hsl(${a.hue} 18% 28%), hsl(${a.hue} 14% 16%))`,
              }}
            >
              <span>{a.initials}</span>
            </div>
          ))}
        </div>
      </div>
      <p className="conex-caption">
        Intercambiaste contacto con <em>42</em> profesionales. 18 desde
        producto, 14 ingeniería, 10 diseño.
      </p>
    </section>
  );
}

// ---- 5. Huella -----------------------------------------------------------
function Huella() {
  const [ref, inView] = useInView();
  return (
    <section ref={ref} className={"sec huella " + (inView ? "in" : "")} data-screen-label="05 Huella">
      <Eyebrow n={5}>Tu huella</Eyebrow>
      <div className="huella-map">
        <svg viewBox="0 0 320 240" className="map-svg" preserveAspectRatio="none">
          <defs>
            <pattern id="dots" width="10" height="10" patternUnits="userSpaceOnUse">
              <circle cx="1" cy="1" r="0.6" fill="currentColor" opacity="0.25" />
            </pattern>
          </defs>
          <rect width="320" height="240" fill="url(#dots)" />
          <path
            className="map-path"
            d="M20 200 L70 170 L120 180 L150 130 L210 110 L260 60"
            fill="none"
            stroke="currentColor"
            strokeWidth="1"
            strokeDasharray="3 4"
          />
          <circle className="map-stop" cx="20" cy="200" r="3" />
          <circle className="map-stop" cx="120" cy="180" r="3" />
          <circle className="map-stop" cx="210" cy="110" r="3" />
          <circle className="map-peak" cx="260" cy="60" r="6" />
          <circle className="map-peak-pulse" cx="260" cy="60" r="14" />
        </svg>
        <div className="map-label">
          <div className="map-label-k">Stand más visitado</div>
          <div className="map-label-v">Pavilion B · Vercel</div>
          <div className="map-label-sub">4 visitas · 38 min totales</div>
        </div>
      </div>
    </section>
  );
}

// ---- 6. Mejor momento ----------------------------------------------------
const TIMELINE = [
  { h: "09", v: 0.2 },
  { h: "10", v: 0.35 },
  { h: "11", v: 0.5 },
  { h: "12", v: 0.4 },
  { h: "13", v: 0.3 },
  { h: "14", v: 0.45 },
  { h: "15", v: 0.7 },
  { h: "16", v: 0.95, peak: true },
  { h: "17", v: 0.6 },
  { h: "18", v: 0.4 },
  { h: "19", v: 0.5 },
  { h: "20", v: 0.3 },
];

function MejorMomento() {
  const [ref, inView] = useInView();
  return (
    <section ref={ref} className={"sec momento " + (inView ? "in" : "")} data-screen-label="06 Mejor momento">
      <div className="halo halo-momento" aria-hidden="true" />
      <Eyebrow n={6}>Tu hora pico</Eyebrow>
      <div className="momento-num">
        16<span className="colon">:</span>00
      </div>
      <div className="momento-line">
        <div className="line-track" />
        <div className="line-fill" />
        <div className="line-bars">
          {TIMELINE.map((t, i) => (
            <div
              key={i}
              className={"bar " + (t.peak ? "peak" : "")}
              style={{
                height: `${t.v * 100}%`,
                transitionDelay: `${i * 50}ms`,
              }}
            />
          ))}
        </div>
        <div className="line-labels">
          {TIMELINE.map((t, i) => (
            <span key={i} className={t.peak ? "lbl peak" : "lbl"}>
              {t.h}
            </span>
          ))}
        </div>
      </div>
      <p className="momento-caption">
        Coincidió con la mesa de Diseño Sistémico — 4 conexiones nuevas y
        11 minutos al micrófono.
      </p>
    </section>
  );
}

// ---- 7. Card final compartible ------------------------------------------
function CardFinal() {
  const [ref, inView] = useInView();
  return (
    <section ref={ref} className={"sec final " + (inView ? "in" : "")} data-screen-label="07 Card final">
      <div className="halo halo-final" aria-hidden="true" />
      <Eyebrow n={7}>Para compartir</Eyebrow>
      <div className="share-frame">
        <div className="share-card">
          <div className="share-grain" />
          {/* 1 logo */}
          <div className="share-logo">
            <span className="logo-mark" />
            <span>FUTURESTACK</span>
          </div>
          {/* 2 año + edición */}
          <div className="share-edition">2026 · Edición 06</div>
          {/* 3 nombre */}
          <div className="share-name">
            María
            <br />
            Salgado
          </div>
          {/* 4 stat hero */}
          <div className="share-stat-hero">
            <div className="hero-k">Tiempo en vivo</div>
            <div className="hero-v">14h 32m</div>
          </div>
          {/* 5 stat secundaria */}
          <div className="share-stat-grid">
            <div>
              <div className="hero-k">Conexiones</div>
              <div className="hero-v small">42</div>
            </div>
            <div>
              <div className="hero-k">Sesiones</div>
              <div className="hero-v small">19</div>
            </div>
          </div>
          {/* 6 top sesión */}
          <div className="share-top">
            <div className="hero-k">Sesión favorita</div>
            <div className="share-top-title">
              La arquitectura silenciosa de los sistemas distribuidos
            </div>
            <div className="share-top-sub">Idris Vélez · Stripe</div>
          </div>
          {/* 7 divider + tagline */}
          <div className="share-divider" />
          <div className="share-tagline">No vienes a un evento. Lo habitas.</div>
          {/* 8 handle + QR */}
          <div className="share-foot">
            <div className="share-handle">@maria.salgado</div>
            <div className="share-qr" aria-hidden="true">
              {Array.from({ length: 49 }).map((_, i) => (
                <span key={i} style={{ opacity: ((i * 7) % 3) === 0 ? 1 : 0.15 }} />
              ))}
            </div>
          </div>
        </div>
      </div>
      <div className="share-actions">
        <button className="btn primary">Compartir</button>
        <button className="btn ghost">Guardar imagen</button>
      </div>
      <div className="end-note">— Fin del recap —</div>
    </section>
  );
}

// ---- App -----------------------------------------------------------------
const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "palette": "aurora",
  "grain": true,
  "halos": true
}/*EDITMODE-END*/;

function App() {
  const [tweaks, setTweak] = useTweaks(TWEAK_DEFAULTS);
  const palette = PALETTES[tweaks.palette] || PALETTES.aurora;

  const cssVars = {
    "--bg": palette.bg,
    "--surface": palette.surface,
    "--elev": palette.elevated,
    "--fg": palette.fg,
    "--accent": palette.accent,
    "--accent-soft": palette.accentSoft,
    "--accent-warm": palette.accentWarm,
    "--halo-1": palette.halo1,
    "--halo-2": palette.halo2,
    "--halo-3": palette.halo3,
    "--muted": palette.muted,
    "--line": palette.line,
  };

  return (
    <div className="app" style={cssVars}>
      <IOSDevice width={402} height={874} dark={true}>
        <div className={"recap " + (tweaks.grain ? "with-grain " : "") + (tweaks.halos ? "with-halos" : "")}>
          <header className="topbar">
            <span className="topbar-back">‹</span>
            <span className="topbar-title">Recap</span>
            <span className="topbar-share">Compartir</span>
          </header>
          <Hero />
          <Tiempo />
          <TopSesion />
          <Conexiones />
          <Huella />
          <MejorMomento />
          <CardFinal />
        </div>
      </IOSDevice>

      <TweaksPanel title="Tweaks">
        <TweakSection title="Paleta">
          <TweakRadio
            label="Aurora"
            value={tweaks.palette}
            onChange={(v) => setTweak("palette", v)}
            options={[
              { value: "aurora", label: "Aurora" },
              { value: "ember", label: "Ember" },
              { value: "mono", label: "Mono" },
            ]}
          />
        </TweakSection>
        <TweakSection title="Atmósfera">
          <TweakToggle
            label="Halos"
            value={tweaks.halos}
            onChange={(v) => setTweak("halos", v)}
          />
          <TweakToggle
            label="Grano"
            value={tweaks.grain}
            onChange={(v) => setTweak("grain", v)}
          />
        </TweakSection>
      </TweaksPanel>
      <PaletteSwitcher
        value={tweaks.palette}
        onChange={(v) => setTweak("palette", v)}
      />
      <TweaksLauncher />
    </div>
  );
}

function PaletteSwitcher({ value, onChange }) {
  const opts = [
    { v: "aurora", c1: "#9DD4FF", c2: "#5B7FFF" },
    { v: "ember", c1: "#FFD0A8", c2: "#FF6B5B" },
    { v: "mono", c1: "#E6EAF0", c2: "#9AA5B1" },
  ];
  return (
    <div className="palette-switcher" role="radiogroup" aria-label="Tema">
      {opts.map((o) => (
        <button
          key={o.v}
          className={"ps-dot " + (value === o.v ? "active" : "")}
          onClick={() => onChange(o.v)}
          style={{ background: `linear-gradient(135deg, ${o.c1}, ${o.c2})` }}
          aria-label={o.v}
          aria-checked={value === o.v}
          role="radio"
        />
      ))}
    </div>
  );
}

function TweaksLauncher() {
  const open = () => window.postMessage({ type: "__activate_edit_mode" }, "*");
  return (
    <button className="tweaks-launcher" onClick={open} aria-label="Abrir Tweaks">
      <span className="tl-dot" />
      <span>Tweaks</span>
    </button>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
