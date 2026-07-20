import { defineConfig } from 'astro/config';
import starlight from '@astrojs/starlight';

export default defineConfig({
  integrations: [
    starlight({
      title: 'EventOS — Manual del organizador',
      defaultLocale: 'root',
      locales: {
        root: { label: 'Español', lang: 'es' },
      },
      // Los grupos se agregan a medida que sus carpetas ganan contenido
      // (autogenerate sobre carpeta vacia rompe el build).
      sidebar: [
        { label: 'Módulos', autogenerate: { directory: 'modulos' } },
      ],
      pagination: false,
    }),
  ],
});
