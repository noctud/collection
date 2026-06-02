import { defineConfig } from "vitepress";
import { tabsMarkdownPlugin } from "vitepress-plugin-tabs";

export default defineConfig({
  title: "Collection",
  description:
    "Enterprise-grade, type-safe, mutable/immutable collections for PHP 8.4+",
  cleanUrls: true,

  markdown: {
    config(md) {
      md.use(tabsMarkdownPlugin);
    },
  },

  themeConfig: {
    logo: { light: "/logo.svg", dark: "/logo-dark.svg" },

    nav: [
      { text: "Getting Started", link: "/collection/getting-started" },
      { text: "Guide", link: "/collection/list" },
      { text: "API Reference", link: "/collection/api/collection" },
      { text: "FAQ", link: "/collection/faq" },
    ],

    sidebar: [
      {
        text: "Introduction",
        items: [
          {
            text: "Getting Started",
            link: "/collection/getting-started",
          },
          { text: "Cheatsheet", link: "/collection/cheatsheet" },
          { text: "Design", link: "/collection/design" },
          { text: "FAQ", link: "/collection/faq" },
        ],
      },
      {
        text: "Guide",
        items: [
          { text: "List", link: "/collection/list" },
          { text: "Set", link: "/collection/set" },
          { text: "Map", link: "/collection/map" },
          { text: "Mutability", link: "/collection/mutability" },
          { text: "Lazy Init", link: "/collection/lazy-init" },
          { text: "Sorting", link: "/collection/sorting" },
          { text: "Best Practices", link: "/collection/best-practices" },
          { text: "Extending", link: "/collection/extending" },
        ],
      },
      {
        text: "API Reference",
        items: [
          { text: "Collection", link: "/collection/api/collection" },
          { text: "List", link: "/collection/api/list" },
          { text: "Set", link: "/collection/api/set" },
          { text: "Map", link: "/collection/api/map" },
          { text: "Factory Functions", link: "/collection/api/functions" },
        ],
      },
    ],

    socialLinks: [
      { icon: "github", link: "https://github.com/noctud/collection" },
      { icon: "discord", link: "https://discord.gg/jS3fKe6vW9" },
    ],

    search: {
      provider: "local",
    }
  },
});
