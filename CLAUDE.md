# Gaia

Configurable classic WordPress theme (`wp-content/themes/gaia/`). Installed as is and configured from options pages (colors, fonts, logos…): no child themes unless a site needs blocks that do not belong in Gaia.

Gaia is currently developed for **Biokin**: everything Biokin needs goes into Gaia, written generically (no Biokin names, colors or texts in the code).

## Current implementation: Biokin

- Biokin is the community association ("sociedad energética") of the people of Lukin and Urbiola (neighbouring villages; membership not exclusive to them). It is about community action for sustainable development and environmental ethics, not only energy.
- Current projects: a collective self-consumption photovoltaic installation and a consumer group that orders food (and more) from local producers. More projects are planned.
- Phase 1 (now): public showcase. Pages: Inicio, Quiénes somos, Contacto (generic message form), Únete (membership request form), Transparencia (statutes, minutes…), Noticias, Proyectos, Agenda.
- Phase 2 (later, keep it in mind): members' personal area ("My area") with the resources of the projects each member takes part in (e.g. solar generation of their share, consumer group ordering system), plus project/member management for the board, through custom plugins.
- Content model (decided):
  - Custom post types live in a separate plugin repository, **biokin-core** (not in the theme): projects (`project`, single built with blocks), events (`event`, agenda) and documents (`document`, transparency). Theme templates display them.
  - News are regular posts. "Convocatorias" of assemblies and events go to the agenda (event type taxonomy); "open calls" go to a news category.
  - Documents have a visibility per document: public (statutes, general assembly minutes), members only (project assembly minutes), later project participants only. Members-only files must be served through a download endpoint that checks the login (uploads are public by URL).
  - Custom post type URL slugs are translated per language by biokin-core (Polylang free does not do it): e.g. `/proyectos/…` and `/eu/proiektuak/…`.

## Constraints

- No paid licenses of any kind: only free / open source plugins and tools.
- Always use the latest stable version of every technology. Ask when unsure which version to use.
- Developer works on Ubuntu 24.04 with LocalWP (SSL enabled; the Biokin site is `https://biokin.local`) and Node 24.

## Code style

- 2-space indentation everywhere (PHP, SCSS, JS, JSON).
- Everything in English: code, variable/function names, comments, front-end UI strings in source.
- Exception: admin-facing texts (SCF field labels, instructions, button labels, options page titles) are in Spanish. Field keys and names stay in English.
- Explicit, descriptive variable names.
- Vanilla JavaScript only: no frameworks, no jQuery.
- PHP functions prefixed with `gaia_`, text domain `gaia`.

## Architecture

- Classic (hybrid) theme: PHP templates (`header.php`, `footer.php`, `index.php`...), not a block/FSE theme.
- Page content is built in the block editor using **custom SCF blocks only**: `inc/blocks.php` registers every `blocks/<name>/block.json` (category "Gaia") and `allowed_block_types_all` limits pages to `gaia/*` blocks (posts keep the core blocks for now).
- `page.php` only prints `the_content()`: each block handles its own width (`.container`) and spacing.
- Each block lives in its own folder:
  ```
  blocks/<block-name>/
    block.json        # registration, render, style, script
    <block-name>.php  # render template
    <block-name>.scss # compiled to <block-name>.css next to it
    <block-name>.js   # optional
  ```
