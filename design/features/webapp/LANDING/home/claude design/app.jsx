/* global React, ReactDOM */
const { useState, useEffect, useRef, useMemo } = React;

// ============================================
// EVENT DATA
// ============================================
const EVENT = {
  name: "LUMINA",
  nameAccent: "SUMMIT",
  year: "2026",
  date: "06 — 08 NOV 2026",
  city: "MEXICO CITY",
  venue: "CENTRO CITIBANAMEX",
  registered: "2,847",
  organizer: "EventOS",
  // target date for countdown (PRE-EVENT)
  startISO: "2026-11-06T09:00:00-06:00",
};

const TRACKS = [
  { idx: "01", name: "Founders & Capital", count: "14 SESSIONS" },
  { idx: "02", name: "AI in Production", count: "22 SESSIONS" },
  { idx: "03", name: "Design Engineering", count: "11 SESSIONS" },
  { idx: "04", name: "Operating Systems", count: "08 SESSIONS" },
  { idx: "05", name: "Open Stage", count: "06 SESSIONS" },
];

const SPEAKERS = [
  { initials: "MR", gold: false },
  { initials: "AK", gold: false },
  { initials: "JD", gold: true },
  { initials: "SO", gold: false },
  { initials: "TC", gold: false },
];

const SPONSORS = ["LINEAR", "VERCEL", "STRIPE", "FIGMA", "ARC", "RAYCAST"];

const PRE_ANNOUNCES = [
  { when: "HACE 2 HRS", body: "Agenda final publicada. Revisa tu itinerario sugerido." },
  { when: "AYER", body: "El check-in abre 07:30, una hora antes del keynote." },
];

const LIVE_HAPPENING = {
  room: "SALA MAGNA · DAY 02",
  title: "The Compounding Edge of Small Teams",
  speaker: "Joaquín Decker",
  speakerRole: "Founder, Northbeam",
  initials: "JD",
  remaining: "23:14",
};

const LIVE_ANNOUNCES = [
  { when: "AHORA", body: "Sala Magna lleva 12 min de delay. Stream sin interrupciones.", unread: true },
  { when: "HACE 4 MIN", body: "Workshop de Linear movido a Sala B—aforo lleno.", unread: true },
  { when: "HACE 12 MIN", body: "Lunch en Patio Norte abre 13:00. Vegetariano en mesa 4.", unread: true },
  { when: "HACE 38 MIN", body: "Agenda de tarde actualizada—nueva sesión sobre infra-as-product.", unread: false },
  { when: "HACE 1 HR", body: "Networking dinner confirma 06:30 PM en rooftop. RSVP cerrado.", unread: false },
  { when: "HACE 2 HRS", body: "WiFi backup activo en SSID 'lumina-fallback'. Pass: lumina26.", unread: false },
];

const HUD = { points: "1,840", position: "Rank 84" };

const SESSIONS_TODAY = [
  { when: "AHORA · 11:30", room: "SALA MAGNA", title: "The Compounding Edge of Small Teams", who: "Joaquín Decker", state: "live" },
  { when: "12:30", room: "SALA B", title: "Postgres at Founder-Scale", who: "Mira Rashidi", state: "next" },
  { when: "13:00", room: "PATIO", title: "Lunch · Networking abierto", who: "—", state: "" },
  { when: "14:30", room: "SALA MAGNA", title: "Design Engineering as a Discipline", who: "Saori Okada", state: "" },
  { when: "15:30", room: "SALA C", title: "Why Most Series A Rounds Underprice", who: "Theo Castellanos", state: "" },
  { when: "16:30", room: "SALA B", title: "Operating with Public Roadmaps", who: "Anna Kowalski", state: "" },
  { when: "17:30", room: "ROOFTOP", title: "Closing Reception · Day 02", who: "—", state: "" },
];

const ENDED_STATS = [
  { lbl: "TIEMPO EN VIVO", num: "14", unit: "h", sub: "08 sesiones completadas" },
  { lbl: "CONEXIONES", num: "32", unit: "", sub: "12 con seguimiento confirmado" },
  { lbl: "RECAP", num: "01", unit: "/01", sub: "Tu video personal listo" },
];

const ARCHIVE_ITEMS = [
  { when: "MAR 2027", name: "Lumina Winter Lab · Cdmx", tag: "REGISTRO ABIERTO", upcoming: true },
  { when: "JUL 2027", name: "Lumina Founders Salon · Mty", tag: "EARLY ACCESS", upcoming: true },
  { when: "NOV 2025", name: "Lumina Summit 2025 · Cdmx", tag: "ARCHIVO" },
  { when: "JUN 2025", name: "Lumina Builders Day · Gdl", tag: "ARCHIVO" },
];

