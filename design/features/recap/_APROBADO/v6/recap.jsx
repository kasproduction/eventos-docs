/* global React, ReactDOM, IOSDevice */

const { useState, useEffect, useRef, useCallback, useMemo } = React;

// ============================================================
// TIERS
// ============================================================
const TIERS = {
  insider:   { label: "Insider",   roman: "I",   color: "#22D3EE", min: 0, max: 3   },
  activo:    { label: "Activo",    roman: "II",  color: "#FF2E93", min: 3, max: 8   },
  headliner: { label: "Headliner", roman: "III", color: "#F59E0B", min: 8, max: 999 },
};

function calcTier(hours) {
  if (hours < TIERS.insider.max)   return "insider";
  if (hours < TIERS.activo.max)    return "activo";
  return "headliner";
}

// ============================================================
// Color presets
// ============================================================
const PRESETS = [
  { id: "magenta",  label: "Magenta + Violeta", c1: "#FF2E93", c2: "#7C3AED" },
  { id: "cyan",     label: "Cian + Magenta",    c1: "#06B6D4", c2: "#EC4899" },
  { id: "yellow",   label: "Amarillo + Negro",  c1: "#FACC15", c2: "#1F1B0A" },
  { id: "orange",   label: "Naranja + Rojo",    c1: "#F97316", c2: "#DC2626" },
  { id: "electric", label: "Electric Blue",     c1: "#3B82F6", c2: "#1E1B4B" },
  { id: "mint",     label: "Mint + Navy",       c1: "#10B981", c2: "#1E3A8A" },
  { id: "sunset",   label: "Sunset",            c1: "#F472B6", c2: "#FB923C" },
  { id: "matrix",   label: "Matrix",            c1: "#22D3EE", c2: "#0F172A" },
];

// ============================================================
// Color signature determinístico (hash del nombre → HSL)
// ============================================================
function hashName(name) {
  let h = 0;
  for (let i = 0; i < name.length; i++) {
    h = ((h << 5) - h) + name.charCodeAt(i);
    h |= 0;
  }
  return Math.abs(h);
}

function colorSignature(name) {
  const h = hashName(name);
  const hue1 = h % 360;
  const hue2 = (h * 7 + 47) % 360;
  return {
    c1: `hsl(${hue1}, 80%, 58%)`,
    c2: `hsl(${hue2}, 70%, 42%)`,
  };
}

// ============================================================
// Serial number determinístico
// ============================================================
function generateSerial(profile) {
  const h = hashName(profile.name + profile.eventName);
  const num = (h % 9999).toString().padStart(4, "0");
  return `${profile.eventName}·2026·#${num}`;
}

function generateVerifyId(profile) {
  const h = hashName(profile.name + profile.eventName);
  const code = h.toString(36).slice(0, 6).toUpperCase();
  return code;
}

