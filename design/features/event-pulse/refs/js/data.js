/* ============================================================
   Event Pulse — Demo Data
   En produccion esto viene del API /pulse/{slug}/bootstrap
   ============================================================ */

var EP = EP || {};

EP.AV = function (n) { return 'https://i.pravatar.cc/320?img=' + n; };

EP.IMGS = [
  'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&q=80',
  'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=600&q=80',
  'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600&q=80',
  'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=600&q=80',
  'https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=600&q=80',
  'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=600&q=80'
];

EP.FN = ['Sofia','Carlos','Laura','Andres','Maria','Pedro','Elena','Diego','Camila','Mateo','Isabella','Juan','Valentina','Nicolas','Paula','Santiago','Andrea','Lucia'];
EP.LN = ['Reyes','Morales','Herrera','Perez','Garcia','Lopez','Diaz','Gil','Rojas','Castillo','Ramirez','Cruz'];
EP.ROLES = ['Product Lead','Head of Growth','Founder','CTO','Design Director','VP Engineering','CFO','COO','Dev Lead'];
EP.INTERESTS = ['IA','Producto','Growth','Fintech','SaaS','DevOps','Diseno','Cloud','Mobile'];
EP.QUOTES = [
  'La verdadera restriccion no es compute, es el apetito por la ambiguedad.',
  'Zero-downtime es un resultado cultural, no de ingenieria.',
  'Si no lo puedes medir, no lo puedes mejorar.',
  'La innovacion no es un departamento, es una actitud.'
];
EP.SOCIAL_QUOTES = [
  'Increible keynote! Aprendiendo mucho sobre growth',
  'El networking esta brutal, ya hice 5 conexiones',
  'La trivia estuvo genial, alcance el top 10!',
  'Mejor evento del ano sin duda',
  'La charla de IA me volo la cabeza',
  'Conectando con founders increibles'
];

EP.ROOMS = [
  { name: 'Auditorio Principal', title: 'Growth Hacking: metricas que importan', speaker: 'Laura Diaz', role: 'VP Engineering', pres: 48, virt: 19, cap: 120, live: true, av: 32 },
  { name: 'Sala A', title: 'React Native a fondo', speaker: 'Andres Gil', role: 'Mobile Lead', pres: 28, virt: 6, cap: 60, live: true, av: 12 },
  { name: 'Sala B', title: 'IA en la empresa moderna', speaker: 'Maria Garcia', role: 'AI Director', pres: 22, virt: 6, cap: 50, live: true, av: 47 },
  { name: 'Sala C', title: 'DevOps para startups', speaker: '', role: '', pres: 0, virt: 0, cap: 40, live: false, av: 0 }
];

EP.SPONSORS = [
  { name: 'AWS', mark: 'AWS', leads: 23, color: 'teal' },
  { name: 'Google Cloud', mark: 'GC', leads: 18, color: 'def' },
  { name: 'Microsoft', mark: 'Ms', leads: 14, color: 'plat' },
  { name: 'Stripe', mark: 'St', leads: 12, color: 'ink' },
  { name: 'Datadog', mark: 'Dd', leads: 11, color: 'def' }
];

EP.SESSIONS = [
  { title: 'Growth Hacking: metricas que importan', speaker: 'Laura Diaz', role: 'VP Engineering', score: 4.8, count: 67, av: 32 },
  { title: 'React Native a fondo', speaker: 'Andres Gil', role: 'Mobile Lead', score: 4.6, count: 34, av: 12 },
  { title: 'IA en la empresa moderna', speaker: 'Maria Garcia', role: 'AI Director', score: 4.2, count: 28, av: 47 },
  { title: 'DevOps para startups', speaker: 'Carlos Morales', role: 'DevOps Lead', score: 4.9, count: 27, av: 22 },
  { title: 'Product-Led Growth', speaker: 'Paula Torres', role: 'Head of Product', score: 4.5, count: 31, av: 55 }
];

EP.state = { ci: 347, on: 189, msg: 1204, ld: 78, conn: 42, ratings: 156, gam: 1204 };

EP.pick = function (a) { return a[Math.floor(Math.random() * a.length)]; };
EP.rName = function () { return EP.pick(EP.FN) + ' ' + EP.pick(EP.LN); };
EP.rAv = function () { return 1 + Math.floor(Math.random() * 70); };
