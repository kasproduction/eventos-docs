// M2.1.2: capturas de pagina completa del admin de Filament. Kamilo entra a mano (clave + codigo); el script no toca credenciales.
import { chromium } from '@playwright/test';
import fs from 'node:fs';
const BASE = 'http://eventos-backend.test/';
const OUT = 'C:/laragon/www/APP EVENTOS/design/features/admin/filament-actual';
fs.mkdirSync(OUT, { recursive: true });
const urls = `admin
mi-seguridad
agenda/event-sessions|agenda/event-sessions/create|@agenda/event-sessions
agenda/session-tracks|agenda/session-tracks/create|agenda/session-tracks/6/edit
agenda/session-types|agenda/session-types/create
agenda/session-ratings
speakers|speakers/create|speakers/19/edit
sponsors/sponsors|sponsors/sponsors/create|sponsors/sponsors/1/edit
documents|documents/create|documents/7/edit
custom-pages|custom-pages/create|custom-pages/1/edit
asistentes/attendee-admins|@asistentes/attendee-admins|asistentes/attendee-bans
entrada/registration-settings/1/edit|entrada/registration-fields|entrada/registration-fields/create|entrada/registration-fields/1/edit|entrada/access-codes|entrada/landing-widget
anuncios/announcements|anuncios/announcements/create|anuncios/announcements/1/edit|anuncios/scheduled-notifications|anuncios/scheduled-notifications/create|anuncios/reminder-settings/1/edit
emails/email-templates|emails/organization-email-settings/1/edit|emails/email-logs
identidad/event-branding/1/edit|#Identidad común|#App móvil|#Onboarding app|#Webapp|identidad/login-slides/1/edit|identidad/highlights|identidad/highlights/create|identidad/highlights/1/edit|identidad/event-faqs|identidad/event-faqs/create|identidad/event-faqs/1/edit|identidad/event-recap-settings/1/edit
modulos
social/wall-posts|social/wall-posts/1|social/event-photos|social/event-photos/1|social/wall-settings/1/edit
gamificacion/gamification-settings/1/edit|gamificacion/golden-tickets|gamificacion/rewards|gamificacion/rewards/create|@gamificacion/rewards|gamificacion/prize-redemptions
salones|salones/1/edit
chat-settings/1/edit
event-pulse/1/edit
support-requests|@support-requests
encuestas|@encuestas
live-game-results
events|events/create
users|users/create|users/2/edit
rate-limit-settings/1/edit
webhooks/webhook-endpoints|webhooks/webhook-endpoints/create|webhooks/webhook-endpoints/1/edit|webhooks/webhook-api-keys|webhooks/webhook-api-keys/create|webhooks/webhook-logs`
  .split(/[\n|]/).map(s => s.trim()).filter(Boolean).map(s => s === 'admin' ? 'admin' : 'admin/' + s);

// Segunda pasada (2026-10-25): '@lista' abre el primer "editar" de esa lista; '#Pestana' captura una pestana interna de
// la pagina anterior. Primero Noir (solo lo que falta si SOLO_FALTAN=1), despues Lux en lux/.
const SOLO = process.env.SOLO_FALTAN === '1';
const FALTAN = ['@agenda/event-sessions', '@asistentes/attendee-admins', '@gamificacion/rewards', '@support-requests', 'encuestas', '@encuestas', '#Identidad común', '#App móvil', '#Onboarding app', '#Webapp'];
const browser = await chromium.launch({ headless: false });
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
const page = await ctx.newPage();
await page.goto(BASE + 'admin/login');
console.log('Esperando que Kamilo entre...');
await page.waitForURL(u => /\/admin\/?$/.test(u.pathname), { timeout: 15 * 60_000 });

async function pasada(tema, dir, soloFaltan) {
  await page.evaluate(t => localStorage.setItem('theme', t), tema);
  fs.mkdirSync(dir, { recursive: true });
  const log = [];
  let ultima = null;
  for (const [i, raw] of urls.entries()) {
    const u = raw.replace(/^admin\//, '');
    const prev = urls[i - 1]?.replace(/^admin\//, '');
    if (soloFaltan && !FALTAN.includes(u)) continue;
    const n = String(i + 1).padStart(2, '0');
    const name = n + '-' + (u === 'admin' ? 'escritorio' : u.replace(/^[@#]/, m => m === '@' ? 'editar-' : 'pestana-').replace(/[\/ ]/g, '_').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase());
    try {
      let status = 200;
      if (u.startsWith('@')) {
        await page.goto(BASE + 'admin/' + u.slice(1), { waitUntil: 'networkidle', timeout: 30_000 });
        const href = await page.locator('main a[href*="/edit"]').first().getAttribute('href', { timeout: 10_000 });
        status = (await page.goto(href, { waitUntil: 'networkidle', timeout: 30_000 }))?.status();
      } else if (u.startsWith('#')) {
        if (soloFaltan && ultima !== 'branding') {
          await page.goto(BASE + 'admin/identidad/event-branding/1/edit', { waitUntil: 'networkidle', timeout: 30_000 });
          ultima = 'branding';
        }
        await page.getByRole('tab', { name: u.slice(1), exact: true }).first().click();
        await page.waitForLoadState('networkidle');
      } else {
        status = (await page.goto(BASE + (u === 'admin' ? 'admin' : 'admin/' + u), { waitUntil: 'networkidle', timeout: 30_000 }))?.status();
        ultima = u === 'identidad/event-branding/1/edit' ? 'branding' : u;
      }
      await page.waitForTimeout(700);
      await page.screenshot({ path: `${dir}/${name}.png`, fullPage: true });
      log.push(`${name}.png ${status} ${page.url().replace(BASE, '')} | ${await page.title()}`);
    } catch (e) { log.push(`${name} ERROR ${e.message.split('\n')[0]}`); }
    console.log(tema, log.at(-1));
  }
  fs.writeFileSync(`${dir}/_indice${soloFaltan ? '-faltantes' : ''}.txt`, log.join('\n') + '\n');
}
await pasada('dark', OUT, SOLO);
await pasada('light', OUT + '/lux', false);
await page.evaluate(() => localStorage.setItem('theme', 'dark'));
await browser.close();