const MEMORIES = ["KEYNOTE", "BACKSTAGE", "LUNCH", "CLOSING", "PANEL", "STAGE", "CROWD", "AFTER"];

// ============================================
// HOOKS
// ============================================
function useCountdown(targetISO, isActive) {
  const [parts, setParts] = useState({ d: "00", h: "00", m: "00", s: "00" });
  useEffect(() => {
    if (!isActive) return;
    const compute = () => {
      const diff = new Date(targetISO).getTime() - Date.now();
      if (diff <= 0) {
        setParts({ d: "00", h: "00", m: "00", s: "00" });
        return;
      }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      setParts({
        d: String(d).padStart(2, "0"),
        h: String(h).padStart(2, "0"),
        m: String(m).padStart(2, "0"),
        s: String(s).padStart(2, "0"),
      });
    };
    compute();
    const id = setInterval(compute, 1000);
    return () => clearInterval(id);
  }, [targetISO, isActive]);
  return parts;
}

function useLiveTimer(initialSeconds, isActive) {
  const [s, setS] = useState(initialSeconds);
  useEffect(() => {
    if (!isActive) return;
    const id = setInterval(() => setS((v) => (v > 0 ? v - 1 : 0)), 1000);
    return () => clearInterval(id);
  }, [isActive]);
  const mm = String(Math.floor(s / 60)).padStart(2, "0");
  const ss = String(s % 60).padStart(2, "0");
  return `${mm}:${ss}`;
}