- One SCF field group per block (`acf-json/group_gaia_block_<name>.json`, title "Bloque: …", location rule "Block is equal to gaia/<name>"), field names prefixed with the block name (`hero_title`…). Use tabs (Contenido / Imagen / Ajustes) when there are many fields.
- `block.json`: `apiVersion` 3, `"category": "gaia"`, admin texts (title, description, keywords) in Spanish, `"style": ["file:./<name>.css"]`, `"acf": {"mode": "preview", "renderTemplate": "<name>.php"}`, `supports.anchor`.
- Render templates: start the wrapper with `gaia_get_block_wrapper_attributes($block, '<name>', [...modifier classes])` (block class, editor classes, anchor). Use `$is_preview` for editor-only tweaks and print a `.block-placeholder` when required fields are empty in the editor.
- Editor styles: `add_editor_style()` loads `main.css` and `editor.css` (`assets/scss/editor.scss`: Gaia blocks full width in the editor).
- **Hero** (`blocks/hero/`): full height minus the header (`calc(100svh - var(--site-header-height))`), background image with focal position and overlay strength, eyebrow, title (H1 by default, switchable to H2), text, up to 2 buttons (link + style: primary / secondary / light), left/center alignment, arrow to the next section. Dark area: redefines the semantic tokens like the footer.
- `functions.php` only loads files from `inc/` (`setup.php`, `assets.php`, `options-pages.php`, `helpers.php`, `account.php`, `avatars.php`, `blocks.php`, `appearance.php`, `option-styles.php`, `logos.php`, `header.php`, `footer.php`, `page-settings.php`, `extras.php`, `options-admin.php`).
- Page layout: `body` is a flex column (`min-height: 100dvh`), `.site-main` grows so the footer sits at the bottom.
- Root templates: `page.php` (blocks only), `single.php` (`.entry.container--narrow`, `.entry-content` for core blocks), `index.php` (blog, archives and search listing with pagination), `404.php`.
- Options pages (`gaia_get_options_subpages()` in `inc/options-pages.php`, one field group per page in `acf-json/group_gaia_<name>.json`) under the parent **Ajustes de Gaia** (`gaia-settings`):
  - **Colores** (`colors_*`), **Tipografía** (`typography_*`), **Estilo** (`style_*`), **Logos** (`logos_*`): see Design tokens.
  - **Cabecera** (`header_*`), **Pie de página** (`footer_*`): see Header / Footer.
  - **Contacto** (`contact_phones` repeater: `number`, `label`; `contact_emails` repeater: `address`, `label`; `contact_address` (translatable); `contact_social_networks` repeater: `network`, `url`): reused in the footer and blocks. Use `gaia_get_contact_details()`.
  - **Extras** (`extras_*`): announcement bar, maintenance mode, breadcrumbs, back to top, 404 texts.
- Design options map (`inc/option-styles.php`, `gaia_get_option_style_map()`): every option that changes the design is mapped to CSS custom properties (`color`, `choice`, `number`) and/or classes (`class` with `prefix` or `when_set`; `background` for area backgrounds). The site prints the properties on `:root` (`gaia_get_option_style_properties()` via `gaia_get_appearance_css()`) and adds the classes with `gaia_get_option_classes()`; the admin samples apply the same map in JS. Colors are only printed when set (components use `var(--option, default)`); "text on" colors (`contrast_of`) are computed when empty: white or dark text, whichever contrasts more (`gaia_get_contrast_text_color()`, `gaia_is_dark_color()`). New design option: field + map entry (+ CSS using the property or class).
- Area backgrounds (header, footer): mode classes (`site-header--background-*`, `site-footer--background-*`) plus `.scheme-dark` / `.scheme-light` from the brightness of the background (`gaia_get_background_classes()`): primary and custom backgrounds get the scheme that fits, so text, links and separators adapt.
- Option samples (`inc/options-admin.php`, `assets/js/options-admin.js`, `assets/scss/admin.scss` → `assets/css/admin.css`, `assets/scss/admin-sample.scss` → `assets/css/admin-sample.css`, only on the Ajustes de Gaia pages):
  - Visual choices: radio fields whose wrapper class contains `gaia-visual-choices` get a thumbnail per choice centered on the element the option changes (a corner, a button, a menu item, the icons of a collection, the real logos…), drawn with CSS from the current values (`gaia_get_choice_thumbnail()`, `acf/prepare_field`, so the JSON keeps plain labels; "Name: description" labels show the description below). Inline SVG is allowed in those labels (`wp_kses_allowed_html`, "acf" context). New visual selector: radio field + that class + a case in `gaia_get_choice_thumbnail()` + its styles.
  - Samples: message fields with the `gaia-sample-field` wrapper class and a renderer in `gaia_get_sample_markup()` (keys `field_gaia_<page>_sample_<name>`): real site components (header and footer template parts, buttons, notices, contact icons, announcement bar…) inside a declarative shadow root that loads `main.css`, so admin and site styles do not mix. The header sample sticks to the top while editing. Samples carry the markup variants they need (logo types, footer layouts, icon collections) as `[data-sample-variants="<option>"] > [data-variant-value]`, and texts bound to fields as `[data-sample-text="<field key>"]`.
  - `options-admin.js` reads the form (or `savedValues` for options on other pages), sets every mapped custom property on `:root` (PHP prints none of them on these pages, so an emptied field shows its default) and updates classes, variants and texts in the samples. Changes are detected with input events plus a value check every 500ms (color pickers change inputs without native events). Nothing is saved until «Actualizar».