// ============================================================
// Profiles
// ============================================================
const PROFILES = {
  full: {
    label: "Asistente completo",
    name: "Maria Salgado",
    handle: "@maria.salgado",
    role: "Product Designer · Linear",
    hasPhoto: false,
    eventName: "FUTURESTACK",
    edition: "EDIC. 06",
    days: 3,
    daysRange: "14 — 16 ABR",
    sessionsCount: 19,
    hours: 14,
    minutes: 32,
    sessions: [
      { time: "DIA 1 · 09:00", title: "Apertura — La decada que viene" },
      { time: "DIA 1 · 11:30", title: "La arquitectura silenciosa" },
      { time: "DIA 1 · 14:00", title: "Cuando los equipos se vuelven autonomos" },
      { time: "DIA 1 · 16:30", title: "Disenar interfaces que respiran" },
      { time: "DIA 2 · 09:30", title: "El precio oculto de no decidir" },
      { time: "DIA 2 · 11:00", title: "Workshop: Sistemas de diseno" },
      { time: "DIA 2 · 14:30", title: "Panel: El futuro del trabajo" },
      { time: "DIA 3 · 10:00", title: "Cierre — Lo que nos espera" },
    ],
  },
  withoutNetworking: {
    label: "Activo sin red",
    name: "Lucia Vega",
    handle: "@lucia.vega",
    role: "Engineering Lead · Vercel",
    hasPhoto: false,
    eventName: "FUTURESTACK",
    edition: "EDIC. 06",
    days: 3,
    daysRange: "14 — 16 ABR",
    sessionsCount: 12,
    hours: 9,
    minutes: 18,
    sessions: [
      { time: "DIA 1 · 09:00", title: "Apertura — La decada que viene" },
      { time: "DIA 1 · 11:30", title: "Arquitectura distribuida" },
      { time: "DIA 1 · 14:00", title: "Equipos autonomos" },
      { time: "DIA 2 · 09:30", title: "Decisiones de producto" },
      { time: "DIA 2 · 14:30", title: "El futuro del trabajo" },
      { time: "DIA 3 · 10:00", title: "Cierre" },
    ],
  },
  networker: {
    label: "Networker puro",
    name: "Carlos Ruiz",
    handle: "@carlos.ruiz",
    role: "Founder · Tally",
    hasPhoto: false,
    eventName: "FUTURESTACK",
    edition: "EDIC. 06",
    days: 2,
    daysRange: "14 — 15 ABR",
    sessionsCount: 4,
    hours: 3,
    minutes: 12,
    sessions: [
      { time: "DIA 1 · 09:00", title: "Apertura" },
      { time: "DIA 1 · 14:00", title: "Equipos autonomos" },
      { time: "DIA 2 · 09:30", title: "Decisiones de producto" },
      { time: "DIA 2 · 17:00", title: "Networking — cierre dia 2" },
    ],
  },
  oneSession: {
    label: "Solo 1 charla",
    name: "Ana Martin",
    handle: "@ana.martin",
    role: "Designer · Independent",
    hasPhoto: false,
    eventName: "FUTURESTACK",
    edition: "EDIC. 06",
    days: 1,
    daysRange: "14 ABR",
    sessionsCount: 1,
    hours: 0,
    minutes: 52,
    sessions: [
      { time: "DIA 1 · 16:30", title: "Disenar interfaces que respiran" },
    ],
  },
  withPhoto: {
    label: "Con foto",
    name: "Diego Paez",
    handle: "@diego.paez",
    role: "Engineering · Vercel",
    hasPhoto: true,
    eventName: "FUTURESTACK",
    edition: "EDIC. 06",
    days: 3,
    daysRange: "14 — 16 ABR",
    sessionsCount: 16,
    hours: 12,
    minutes: 14,
    sessions: [
      { time: "DIA 1 · 09:00", title: "Apertura" },
      { time: "DIA 1 · 11:30", title: "Arquitectura distribuida" },
      { time: "DIA 2 · 09:30", title: "Decisiones de producto" },
      { time: "DIA 3 · 10:00", title: "Cierre" },
    ],
  },
  empty: {
    label: "Sin actividad",
    name: "Pedro Soto",
    handle: "@pedro.soto",
    role: null,
    hasPhoto: false,
    eventName: "FUTURESTACK",
    edition: "EDIC. 06",
    days: 0,
    daysRange: "—",
    sessionsCount: 0,
    hours: 0,
    minutes: 0,
    sessions: [],
  },
};

// ============================================================
// Helpers
// ============================================================
function shouldGenerateRecap(p) {
  return p.sessionsCount >= 1 || p.hours >= 1 || p.minutes >= 1;
}

function formatTiempo(p) {
  if (p.hours >= 1) return `${p.hours}h ${String(p.minutes).padStart(2,"0")}m`;
  return `${p.minutes}m`;
}

function getInitials(name) {
  return name.trim().split(/\s+/).map(w => w[0]).slice(0, 2).join("").toUpperCase();
}

function splitName(name) {
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return [parts[0]];
  return [parts[0], parts.slice(1).join(" ")];
}