// ============================================
// PRE-EVENT
// ============================================
function PreEvent({ active }) {
  const cd = useCountdown(EVENT.startISO, active);
  return (
    <div className="pre-grid">
      <div className="corner-mark tl">EVENTOS · MIS REGISTROS</div>
      <div className="corner-mark tr">W.0 · HOME / PRE-EVENT</div>
      <div className="corner-mark br">MX-CDMX · 06 NOV</div>

      <div className="pre-hero">
        <div className="top">
          <div className="eyebrow">
            <span className="strong">{EVENT.date}</span>
            <span className="sep"></span>
            <span>{EVENT.city} · {EVENT.venue}</span>
            <span className="sep"></span>
            <span><span className="gold">{EVENT.registered}</span> registrados</span>
          </div>
          <h1 className="hero-type">
            {EVENT.name}<br />
            <span className="gold">{EVENT.nameAccent}</span> {EVENT.year.slice(2)}
          </h1>
          <p className="sub">
            Tres días con los founders, builders y operadores que están
            redefiniendo cómo se construye software hoy. Tu lugar está
            confirmado.
          </p>
        </div>
      </div>

      <div className="pre-rail">
        <div className="countdown-block">
          <div className="eyebrow">FALTAN</div>
          <div className="countdown-grid">
            <div className="countdown-cell"><div className="num">{cd.d}</div><div className="lbl">DÍAS</div></div>
            <div className="countdown-cell"><div className="num">{cd.h}</div><div className="lbl">HRS</div></div>
            <div className="countdown-cell"><div className="num">{cd.m}</div><div className="lbl">MIN</div></div>
            <div className="countdown-cell"><div className="num">{cd.s}</div><div className="lbl">SEG</div></div>
          </div>
        </div>

        <div className="qr-block">
          <div className="qr-art"></div>
          <div className="qr-meta">
            <div className="t">Mi entrada · QR</div>
            <div className="s">FOLIO LUM-2847-K</div>
          </div>
        </div>

        <div className="cta-prep">
          <div className="item">
            <span className="label">Itinerario sugerido</span>
            <span className="arrow">→</span>
          </div>
          <div className="item">
            <span className="label">Cómo llegar al venue</span>
            <span className="arrow">→</span>
          </div>
          <div className="item">
            <span className="label">Hospedaje recomendado</span>
            <span className="arrow">→</span>
          </div>
          <div className="item">
            <span className="label">Mi perfil de networking</span>
            <span className="arrow">→</span>
          </div>
        </div>
      </div>

      <div className="pre-strip">
        <div className="strip-block">
          <div className="head">
            <span className="t">Tracks</span>
            <span className="more">05</span>
          </div>
          <div className="tracks">
            {TRACKS.map((t) => (
              <div key={t.idx} className="track">
                <span className="idx">{t.idx}</span>
                <span className="name">{t.name}</span>
                <span className="count">{t.count}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="strip-block">
          <div className="head">
            <span className="t">Speakers</span>
            <span className="more">+126 MÁS</span>
          </div>
          <div className="speakers-row">
            <div className="row">
              {SPEAKERS.map((s, i) => (
                <div key={i} className={"speaker-chip" + (s.gold ? " gold" : "")}>{s.initials}</div>
              ))}
            </div>
            <div className="label">
              <span className="strong">Joaquín Decker</span>, Mira Rashidi y 124 más
            </div>
          </div>
        </div>

        <div className="strip-block">
          <div className="head">
            <span className="t">Sponsors</span>
            <span className="more">12</span>
          </div>
          <div className="sponsors-row">
            {SPONSORS.map((s) => (
              <span key={s} className="sponsor-mark">{s}</span>
            ))}
          </div>
        </div>

        <div className="strip-block">
          <div className="head">
            <span className="t">Anuncios</span>
            <span className="more">02</span>
          </div>
          <div className="announce-mini">
            {PRE_ANNOUNCES.map((a, i) => (
              <div key={i} className="a">
                <div className="when">{a.when}</div>
                <div className="body">{a.body}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ============================================
// LIVE
// ============================================
function Live({ active }) {
  const timer = useLiveTimer(23 * 60 + 14, active);
  const railRef = useRef(null);
  const scrollDay = (dir) => {
    const el = railRef.current;
    if (!el) return;
    el.scrollBy({ left: dir * 280, behavior: "smooth" });
  };

  return (
    <div className="live-grid">
      <div className="corner-mark tl">EVENTOS · LUMINA SUMMIT 26</div>
      <div className="corner-mark tr">W.0 · HOME / LIVE</div>

      <div className="live-hero">
        <div className="live-eyebrow">
          <span className="pulse">EN VIVO</span>
          <span>DAY 02 / 03</span>
          <span style={{ color: "var(--fg-faint)" }}>·</span>
          <span>{EVENT.city}</span>
        </div>
        <h1 className="hero-type">
          {EVENT.name} <span className="gold">{EVENT.nameAccent}</span>
        </h1>
      </div>

      <div className="happening-card">
        <div className="speaker-portrait">
          <div className="frame-mark">SPEAKER · 02 / 03</div>
          <div className="initials">{LIVE_HAPPENING.initials}</div>
          <div className="frame-mark br">LIVE</div>
        </div>
        <div className="happening-meta">
          <div className="row-eyebrow">
            <span className="room">{LIVE_HAPPENING.room}</span>
            <span className="timer">— {timer} restantes</span>
          </div>
          <h2>{LIVE_HAPPENING.title}</h2>
          <div className="speaker-name">
            <span className="strong">{LIVE_HAPPENING.speaker}</span> &nbsp;·&nbsp; {LIVE_HAPPENING.speakerRole}
          </div>
          <div className="progress"><div className="fill"></div></div>
          <div className="actions">
            <button className="btn-primary">Unirme al stream →</button>
            <button className="btn-secondary">Agenda completa</button>
          </div>
        </div>
      </div>

      <div className="live-rail">
        <div className="rail-section scroll">
          <div className="rail-head">
            <span className="t">Anuncios</span>
            <span className="badge">3 NUEVOS</span>
          </div>
          <div className="announces-list">
            {LIVE_ANNOUNCES.map((a, i) => (
              <div key={i} className={"announce" + (a.unread ? " unread" : "")}>
                <div className="when">{a.when}</div>
                <div className="body">{a.body}</div>
              </div>
            ))}
          </div>
        </div>

        <div className="rail-section">
          <div className="rail-head">
            <span className="t">Tu progreso</span>
            <span style={{ fontFamily: "var(--mono)", fontSize: 10, color: "var(--fg-dim)", letterSpacing: "0.16em" }}>SOLO TÚ</span>
          </div>
          <div className="hud-block">
            <div className="hud-cell">
              <div className="num">{HUD.points}</div>
              <div className="lbl">PUNTOS</div>
            </div>
            <div className="hud-cell">
              <div className="num">{HUD.position}</div>
              <div className="lbl">POSICIÓN</div>
            </div>
          </div>
        </div>
      </div>

      <div className="day-picker">
        <div className="head">
          <span className="t">Agenda · HOY · DAY 02</span>
          <div className="nav">
            <button onClick={() => scrollDay(-1)} aria-label="Anterior">←</button>
            <button onClick={() => scrollDay(1)} aria-label="Siguiente">→</button>
          </div>
        </div>
        <div className="day-rail" ref={railRef}>
          {SESSIONS_TODAY.map((s, i) => (
            <div key={i} className={"session-card" + (s.state ? " " + s.state : "")}>
              <div className="when">
                <span>{s.when}</span>
                <span className="room">{s.room}</span>
              </div>
              <div className="title">{s.title}</div>
              <div className="who">{s.who}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ============================================
// ENDED
// ============================================
function Ended() {
  return (
    <div className="ended-grid">
      <div className="corner-mark tl">EVENTOS · LUMINA SUMMIT 26</div>
      <div className="corner-mark tr">W.0 · HOME / ENDED</div>

      <div className="ended-hero">
        <div className="eyebrow">
          <span className="gold">GRACIAS POR ASISTIR</span>
          <span className="sep"></span>
          <span>{EVENT.date.replace("06 — 08", "06–08")}</span>
          <span className="sep"></span>
          <span>{EVENT.city}</span>
        </div>
        <h1 className="hero-type">
          {EVENT.name} <span className="gold">{EVENT.nameAccent}</span>
        </h1>
      </div>

      <div className="recap-band">
        <div className="meta">
          <span className="eb">TU RECAP · LISTO</span>
          <h2>3 días, 14 horas en vivo, 32 personas nuevas.</h2>
          <p className="desc">
            Tu video personal de 90 segundos compila las sesiones que
            asististe, las personas que conociste y los momentos que el
            equipo capturó cerca de ti. Descargable, compartible, tuyo.
          </p>
        </div>
        <div style={{ position: "relative", display: "flex", flexDirection: "column", gap: 10 }}>
          <button className="btn-primary">Ver mi recap →</button>
          <button className="btn-secondary">Descargar certificado</button>
        </div>
      </div>

      <div className="stats-grid">
        {ENDED_STATS.map((s, i) => (
          <div key={i} className="stat">
            <div className="lbl">{s.lbl}</div>
            <div className="num">{s.num}<span className="unit">{s.unit}</span></div>
            <div className="sub">{s.sub}</div>
          </div>
        ))}
        <div className="stat tier">
          <div className="lbl">TU TIER</div>
          <div className="num">Gold</div>
          <div className="num-sub">TOP 18% · ASISTENCIA + ENGAGEMENT</div>
        </div>
      </div>

      <div className="ended-strip">
        <div className="archive-list">
          <div className="head">
            <span>Próximos eventos del organizador</span>
            <span className="more">VER ARCHIVO →</span>
          </div>
          {ARCHIVE_ITEMS.map((a, i) => (
            <div key={i} className={"item" + (a.upcoming ? " upcoming" : "")}>
              <span className="when">{a.when}</span>
              <span className="name">{a.name}</span>
              <span className="tag">{a.tag}</span>
            </div>
          ))}
        </div>

        <div className="memories-row">
          <div className="head">
            <span>Memorias del evento</span>
            <span className="more">GALERÍA COMPLETA →</span>
          </div>
          <div className="memories-grid">
            {MEMORIES.map((m, i) => (
              <div key={i} className="memory">
                <span className="lbl">{m}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

// ============================================
// DESIGN NOTES
// ============================================
const NOTES = {
  pre: {
    title: "PRE-EVENT · razonamiento",
    protag: "Tipografía masiva del nombre (168px) como protagonista único. La marca del momento que vivirás, no un titular.",
    decisions: [
      "El countdown no es protagonista — vive en rail derecho a 28px, sobrio. Lo que pesa es el nombre del evento.",
      "Eyebrow lleva fecha + ciudad + total registrados como dato auditable, no como pill estilo enterprise.",
      "Strip inferior compacto en 4 zonas: tracks numerados, speakers como caras, sponsors como wordmarks tipográficos, anuncios cortos.",
      "Removidas: 'salud del evento', 'capacidad live', barras de progreso de registro, badges de status. No aportan a 'estoy registrado, cómo me preparo'.",
    ],
    polemic: "NO incluyo un feed de redes sociales / hashtag wall. Cisco/Hopin lo hacen como default — engagement-theater pre-evento. Antes del evento no hay nada que comentar todavía; ese feed termina siendo bots y el organizador anunciando lo mismo dos veces. Mejor 0 que ruido.",
  },
  live: {
    title: "LIVE · razonamiento",
    protag: "Card 'Happening Now' como pieza central premium con foto-portrait + sesión actual + CTA. Una decisión accionable, no diez widgets.",
    decisions: [
      "Hero compacto 64px — el nombre del evento ya no es protagonista, lo que importa es 'qué pasa ahora'.",
      "Countdown del PRE se transforma en timer de la sesión actual ('— 23:14 restantes') en gold mono. Mismo lenguaje, otra función.",
      "Anuncios scrolleables en rail derecho con 3 unread marcados con dot gold. Density real, no muestreo.",
      "HUD personal: solo puntos + 'Rank 84'. No hay 'Top 12%' ni leaderboard visible.",
      "Day picker horizontal abajo: solo HOY, marca AHORA y NEXT. No es la agenda completa — esa es otra vista (W.3).",
    ],
    polemic: "NO incluyo un widget de 'asistentes online ahora · 1,247'. Es la métrica más copiada del segmento (Cisco, Hopin, ICE360) y la menos accionable: si estás conectado tú, ya sabes que hay gente. Reemplazada por el dot rojo pulsando del eyebrow — la liveness se siente, no se cuenta.",
  },
  ended: {
    title: "ENDED · razonamiento",
    protag: "Recap band como pieza certificable con CTA gold prominente. El trofeo es el video personal, no una medalla.",
    decisions: [
      "Eyebrow gold 'GRACIAS POR ASISTIR' antes del nombre del evento — la deferencia primero.",
      "Stats grid de 4 columnas con números a 96px tipográficos. Tu tiempo, tus conexiones, tu recap, tu tier. Solo TÚ — sin comparativa con otros.",
      "Tier 'Gold' en italic display, no como medalla circular. La sutileza es la insignia.",
      "Strip inferior de 2 zonas: próximos eventos del mismo organizador (retención silenciosa) + memorias en grid (recuerdo, no validación social).",
      "Removidas: 'rate this event', 'NPS', encuestas de feedback. Eso va por email, no en el home.",
    ],
    polemic: "NO incluyo el botón 'Compartir mi asistencia en LinkedIn'. Es estándar en Hopin/Bizzabo, y siempre se siente como flex — el evento te pide que hagas su marketing. Si el recap es bueno, el asistente lo comparte por iniciativa; si no lo es, ningún CTA va a salvarlo. Cero coerción social.",
  },
};

function NotesOverlay({ open, state, onClose }) {
  const data = NOTES[state] || NOTES.pre;
  return (
    <aside className={"notes-overlay" + (open ? " open" : "")}>
      <button className="close-notes" onClick={onClose} aria-label="Cerrar">×</button>
      <span className="label-eb">DESIGN NOTES</span>
      <h3>{data.title}</h3>
      <h4>Protagonista</h4>
      <p>{data.protag}</p>
      <h4>Decisiones de jerarquía</h4>
      <ul>
        {data.decisions.map((d, i) => <li key={i}>{d}</li>)}
      </ul>
      <h4>Decisión polémica</h4>
      <div className="polemic">
        <span className="pe">LO QUE OMITO A PROPÓSITO</span>
        <p>{data.polemic}</p>
      </div>
    </aside>
  );
}

// ============================================
// APP SHELL
// ============================================
const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "state": "pre"
}/*EDITMODE-END*/;

function App() {
  const [state, setState] = useState(TWEAK_DEFAULTS.state || "pre");
  const [notesOpen, setNotesOpen] = useState(false);

  // tweaks panel protocol
  useEffect(() => {
    const handler = (e) => {
      if (!e.data) return;
      if (e.data.type === "__activate_edit_mode") {
        // panel-driven; here we just keep our switcher visible
      }
    };
    window.addEventListener("message", handler);
    return () => window.removeEventListener("message", handler);
  }, []);

  const switchTo = (s) => {
    setState(s);
  };

  return (
    <>
      <div className="stage" data-screen-label={`Home ${state.toUpperCase()}`}>
        <div className="top-rail">
          <div className="brand-mark">
            <span className="dot"></span>
            <span className="org">EventOS</span>
            <span>· W.0 SPATIAL · HOME</span>
          </div>
          <div className="state-switcher" role="tablist">
            <button className={state === "pre" ? "active" : ""} onClick={() => switchTo("pre")}>PRE-EVENT</button>
            <button className={state === "live" ? "active" : ""} onClick={() => switchTo("live")}>LIVE</button>
            <button className={state === "ended" ? "active" : ""} onClick={() => switchTo("ended")}>ENDED</button>
          </div>
          <button
            className={"notes-toggle" + (notesOpen ? " on" : "")}
            onClick={() => setNotesOpen((v) => !v)}
          >
            {notesOpen ? "Cerrar notas ×" : "Design notes ↗"}
          </button>
        </div>

        <div className="workspace">
          <div className={"workspace-inner" + (state === "pre" ? " active" : "")}>
            <PreEvent active={state === "pre"} />
          </div>
          <div className={"workspace-inner" + (state === "live" ? " active" : "")}>
            <Live active={state === "live"} />
          </div>
          <div className={"workspace-inner" + (state === "ended" ? " active" : "")}>
            <Ended />
          </div>
        </div>
      </div>

      <NotesOverlay open={notesOpen} state={state} onClose={() => setNotesOpen(false)} />
    </>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