- Read simple options with `gaia_get_option_value($field_name, $default_value)` (default when the value is empty or the field was never saved).
- Options pages are not per language. Translatable option fields (footer tagline and credits, contact address, phone/email labels, announcement, maintenance and 404 texts) are SCF `group` fields with table layout and no sub fields in `acf-json/`; `gaia_register_translatable_option_sub_fields()` (`inc/options-pages.php`) adds one sub field per Polylang language (`es`, `eu`…), shown as side-by-side columns. To make another field translatable, define it as an empty group in the JSON and add its key and sub field settings to `gaia_get_translatable_option_fields()`.
- Read them with `gaia_get_translated_option_text()` / `gaia_get_translated_option_html()` (WYSIWYG) or `gaia_pick_translation()` for repeater rows: current language, then default language, then any non-empty value. Languages: `gaia_get_languages()`, `gaia_get_current_language_slug()`, `gaia_get_default_language_slug()`.
- Icons are inline SVG files printed with `gaia_get_icon_svg('<folder>/<name>')`:
  - `assets/icons/ui/`: every non-brand icon comes from **Lucide** (lucide.dev, ISC). Remove `class`/`width`/`height` from the `<svg>` tag only (keep them on inner shapes like `<rect>`), add `aria-hidden="true" focusable="false"`.
  - `assets/icons/social/`: brand icons from Simple Icons (CC0), printed with `gaia_get_social_icon_svg()`. Falls back to `ui/link.svg`. Network names come from the choices of the `network` select field.
- To add a social network: add a choice (`slug : Name`) to the "Red" select in the Contacto field group and drop `<slug>.svg` from simpleicons.org into `assets/icons/social/` (add `aria-hidden="true" focusable="false" fill="currentColor"`, remove `<title>`).
- Reusable template parts in `template-parts/` (called with `get_template_part($slug, null, ['class' => ...])`): `header/site-header` and `footer/site-footer` (whole header and footer, also used by the admin samples with `is_sample`), `footer/contact`, `social-links`, `language-switcher` (Polylang), `scheme-switcher`, `user-menu` (log in link, or small avatar + user name button (name hidden below `small`) + dropdown: Profile, My area, Administration for editors, Log out in red at the bottom; JS in `assets/js/user-menu.js`, only enqueued for logged-in users). "Log in" / "Log out" use the WordPress core text domain on purpose (core front-end translations). Strings that core only translates in the admin ("Profile", "Dashboard"...) must use the `gaia` domain.
- Header (`inc/header.php`, `template-parts/header/site-header.php`, `assets/scss/layout/_header.scss`, `assets/js/site-header.js`): settings from `gaia_get_header_settings()` (filter `gaia_header_settings`), classes from `gaia_get_header_classes()`.
  - Layouts (`header_layout`, one markup, CSS grid areas): `two-rows` (logo left, languages + tools right, menu centered in a second row with full-width separators), `one-row` (logo, menu, tools), `centered` (logo centered, menu below), `minimal` (logo + hamburger, the menu is always a drawer).
  - Logo (`header_logo`) and optional narrow-screen logo (`header_logo_mobile`) are picked from the Logos library.
  - Options: sticky, hide on scroll (shown again when scrolling up), transparent over a first hero block (`gaia_is_header_transparent()`, filter `gaia_header_transparent`, body class `has-transparent-header`; dark scheme at the top, the background scheme from `data-solid-scheme` once scrolled), language switcher, user menu, search (panel below the header), scheme switcher (only in `auto` color mode: light / dark / browser, `template-parts/scheme-switcher.php`; the choice is stored in localStorage `gaia-color-scheme`, browser = no key; `data-color-preference` on `html`).
  - Colores tab: background (`auto` (scheme header color), `surface`, `primary`, `dark`, `custom` + color), text color (`.has-custom-text-color`), menu accent color, active item indicator (`underline`, `color`, `pill`: `site-header--indicator-*`), bottom and navigation separators (on/off) and their color, shadow (`none`, `scrolled` with `.is-page-scrolled` from JS, `always`). Logo height (`--header-logo-height`).
  - Narrow mode below 768px (`medium` breakpoint, also `WIDE_SCREEN_QUERY` in the JS): hamburger, logo, tools. The panel (`#site-header-panel`) is a drawer (`header_mobile_menu`: `left`, `right` or `fullscreen`) below the header with a backdrop; it holds the language switcher and the menu. Clicking outside or Escape closes it; the page does not scroll while it is open (`.has-open-menu` on `html`). On wide screens (except `minimal`) the panel is `display: contents` so its children go into the header grid.
  - Primary menu two levels deep: `gaia_add_submenu_toggle()` adds a `[data-submenu-toggle]` button to parent items; dropdowns on wide screens (hover, focus or click, one open at a time), accordions in the drawer.
  - The first row has the same height on every screen size (`--header-logo-height`). No social links in the header. JS keeps `--site-header-height` updated.
