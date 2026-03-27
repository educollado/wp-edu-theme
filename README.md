# wp-edu-theme

Tema WordPress a medida para [eduardocollado.com](https://eduardocollado.com) — blog personal y podcast sobre redes, tecnología y aprendizaje.

---

## Características

- **Diseño propio** — paleta cálida (crema + terracota), tipografía editorial con Fraunces, Figtree e IBM Plex Mono
- **Blog + Podcast** — posts normales y episodios de podcast conviven con layouts diferenciados
- **Tabla de contenidos automática** — el JS escanea los `h2`/`h3` del artículo y genera el TOC dinámicamente
- **Navegación personalizada** — walker propio con soporte de submenús y toggle mobile
- **Sin dependencias de JS externas** — vanilla JS puro, sin jQuery
- **Tamaños de imagen optimizados** — `edu-card` (640×360), `edu-hero` (1200×500), `edu-thumb` (80×80)
- **Compatible con el editor de bloques** — soporte para `wp-block-styles` y `editor-styles`

---

## Estructura

```
wp-edu-theme/
├── style.css                  # Todo el CSS (variables, reset, componentes)
├── functions.php              # Setup, enqueue, walkers y helpers
├── header.php                 # Cabecera y navegación principal
├── footer.php                 # Pie de página
├── front-page.php             # Homepage (hero + secciones podcast/blog)
├── single.php                 # Artículo individual
├── archive.php                # Listado de entradas / archivo
├── search.php                 # Resultados de búsqueda
├── page.php                   # Página estática
├── index.php                  # Fallback
├── 404.php                    # Página de error
├── sidebar.php                # Sidebar genérico
├── assets/
│   └── js/theme.js            # Nav toggle + TOC dinámico
└── template-parts/
    ├── content.php            # Card de entrada de blog
    ├── content-podcast.php    # Card de episodio de podcast
    ├── content-none.php       # Estado vacío
    └── sidebar-blog.php       # Sidebar del blog
```

---

## Instalación

1. Clona o descarga el repositorio en `wp-content/themes/`:

   ```bash
   git clone git@github.com:educollado/wp-edu-theme.git wp-content/themes/wp-edu-theme
   ```

2. Activa el tema desde **Apariencia → Temas** en el panel de WordPress.

3. Configura los menús desde **Apariencia → Menús**:
   - **Menú principal** (`primary`) — navegación de cabecera
   - **Menú footer** (`footer`) — enlaces del pie de página

4. Los episodios de podcast son posts asignados a la categoría **Podcast**.

---

## Requisitos

- WordPress 6.0 o superior
- PHP 8.0 o superior

---

## Licencia

GPL v2 o posterior — ver [LICENSE](LICENSE).
