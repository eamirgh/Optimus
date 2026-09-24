import { defineConfig } from 'vitepress';

export default defineConfig({
  title: 'Laravel Optimus',
  description: 'High-Performance Image & Asset Performance Optimization Engine for Laravel',
  lang: 'en-US',
  lastUpdated: true,
  cleanUrls: true,

  base: process.env.BASE_PATH || '/optimus/',

  head: [
    ['meta', { name: 'theme-color', content: '#f97316' }],
  ],

  themeConfig: {
    siteTitle: 'Laravel Optimus',

    nav: [
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'Blade Components', link: '/guide/blade-components' },
      { text: 'Drivers', link: '/guide/image-drivers' },
      { text: 'Security', link: '/guide/security-and-caching' },
      { text: 'Benchmarks', link: '/guide/benchmarks' },
      {
        text: 'v1.x',
        items: [
          { text: 'GitHub', link: 'https://github.com/eamirgh/optimus' },
        ],
      },
    ],

    sidebar: [
      {
        text: 'Getting Started',
        items: [
          { text: 'Introduction & Setup', link: '/guide/getting-started' },
          { text: 'Configuration', link: '/guide/configuration' },
        ],
      },
      {
        text: 'Components & Frontend',
        items: [
          { text: 'Blade Components', link: '/guide/blade-components' },
          { text: 'CSS Backgrounds', link: '/guide/css-backgrounds' },
        ],
      },
      {
        text: 'Backend Architecture',
        items: [
          { text: 'Image Processing Drivers', link: '/guide/image-drivers' },
          { text: 'Security & DoS Protection', link: '/guide/security-and-caching' },
          { text: 'Async Queue & Scaling', link: '/guide/async-and-queues' },
          { text: 'Performance Benchmarks', link: '/guide/benchmarks' },
        ],
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/eamirgh/optimus' },
    ],

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2026 Amir Ghaffari',
    },

    search: {
      provider: 'local',
    },
  },
});