- Footer (`inc/footer.php`, `template-parts/footer/site-footer.php`, `template-parts/footer/contact.php`, `assets/scss/layout/_footer.scss`): settings from `gaia_get_footer_settings()` (filter `gaia_footer_settings`).
  - Layouts (`footer_layout`): `brand-contact` (brand + social, contact row, bottom bar), `columns` (brand, footer menu, contact), `minimal` (one row: logo, legal menu, social, credits).
  - Colores tab: background (`dark`, `light` (surface), `primary` (text from `--color-on-primary`), `custom` + color), text color, accent color (tagline, labels, icons, link hover), separators (on/off) and their color. Logo from the Logos library and its height (`--footer-logo-height`); contact and social can be hidden.
- Contact icons (Contacto > Iconos, `components/_contact-details.scss`, `_social-links.scss`): contact details icons on/off, collection (`assets/icons/contact/<lucide|tabler|phosphor|heroicons>/<address|phone|email>.svg`, all free: ISC/MIT; `gaia_get_contact_icon_svg()`), shape (none / circle / square) and fill (outline / solid = icon in the background color), color; social links shape, fill, size and color. Classes `contact-details--*` and `social-links--*` from the options map.
  - Menu locations: `primary`, `footer` ("Menú del pie", columns layout) and `legal` ("Menú legal", bottom bar), printed with `gaia_footer_menu()`.
- Per-page settings (`inc/page-settings.php`, side box "Ajustes de la página" on pages and posts, `page_*` fields): header layout, transparent header (auto / yes / no), hide header, footer layout and background, hide footer. Empty values inherit the global options; applied through the header/footer settings filters.
- Extras (`inc/extras.php`, `assets/js/extras.js`):
  - Announcement bar (`template-parts/announcement-bar.php`): translatable text, optional button (link + translatable text, default "More information"), style (primary / dark / highlight, text from the "text on" colors), optional start and end dates, dismissible (close button at the far right; stored in localStorage by text hash; a head script hides it before paint). Printed at the top of the header.
  - Maintenance mode: visitors without `edit_posts` get a 503 page (`template-parts/maintenance.php`, translatable title and text); the Login page, AJAX and REST keep working.
  - Breadcrumbs (`template-parts/breadcrumbs.php`, `gaia_get_breadcrumb_items()`): not on the front page, 404 or pages that start with a hero.
  - Back to top button (after 600px of scroll), configurable 404 title and text (`gaia_get_not_found_texts()`), reusable `template-parts/search-form.php`.
- Login and account area (`inc/account.php`, `page-templates/`, `template-parts/account/`, `assets/scss/layout/_account.scss`):
  - Regular pages using the page templates **Login** (`page-templates/login.php`: log in, `?action=lostpassword`, `?action=resetpassword`) and **Account** (`page-templates/account.php`: "My area" personal dashboard; `?section=profile`: details + password). Found with `gaia_get_page_id_by_template()` and translated with `pll_get_post()`. Use `gaia_get_login_page_url()` / `gaia_get_account_page_url()`.
  - The user menu links to the profile (`gaia_get_user_profile_url()`) and My area (`gaia_get_user_account_url()`) for everyone. Editors and administrators also get "Administration" (wp-admin) and keep the admin bar.
  - `login_url`, `lostpassword_url` and `edit_profile_url` (for users without `edit_posts`) point to these pages; GET requests to `wp-login.php` (login, lost password, reset) redirect to them; the reset password email links to the front-end page. Log out still uses `wp-login.php`.
  - Users without `edit_posts` never see wp-admin (redirected to the account page) nor the admin bar.
  - Profile: avatar, username, first/last name, email (changing username or email requires the current password), plus a password card. Cards side by side from 768px.
  - Username change: WordPress has no API for it; `gaia_change_user_login()` updates the users table (and `user_nicename` if free), clears the cache and renews the auth cookie. Validated by `gaia_validate_new_user_login()`.
  - Local avatars (`inc/avatars.php`): uploaded image (JPG/PNG/WebP/GIF, max 2 MB) stored as an attachment, ID in the `gaia_avatar_id` user meta; replaces Gravatar through `pre_get_avatar_data`; hidden from the media library; deleted when replaced, removed or when the user is deleted. Custom file input (`.avatar-dropzone`: click or drag and drop; the native input is visually hidden because its texts cannot be translated), client-side type/size check and live preview in `assets/js/avatar-field.js` (account page only).
  - Forms post to the same page with `gaia_form` + nonce (`gaia_form_hidden_fields()`), are handled on `template_redirect` and redirect with `?status=` on success (messages in `gaia_get_status_message()`); errors via `gaia_get_form_errors()`.