// ============================================================
// Lockups (mockups de keyvisuales tipograficos)
// En produccion: Filament FileUpload guarda PNG transparente,
// la API devuelve la URL, este componente la sustituye por <img>.
// ============================================================
function HorizontalLockup({ tone = "light", size = "lg" }) {
  const stroke = tone === "light" ? "#FFFFFF" : "#0A0A0F";
  const fill = stroke;
  const w = size === "mini" ? 110 : 280;
  const h = size === "mini" ? 18 : 56;
  return (
    <svg viewBox="0 0 280 56" width={w} height={h} aria-label="FUTURESTACK 2026">
      <text
        x="0" y="40"
        fontFamily="Plus Jakarta Sans, sans-serif"
        fontWeight="800"
        fontSize="38"
        letterSpacing="-1.6"
        fill={fill}
      >FUTURESTACK</text>
      <rect x="232" y="8" width="1" height="40" fill={fill} opacity="0.5" />
      <text
        x="240" y="22"
        fontFamily="Plus Jakarta Sans, sans-serif"
        fontWeight="600"
        fontSize="9"
        letterSpacing="2"
        fill={fill}
        opacity="0.9"
      >EDIC</text>
      <text
        x="240" y="36"
        fontFamily="Plus Jakarta Sans, sans-serif"
        fontWeight="800"
        fontSize="14"
        letterSpacing="-0.4"
        fill={fill}
      >06</text>
      <text
        x="240" y="48"
        fontFamily="Plus Jakarta Sans, sans-serif"
        fontWeight="500"
        fontSize="8"
        letterSpacing="1.2"
        fill={fill}
        opacity="0.65"
      >2026</text>
    </svg>
  );
}

function PosterLockup({ tone = "light" }) {
  const fill = tone === "light" ? "#FFFFFF" : "#0A0A0F";
  return (
    <svg viewBox="0 0 240 320" width={240} height={320} aria-label="FUTURESTACK Poster 2026">
      {/* Top bar */}
      <line x1="20" y1="32" x2="220" y2="32" stroke={fill} strokeWidth="1" opacity="0.5" />
      <text x="20" y="26" fontFamily="Plus Jakarta Sans" fontWeight="600" fontSize="9" letterSpacing="1.8" fill={fill} opacity="0.7">CONFERENCIA</text>
      <text x="220" y="26" textAnchor="end" fontFamily="Plus Jakarta Sans" fontWeight="600" fontSize="9" letterSpacing="1.8" fill={fill} opacity="0.7">EDIC. 06</text>

      {/* Monogram FS giant */}
      <text
        x="120" y="170"
        textAnchor="middle"
        fontFamily="Plus Jakarta Sans"
        fontWeight="800"
        fontSize="180"
        letterSpacing="-10"
        fill={fill}
      >FS</text>

      {/* Subtitle FUTURESTACK */}
      <text
        x="120" y="210"
        textAnchor="middle"
        fontFamily="Plus Jakarta Sans"
        fontWeight="700"
        fontSize="22"
        letterSpacing="2"
        fill={fill}
      >FUTURESTACK</text>

      {/* Decorative rays */}
      <g opacity="0.35">
        {Array.from({ length: 12 }).map((_, i) => {
          const a = (i / 12) * 360;
          const rad = (a * Math.PI) / 180;
          const x1 = 120 + Math.cos(rad) * 50;
          const y1 = 165 + Math.sin(rad) * 50;
          const x2 = 120 + Math.cos(rad) * 100;
          const y2 = 165 + Math.sin(rad) * 100;
          return <line key={i} x1={x1} y1={y1} x2={x2} y2={y2} stroke={fill} strokeWidth="0.6" />;
        })}
      </g>

      {/* Bottom bar */}
      <line x1="20" y1="260" x2="220" y2="260" stroke={fill} strokeWidth="1" opacity="0.5" />
      <text
        x="120" y="288"
        textAnchor="middle"
        fontFamily="Plus Jakarta Sans"
        fontWeight="200"
        fontSize="56"
        letterSpacing="-2"
        fill={fill}
      >2026</text>
    </svg>
  );
}

