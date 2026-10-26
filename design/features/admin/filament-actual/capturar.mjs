// M2.1.2: capturas de pagina completa del admin de Filament. Kamilo entra a mano (clave + codigo); el script no toca credenciales.
import { chromium } from '@playwright/test';
import fs from 'node:fs';
const BASE = 'http://eventos-backend.test/';
const OUT = 'C:/laragon/www/APP EVENTOS/design/features/admin/filament-actual';
fs.mkdirSync(OUT, { recursive: true });
const urls = `admin
mi-seguridad
agenda/event-sessions|agenda/event-sessions/create|agenda/event-sessions/42/edit
agenda/session-tracks|agenda/session-tracks/create|agenda/session-tracks/6/edit
agenda/session-types|agenda/session-types/create
agenda/session-ratings
speakers|speakers/create|speakers/19/edit
sponsors/sponsors|sponsors/sponsors/create|sponsors/sponsors/1/edit
documents|documents/create|documents/7/edit
custom-pages|custom-pages/create|custom-pages/1/edit
asistentes/attendee-admins|asistentes/attendee-admins/1/edit|asistentes/attendee-bans
entrada/registration-settings/1/edit|entrada/registration-fields|entrada/registration-fields/create|entrada/registration-fields/1/edit|entrada/access-codes|entrada/landing-widget
anuncios/announcements|anuncios/announcements/create|anuncios/announcements/1/edit|anuncios/scheduled-notifications|anuncios/scheduled-notifications/create|anuncios/reminder-settings/1/edit
emails/email-templates|emails/organization-email-settings/1/edit|emails/email-logs
identidad/event-branding/1/edit|identidad/login-slides/1/edit|identidad/highlights|identidad/highlights/create|identidad/highlights/1/edit|identidad/event-faqs|identidad/event-faqs/create|identidad/event-faqs/1/edit|identidad/event-recap-settings/1/edit
modulos
social/wall-posts|social/wall-posts/1|social/event-photos|social/event-photos/1|social/wall-settings/1/edit
gamificacion/gamification-settings/1/edit|gamificacion/golden-tickets|gamificacion/rewards|gamificacion/rewards/create|gamificacion/rewards/1/edit|gamificacion/prize-redemptions
salones|salones/1/edit
chat-settings/1/edit
event-pulse/1/edit
support-requests
live-game-results
events|events/create
users|users/create|users/2/edit
rate-limit-settings/1/edit
webhooks/webhook-endpoints|webhooks/webhook-endpoints/create|webhooks/webhook-endpoints/1/edit|webhooks/webhook-api-keys|webhooks/webhook-api-keys/create|webhooks/webhook-logs`
  .split(/[\n|]/).map(s => s.trim()).filter(Boolean).map(s => s === 'admin' ? 'admin' : 'admin/' + s);

const browser = await chromium.launch({ headless: false });
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
const page = await ctx.newPage();
await page.goto(BASE + 'admin/login');
console.log('Esperando que Kamilo entre...');
await page.waitForURL(u => /\/admin\/?$/.test(u.pathname), { timeout: 15 * 60_000 });
console.log('Dentro. Capturando', urls.length);
const log = [];
for (const [i, u] of urls.entries()) {
  const name = String(i + 1).padStart(2, '0') + '-' + u.replace(/^admin\/?/, '').replace(/\//g, '_') ;
  try {
    const r = await page.goto(BASE + u, { waitUntil: 'networkidle', timeout: 30_000 });
    await page.waitForTimeout(700);
    const file = (name === String(i + 1).padStart(2, '0') + '-' ? name + 'escritorio' : name) + '.png';
    await page.screenshot({ path: `${OUT}/${file}`, fullPage: true });
    log.push(`${file} ${r?.status()} ${page.url().replace(BASE, '')} | ${await page.title()}`);
  } catch (e) { log.push(`${name} ERROR ${e.message.split('\n')[0]}`); }
  console.log(log.at(-1));
}
fs.writeFileSync(`${OUT}/_indice.txt`, log.join('\n') + '\n');
await browser.close();