- SCF field groups are synced as JSON in `acf-json/`: edit fields locally, commit the JSON, sync in other environments.

## Design tokens

- SCSS in `assets/scss/`: `abstracts/` (breakpoints and schemes mixins), `base/` (fonts, reset, tokens, typography, utilities), `components/` (buttons, forms, site logo, social links, contact details, language switcher, user menu, announcement bar, breadcrumbs, back to top), `layout/` (page, header, footer, content, account).
- Buttons (`components/_buttons.scss`): `.button` (primary), `.button--secondary` (`.button--outline` is an alias), `.button--light` (white, for dark images). Colors from Colores > Botones (`--button-primary-color`, `--button-primary-text`, hover color/text, same for secondary; defaults: primary color + automatic text, secondary = secondary color or the text color; hover = 15% darker). Estilo > Botones: solid or outline per type (`--button-primary-outline` / `--button-secondary-outline`, 0 or 1, mixed into the colors), shape, size (padding and font size), weight, uppercase, border width and hover effect (color, lift, grow). `.is-hover` shows the hover state (samples).
- Reusable UI classes: `.button`, `.form` (`__row`, `__field`, `__label`, `__input`, `__hint`, `__checkbox`, `__actions`), `.form-notice--success|--error`.
- Text inputs, textareas and selects share one style site-wide (also without `.form__input`): subtle border, primary color (`--color-primary`) border on focus.
- Current-password fields use `autocomplete="off"` + `data-no-autofill` (`assets/js/no-autofill.js`: read-only until focused, so browsers do not autofill them).
- Grid/flex children that contain inputs need `min-width: 0` (and `minmax(0, …)` columns) so they can shrink; check layouts down to 280px.
- Inputs and buttons use the `--control-*` tokens (line height 1.25, compact padding) so they have the same height: 16px text below 768px (avoids iOS zoom on focus), 14px from 768px.
- `.form__actions`: main button on the right, secondary links on the left (button first in the markup).
- Cards (`.account-card`): surface background, subtle border (`--color-separator`) and soft shadow (`--shadow-card`). Login/lost/reset card titles are centered.
- JS: one file per feature in `assets/js/`, enqueued with `defer` in `inc/assets.php`.
- All design values are CSS custom properties in `base/_tokens.scss`; use them instead of hard-coded values.
- Palette (generic names, Gaia defaults in `base/_tokens.scss`, overridden per site from **Ajustes de Gaia > Colores > Marca**): `--color-primary` (brand), `--color-primary-light` and `--color-primary-dark` (automatic `color-mix()` of the primary unless set), `--color-on-primary` (text on the primary), optional `--color-secondary` / `--color-on-secondary`, `--color-highlight` (decorative) / `--color-on-highlight`, `--color-black`, `--color-white`; states `--color-success-custom` / `--color-error-custom`. Never hard-code a site's brand colors in the code.
- Color mode (`colors_mode`, `gaia_get_color_mode()`): `light`, `dark` or `auto` (browser preference, plus the header scheme switcher; an inline head script sets `data-color-scheme` and `data-color-preference` on `html` before paint). Each scheme has its own background, surface, text, heading, accent and link options (`colors_light_*`, `colors_dark_*`; heading and link fall back to text and accent).
- Schemes (`abstracts/_schemes.scss`): `schemes.light` / `schemes.dark` set the semantic tokens; applied on `:root`, `:root[data-color-scheme='dark']` and the `.scheme-light` / `.scheme-dark` classes, which force a scheme on an area (dark footer, hero, transparent header).
- Use semantic tokens (`--color-text`, `--color-text-muted`, `--color-heading`, `--color-accent`, `--color-link`, `--color-background`, `--color-surface`, `--color-separator`, `--color-error`...) in components, never palette tokens, so they work in both schemes.
- Accent headings with `.has-accent-color`.
- Compact scale: keep sizes and paddings small. Font sizes: `--font-size-smallest` (10px), `--font-size-smaller` (12px), `--font-size-small` (13px), `--font-size-base` (15-16px), `--font-size-large` (16-18px), `--font-size-heading-4` (17-20px) … `--font-size-heading-1` (28-40px). Spacing `--space-3xs` (4px) … `--space-xl` (32px), `--space-2xl`, `--space-3xl` fluid.
- Appearance CSS (`inc/appearance.php`): `gaia_get_appearance_css()` prints `@font-face` for the selected fonts and the `:root` properties of the options map inline after `main.css` and in the block editor (`block_editor_settings_all`); the selected fonts are preloaded.
- Tipografía: heading and body fonts, heading weight, uppercase headings, text size (`--root-font-size` on `html`: scales the whole design), link underline (always / hover / never: `--link-decoration`, `--link-hover-decoration`). Fonts are self-hosted woff2 in `assets/fonts/`, listed in `gaia_get_available_fonts()` (Inter, Montserrat, Open Sans, Lora, Merriweather, Playfair Display). Add a font by dropping its files there and adding an entry. Defaults: Montserrat (headings), Inter (text).
- Estilo: corner radius (`none` / `soft` / `round`), container width (`narrow` / `normal` / `wide`), shadows (`--shadow-strength`: none / soft / strong, used by the scheme shadows), buttons (see Buttons).
- Logos (`inc/logos.php`): library of `main`, `compact` and `icon` logos, each with a light-background and a dark-background version (`logos_<type>_light|dark`), plus the favicon. Header and footer pick a type (`main`, `compact`, `icon`, `text` = site name, `none`). `gaia_get_logo_markup($type)` prints both versions (`.site-logo__image--on-light|--on-dark`) and CSS shows the one for the current scheme; a missing version falls back to the other one, then to `main`, then to the Gaia logos in `assets/images/`.
- Favicon: Logos option, else `assets/images/gaia-icon.png`; the WordPress site icon takes precedence.
- Biokin values (set in its options, not in code): primary `#85c06d` (text on it: automatic, dark), primary light `#c2dcb5`, primary dark `#304a23`, light text `#131811`, highlight `#f1e60e`, dark background `#131811`, fonts Montserrat (headings) + Inter (text), color mode light, footer dark.

