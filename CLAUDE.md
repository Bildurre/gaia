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
- **Hero** (`blocks/hero/`): full height minus the header (`calc(100svh - var(--site-header-height))`), background image with focal position and overlay strength, eyebrow, title (H1 by default, switchable to H2), text, up to 2 buttons (link + style), left/center alignment, arrow to the next section. Dark area: redefines the semantic tokens like the footer.
- `functions.php` only loads files from `inc/` (`setup.php`, `assets.php`, `options-pages.php`, `helpers.php`, `account.php`, `avatars.php`, `blocks.php`, `appearance.php`).
- Page layout: `body` is a flex column (`min-height: 100dvh`), `.site-main` grows so the footer sits at the bottom.
- Options pages (registered in `inc/options-pages.php`, fields in `acf-json/`) under "Ajustes del sitio":
  - **Apariencia** (colors, fonts, favicon; see Design tokens).
  - **Contacto** (`contact_phones` repeater: `number`, `label`; `contact_emails` repeater: `address`, `label`; `contact_address` (translatable); `contact_social_networks` repeater: `network`, `url`): reused in the footer and blocks. Use `gaia_get_contact_details()`.
  - **Cabecera** (`header_logo`, light-background version).
  - **Footer** (`footer_logo`, `footer_tagline`, `footer_credits` WYSIWYG).
- Options pages are not per language. Translatable option fields (footer tagline and credits, contact address, phone/email labels) are SCF `group` fields with table layout and no sub fields in `acf-json/`; `gaia_register_translatable_option_sub_fields()` (`inc/options-pages.php`) adds one sub field per Polylang language (`es`, `eu`…), shown as side-by-side columns. To make another field translatable, define it as an empty group in the JSON and add its key and sub field settings to `gaia_get_translatable_option_fields()`.
- Read them with `gaia_get_translated_option_text()` / `gaia_get_translated_option_html()` (WYSIWYG) or `gaia_pick_translation()` for repeater rows: current language, then default language, then any non-empty value. Languages: `gaia_get_languages()`, `gaia_get_current_language_slug()`, `gaia_get_default_language_slug()`.
- Icons are inline SVG files printed with `gaia_get_icon_svg('<folder>/<name>')`:
  - `assets/icons/ui/`: every non-brand icon comes from **Lucide** (lucide.dev, ISC). Remove `class`/`width`/`height` from the `<svg>` tag only (keep them on inner shapes like `<rect>`), add `aria-hidden="true" focusable="false"`.
  - `assets/icons/social/`: brand icons from Simple Icons (CC0), printed with `gaia_get_social_icon_svg()`. Falls back to `ui/link.svg`. Network names come from the choices of the `network` select field.
- To add a social network: add a choice (`slug : Name`) to the "Red" select in the Contacto field group and drop `<slug>.svg` from simpleicons.org into `assets/icons/social/` (add `aria-hidden="true" focusable="false" fill="currentColor"`, remove `<title>`).
- Reusable template parts in `template-parts/` (called with `get_template_part($slug, null, ['class' => ...])`): `social-links` (footer), `language-switcher` (Polylang), `user-menu` (log in link, or small avatar + user name button (name hidden below `small`) + dropdown: Profile, My area, Administration for editors, Log out in red at the bottom; JS in `assets/js/user-menu.js`, only enqueued for logged-in users). "Log in" / "Log out" use the WordPress core text domain on purpose (core front-end translations). Strings that core only translates in the admin ("Profile", "Dashboard"...) must use the `gaia` domain.
- Header (`header.php`, `assets/scss/layout/_header.scss`, `assets/js/site-header.js`):
  - No social links in the header.
  - Narrow mode below 768px (`medium` breakpoint, also `WIDE_SCREEN_QUERY` in the JS).
  - Wide (>= `medium`): logo left; language switcher + user menu on the right; primary menu centered in a second row, with full-width separators above and below it.
  - The first row has the same height on every screen size (logo height `--header-logo-height`). Almost-white background `--color-header-background`, separators `--color-separator`.
  - Narrow: hamburger (ink CSS bars animated into an X), logo, user menu. The panel (`#site-header-panel`) is a drawer that slides in from the left below the header (max width 20rem) with a backdrop; it holds the language switcher and the menu. Clicking outside or Escape closes it; the page does not scroll while it is open (`.has-open-menu` on `html`), the drawer scrolls if needed. On wide screens the panel is `display: contents` so its children go into the header grid.
  - Sticky and always visible (no hide on scroll). JS keeps `--site-header-height` updated for the mobile panel. Escape closes the menu.
  - Primary menu is one level deep for now (`depth => 1`).
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