// ============================================================
// SunRays
// ============================================================
function SunRays({ count = 24, opacity = 0.5 }) {
  const rays = Array.from({ length: count }, (_, i) => {
    const angle = (360 / count) * i;
    return (
      <line
        key={i}
        x1="50" y1="50"
        x2="50" y2="2"
        stroke="white"
        strokeWidth={i % 2 === 0 ? 0.4 : 0.18}
        opacity={i % 2 === 0 ? opacity : opacity * 0.5}
        transform={`rotate(${angle} 50 50)`}
      />
    );
  });
  return (
    <svg viewBox="0 0 100 100" width="100%" height="100%" style={{ overflow: "visible" }}>
      {rays}
    </svg>
  );
}

// ============================================================
// Cover
// ============================================================
function Cover({ profile, onContinue, titleType }) {
  const initials = getInitials(profile.name);
  const nameLines = splitName(profile.name);
  const useLockup = titleType === "horizontal" || titleType === "poster";
  return (
    <section className="cover">
      {/* iniciales watermark */}
      <div className="cover-watermark">
        <span>{initials}</span>
      </div>

      <div className="cover-rays">
        <SunRays count={28} opacity={0.55} />
      </div>
      <div className="cover-rays-2">
        <SunRays count={16} opacity={0.40} />
      </div>

      <span className="cover-spark s1">✦</span>
      <span className="cover-spark s2">✧</span>
      <span className="cover-spark s3">✦</span>
      <span className="cover-spark s4">✧</span>
      <span className="cover-spark s5">✦</span>
      <span className="cover-spark s6">✧</span>

      <div className="cover-meta">
        {useLockup ? (
          <div className="cover-meta-tag lockup-mini">
            <HorizontalLockup tone="light" size="mini" />
          </div>
        ) : (
          <div className="cover-meta-tag">{profile.eventName}</div>
        )}
        <div className="cover-meta-edition">{profile.edition} · 2026</div>
      </div>

      <div className="cover-protagonist">
        <div className="cover-eyebrow">Tu Recap</div>

        {profile.hasPhoto ? (
          <>
            <div className="cover-photo">
              <span className="initials-mini">{initials}</span>
            </div>
            <div className="cover-secondary-name">{profile.name}</div>
          </>
        ) : (
          <h1 className="cover-name-hero">
            {nameLines.map((line, i) => (
              <span key={i} className={`ln ln-${i + 1}`}>{line}</span>
            ))}
          </h1>
        )}
      </div>

      <div className="cover-foot">
        <div className="cover-event">{profile.daysRange} · 2026</div>
        <button className="cover-cta" onClick={onContinue}>
          <span>Ver mi recap</span>
          <span className="arrow">→</span>
        </button>
      </div>
    </section>
  );
}