## Plugins

- **Secure Custom Fields (SCF)**: free fork of ACF (same API: `get_field`, `have_rows`...). Includes former Pro features.
- **Polylang** (free): languages are Spanish and Basque (at least). Each language is a separate post. Theme strings use `__()` / `esc_html_e()` with the `gaia` text domain.
- Theme translations in `languages/` (`gaia.pot`, `es_ES.po/.mo`, `eu.po/.mo`). Every new front-end string needs its Spanish and Basque translation added to both `.po` files and the `.mo` files recompiled (Poedit or Loco Translate).
- **WP Mail SMTP**: email delivery.

## Tooling

- `npm run dev`: Sass watch + Browsersync in snippet mode (no proxy). Browse the local site as usual: when `wp_get_environment_type()` is `local`, the theme loads the Browsersync client from port 3000 on the front end. CSS is injected, PHP/JS/block.json changes reload the page.
- Local settings in an untracked `.env` (see `.env.example`): `LOCAL_SITE_DOMAIN` (e.g. `biokin.local`), optional `SSL_CERT_PATH` / `SSL_KEY_PATH`.
- Browsersync serves https with the LocalWP site certificate (`~/.config/Local/run/router/nginx/certs/<domain>.{crt,key}`). Falls back to http if not found.
- `npm run build`: compressed CSS without source maps.
- Sass compiles `assets/scss/` to `assets/css/` and each `blocks/**/*.scss` next to its source.
- Compiled CSS is gitignored: run `npm run build` before deploying.
- Dependencies are pinned to exact versions.

## Git workflow

- Claude works on `claude/*` branches. `claude.sh --start <branch>` checks a branch out locally, `claude.sh --finish <branch>` merges it into `main` and deletes it.

## Pending decisions

- Forms: will be hand-made (no plugin), postponed for now.