- SCSS in `assets/scss/`: `abstracts/` (breakpoints mixin), `base/` (fonts, reset, tokens, typography, utilities), `components/` (buttons, forms, social links, language switcher, user menu), `layout/` (page, header, footer, account).
- Buttons: `.button` (text color background) and variants `.button--accent` (primary color with ink text), `.button--light` (white), `.button--outline` (transparent, current text color border).
- Reusable UI classes: `.button`, `.form` (`__row`, `__field`, `__label`, `__input`, `__hint`, `__checkbox`, `__actions`), `.form-notice--success|--error`.
- Text inputs, textareas and selects share one style site-wide (also without `.form__input`): subtle border, primary color (`--color-primary`) border on focus.
- Current-password fields use `autocomplete="off"` + `data-no-autofill` (`assets/js/no-autofill.js`: read-only until focused, so browsers do not autofill them).
- Grid/flex children that contain inputs need `min-width: 0` (and `minmax(0, …)` columns) so they can shrink; check layouts down to 280px.
- Inputs and buttons use the `--control-*` tokens (line height 1.25, compact padding) so they have the same height: 16px text below 768px (avoids iOS zoom on focus), 14px from 768px.
- `.form__actions`: main button on the right, secondary links on the left (button first in the markup).
- Cards (`.account-card`): surface background, subtle border (`--color-separator`) and soft shadow (`--shadow-card`). Login/lost/reset card titles are centered.
- JS: one file per feature in `assets/js/`, enqueued with `defer` in `inc/assets.php`.
- All design values are CSS custom properties in `base/_tokens.scss`; use them instead of hard-coded values.
- Palette (generic names, Gaia defaults in `base/_tokens.scss`, overridden per site from **Ajustes del sitio > Apariencia**): `--color-primary` (brand), `--color-primary-light` and `--color-primary-dark` (automatic `color-mix()` of the primary unless set), `--color-ink` (almost black reading text), `--color-highlight` (decorative only), `--color-black`, `--color-gray-100`, `--color-white`. Never hard-code a site's brand colors in the code.
- Biokin values (set in its options, not in code): primary `#85c06d`, primary light `#c2dcb5`, primary dark `#304a23`, text `#131811`, highlight `#f1e60e`, fonts Montserrat (headings) + Inter (text).
- There is no dark theme. Reading text and headings are ink; the accent is the dark primary (accent headings with `.has-accent-color`, captions, links, details).
- Use semantic tokens (`--color-text`, `--color-heading`, `--color-accent`, `--color-link`, `--color-background`, `--color-surface`, `--color-error`...) in components, not palette tokens.
- Dark areas (footer, hero) set their colors from the palette and redefine the semantic tokens locally.
- Footer: `.site-footer` sets its colors from the palette (background ink, text white, accent/links/labels primary, contact icons and separators primary light, credits primary dark) and redefines the semantic tokens its children use.
- Compact scale: keep sizes and paddings small. Font sizes: `--font-size-smallest` (10px), `--font-size-smaller` (12px), `--font-size-small` (13px), `--font-size-base` (15-16px), `--font-size-large` (16-18px), `--font-size-heading-4` (17-20px) … `--font-size-heading-1` (28-40px). Spacing `--space-3xs` (4px) … `--space-xl` (32px), `--space-2xl`, `--space-3xl` fluid.
- Footer layout: row 1 brand (logo + tagline) left and social networks right (below the brand on narrow screens); row 2 address, phones and emails with Lucide icons (one group per row on narrow screens); phone/email labels below the value, aligned right; credits centered at the bottom, with a container-width separator like the one above the contact row. Everything wraps.
- Appearance (`inc/appearance.php`, options page **Apariencia**, `acf-json/group_gaia_appearance.json`): colors, heading and body fonts, favicon. `gaia_get_appearance_css()` prints `@font-face` for the selected fonts and the `:root` overrides inline after `main.css` and in the block editor (`block_editor_settings_all`); the selected fonts are preloaded.
- Fonts: self-hosted woff2 in `assets/fonts/`, listed in `gaia_get_available_fonts()` (Inter, Montserrat, Open Sans, Lora, Merriweather, Playfair Display). Add a font by dropping its files there and adding an entry. Defaults: Montserrat (headings), Inter (text).
- Favicon: Apariencia option, else `assets/images/gaia-icon.png`; the WordPress site icon takes precedence.
- Logos: header/footer logo options, else the Gaia logo (`assets/images/gaia-logo.png`, `gaia-logo-light.png` with white text for the footer; an `.svg` takes precedence), else the site name (`gaia_get_logo_markup($option, $variant)`).

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