// ============================================================
// Card final con flip 3D
// ============================================================
function FinalCard({ profile, titleType }) {
  const initials = getInitials(profile.name);
  const nameLines = splitName(profile.name);
  const tierKey = calcTier(profile.hours);
  const tier = TIERS[tierKey];
  const serial = generateSerial(profile);
  const verifyCode = generateVerifyId(profile);
  const [flipped, setFlipped] = useState(false);

  const onCardClick = (e) => {
    // Evita flip si clickea botones u otros interactivos
    if (e.target.closest("button")) return;
    setFlipped(f => !f);
  };

  return (
    <section className="final" style={{ "--tier": tier.color }}>
      <div className="final-eyebrow">02 · Tu certificado compartible</div>

      <div className="card-3d-scene">
        <div
          className={"card-3d-inner " + (flipped ? "flipped" : "")}
          onClick={onCardClick}
        >
          {/* FRONT */}
          <div className="card-face front">
            <div className="share-card">
              <div className="share-watermark"><span>{initials}</span></div>
              <div className="share-rays">
                <SunRays count={24} opacity={0.40} />
              </div>
              <div className="share-pattern" />
              <div className="share-grain" />

              <div className="share-content">
                <div className="share-head">
                  <div className="share-avatar">{initials}</div>
                  <div className="share-head-text">
                    <span className="share-head-name">{profile.handle.replace("@", "")}</span>
                    {profile.role && <span className="share-head-sub">{profile.role}</span>}
                  </div>
                  <span className="share-x">···</span>
                </div>

                {profile.hasPhoto ? (
                  <>
                    <div className="share-protagonist-photo">
                      <span className="initials">{initials}</span>
                    </div>
                    <div className="share-protagonist-name with-photo">
                      <h2 className="share-name-hero">
                        {nameLines.map((line, i) => (
                          <span key={i} className={`ln ln-${i + 1}`}>{line}</span>
                        ))}
                      </h2>
                    </div>
                  </>
                ) : (
                  <div className="share-protagonist-name">
                    <h2 className="share-name-hero">
                      {nameLines.map((line, i) => (
                        <span key={i} className={`ln ln-${i + 1}`}>{line}</span>
                      ))}
                    </h2>
                  </div>
                )}

                {/* Tier insignia asimétrica */}
                <div className="tier-insignia">
                  <div className="tier-roman">{tier.roman}</div>
                  <div className="tier-meta">
                    <div className="tier-meta-label">{tier.label}</div>
                    <div className="tier-meta-sub">Tier · Permanencia certificada</div>
                  </div>
                </div>

                <div className="share-cert">
                  <div className="share-cert-label">Certificado de asistencia</div>
                  <div className="share-cert-stats">
                    <div className="cert-stat">
                      <div className="cert-v">{formatTiempo(profile)}</div>
                      <div className="cert-k">en vivo</div>
                    </div>
                    <div className="cert-stat">
                      <div className="cert-v">{profile.sessionsCount}</div>
                      <div className="cert-k">{profile.sessionsCount === 1 ? "sesion" : "sesiones"}</div>
                    </div>
                    <div className="cert-stat">
                      <div className="cert-v">{profile.days}</div>
                      <div className="cert-k">{profile.days === 1 ? "dia" : "dias"}</div>
                    </div>
                  </div>
                </div>

                <div className="share-event">
                  {titleType === "horizontal" ? (
                    <div className="share-event-lockup">
                      <HorizontalLockup tone="light" size="lg" />
                    </div>
                  ) : titleType === "poster" ? (
                    <div className="share-event-lockup poster">
                      <PosterLockup tone="light" />
                    </div>
                  ) : (
                    <div className="share-event-name">{profile.eventName}</div>
                  )}
                  <div className="share-event-date">{profile.daysRange} · 2026</div>
                </div>

                <div className="share-foot">
                  <div className="share-foot-row">
                    <div className="share-handle">{profile.handle}</div>
                    <div className="share-serial">#{serial.split("#")[1]}</div>
                  </div>
                  <div className="share-verify">verifica en eventos.app/r/{verifyCode}</div>
                </div>
              </div>
            </div>
          </div>

          {/* BACK */}
          <div className="card-face back">
            <div className="share-back">
              <div className="back-content">
                <div className="back-header">
                  <div className="back-title">Detalle</div>
                  <div className="back-stamp">Auténtico</div>
                </div>

                <div className="back-section">
                  <div className="back-section-h">Sesiones registradas</div>
                  <div className="back-list">
                    {profile.sessions.slice(0, 8).map((s, i) => (
                      <div key={i} className="back-list-item">
                        <span className="when">{s.time}</span>
                        <span className="what">{s.title}</span>
                      </div>
                    ))}
                    {profile.sessions.length > 8 && (
                      <div className="back-list-item" style={{ borderBottom: "none", opacity: 0.6 }}>
                        <span className="when">···</span>
                        <span className="what">+ {profile.sessions.length - 8} sesiones más</span>
                      </div>
                    )}
                  </div>
                </div>

                <div className="back-serial-block">
                  <div className="back-serial-k">Numero de serie</div>
                  <div className="back-serial">{serial}</div>

                  <div className="back-qr-row">
                    <div className="back-qr">
                      {Array.from({ length: 121 }).map((_, i) => (
                        <span key={i} style={{ opacity: ((i * 13) % 5) === 0 ? 1 : 0.10 }} />
                      ))}
                    </div>
                    <div className="back-verify">
                      Escanea para verificar.<br />
                      <b>eventos.app/r/{verifyCode}</b>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="final-flip-hint">Toca la card para girar</div>

      <div className="share-actions">
        <button className="btn-circle" title="Volver">‹</button>
        <button className="btn-share">
          <span className="ig-icon">IG</span>
          <span>Compartir en Stories</span>
        </button>
        <button className="btn-circle dark" title="Guardar">↓</button>
      </div>
    </section>
  );
}

// ============================================================
// Empty
// ============================================================
function EmptyState({ profile }) {
  return (
    <section className="empty">
      <div className="empty-icon">∅</div>
      <div className="empty-tag">Sin actividad registrada</div>
      <div className="empty-headline">No hay recap esta vez, {profile.name.split(" ")[0]}.</div>
      <div className="empty-body">
        Sin sesiones, sin tiempo en vivo. El backend no genera certificado vacio.
      </div>
    </section>
  );
}

// ============================================================
// Custom Panel
// ============================================================
function Panel(props) {
  const {
    profile, profileKey, setProfileKey,
    c1, c2, setC1, setC2, presetId, setPreset,
    useSignature, setUseSignature,
    titleType, setTitleType,
  } = props;

  return (
    <aside className="panel">
      <div className="panel-header">
        <span className="panel-title">Recap v6 · DaVinci</span>
        <span className="panel-version">FLIP 3D</span>
      </div>

      <div className="panel-section">
        <h4>Perfil del attendee</h4>
        <div className="panel-radio-group">
          {Object.entries(PROFILES).map(([key, p]) => {
            const tk = (p.hours > 0 || p.sessionsCount > 0) ? calcTier(p.hours) : "none";
            return (
              <div
                key={key}
                className={"panel-radio " + (profileKey === key ? "active" : "")}
                onClick={() => setProfileKey(key)}
              >
                <span>{p.label}</span>
                <span className="tier-tag" data-tier={tk}>
                  {tk === "none" ? "—" : TIERS[tk].label}
                </span>
              </div>
            );
          })}
        </div>
      </div>

      <div className="panel-section">
        <h4>Titulo del evento</h4>
        <div className="panel-radio-group">
          <div
            className={"panel-radio " + (titleType === "text" ? "active" : "")}
            onClick={() => setTitleType("text")}
          >
            <span>Texto plano (FUTURESTACK)</span>
          </div>
          <div
            className={"panel-radio " + (titleType === "horizontal" ? "active" : "")}
            onClick={() => setTitleType("horizontal")}
          >
            <span>Lockup horizontal</span>
          </div>
          <div
            className={"panel-radio " + (titleType === "poster" ? "active" : "")}
            onClick={() => setTitleType("poster")}
          >
            <span>Lockup poster (vertical)</span>
          </div>
        </div>
        <div className="panel-note">
          En produccion el organizador sube PNG transparente desde Filament:
          <br /><code>events.recap_config.branding.title</code>
          <br />Si no sube imagen, fallback automatico a texto.
        </div>
      </div>

      <div className="panel-section">
        <h4>Modo color</h4>
        <div
          className={"toggle-row " + (useSignature ? "on" : "")}
          onClick={() => setUseSignature(!useSignature)}
        >
          <span>{useSignature ? "Signature personal (auto)" : "Color del evento"}</span>
          <span className="pill" />
        </div>
      </div>

      {!useSignature && (
        <>
          <div className="panel-section">
            <h4>Paletas (presets)</h4>
            <div className="swatches">
              {PRESETS.map(p => (
                <div
                  key={p.id}
                  className={"swatch " + (presetId === p.id ? "active" : "")}
                  style={{ background: `linear-gradient(135deg, ${p.c1}, ${p.c2})` }}
                  title={p.label}
                  onClick={() => setPreset(p)}
                />
              ))}
            </div>
          </div>

          <div className="panel-section">
            <h4>Color custom (en vivo)</h4>
            <div className="color-row">
              <label>Color 1</label>
              <span className="hex">{c1}</span>
              <input type="color" value={c1} onChange={(e) => setC1(e.target.value)} />
            </div>
            <div className="color-row">
              <label>Color 2</label>
              <span className="hex">{c2}</span>
              <input type="color" value={c2} onChange={(e) => setC2(e.target.value)} />
            </div>
          </div>
        </>
      )}

      {useSignature && (
        <div className="panel-section">
          <h4>Signature de "{profile.name}"</h4>
          <div className="color-row">
            <span className="hex">{c1}</span>
            <span className="hex">{c2}</span>
          </div>
          <div style={{ fontSize: 10, color: "rgba(255,255,255,0.5)", lineHeight: 1.4 }}>
            Hash deterministico del nombre. Mismo attendee = misma paleta evento a evento.
          </div>
        </div>
      )}
    </aside>
  );
}

// ============================================================
// App
// ============================================================
function App() {
  const [profileKey, setProfileKey] = useState("full");
  const [presetId, setPresetId] = useState("magenta");
  const [presetC1, setPresetC1] = useState("#FF2E93");
  const [presetC2, setPresetC2] = useState("#7C3AED");
  const [useSignature, setUseSignature] = useState(false);
  const [titleType, setTitleType] = useState("text"); // text | horizontal | poster

  const profile = PROFILES[profileKey] || PROFILES.full;
  const willGenerate = shouldGenerateRecap(profile);

  // Color signature determinístico — recalcula si cambia perfil
  const sig = useMemo(() => colorSignature(profile.name), [profile.name]);

  // Color efectivo según modo
  const c1 = useSignature ? sig.c1 : presetC1;
  const c2 = useSignature ? sig.c2 : presetC2;

  const deckRef = useRef(null);
  const [page, setPage] = useState(0);

  useEffect(() => {
    const deck = deckRef.current;
    if (!deck) return;
    const onScroll = () => {
      const w = deck.clientWidth;
      const idx = Math.round(deck.scrollLeft / w);
      setPage(idx);
    };
    deck.addEventListener("scroll", onScroll, { passive: true });
    return () => deck.removeEventListener("scroll", onScroll);
  }, [willGenerate]);

  useEffect(() => {
    if (deckRef.current) {
      deckRef.current.scrollLeft = 0;
      setPage(0);
    }
  }, [profileKey]);

  const goTo = useCallback((idx) => {
    if (deckRef.current) {
      const w = deckRef.current.clientWidth;
      deckRef.current.scrollTo({ left: w * idx, behavior: "smooth" });
    }
  }, []);

  const setPreset = (p) => {
    setPresetId(p.id);
    setPresetC1(p.c1);
    setPresetC2(p.c2);
  };

  const cssVars = { "--c1": c1, "--c2": c2 };
  const totalPages = willGenerate ? 2 : 1;

  return (
    <div className="app" style={cssVars}>
      <IOSDevice width={402} height={874} dark={true}>
        <div className="recap">
          <header className="topbar">
            <span
              className={"topbar-back " + (page === 1 ? "active" : "disabled")}
              onClick={() => goTo(0)}
            >‹</span>
            <div className="topbar-meta">
              <span>RECAP</span>
              <span className="dot" />
              <span>{profile.eventName}</span>
            </div>
            <span className="topbar-page">
              {String(page + 1).padStart(2, "0")} / {String(totalPages).padStart(2, "0")}
            </span>
          </header>

          {willGenerate ? (
            <>
              <div className="recap-deck" ref={deckRef}>
                <div className="recap-screen">
                  <Cover profile={profile} onContinue={() => goTo(1)} titleType={titleType} />
                </div>
                <div className="recap-screen">
                  <FinalCard profile={profile} titleType={titleType} />
                </div>
              </div>
              <div className="deck-indicator">
                <span className={"dot " + (page === 0 ? "active" : "")} onClick={() => goTo(0)} />
                <span className={"dot " + (page === 1 ? "active" : "")} onClick={() => goTo(1)} />
              </div>
            </>
          ) : (
            <div className="recap-deck">
              <div className="recap-screen">
                <EmptyState profile={profile} />
              </div>
            </div>
          )}
        </div>
      </IOSDevice>

      <Panel
        profile={profile}
        profileKey={profileKey}
        setProfileKey={setProfileKey}
        c1={c1} c2={c2}
        setC1={setPresetC1} setC2={setPresetC2}
        presetId={presetId}
        setPreset={setPreset}
        useSignature={useSignature}
        setUseSignature={setUseSignature}
        titleType={titleType}
        setTitleType={setTitleType}
      />
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
