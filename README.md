# Gigantic Plugin Template

Template base para desenvolvimento de plugins WordPress internos da
**Gigantic**.

------------------------------------------------------------------------

# 🚀 Objetivo

Criar plugins WordPress: - com interface própria no admin - sem
dependência de Gutenberg - com estrutura reutilizável e escalável

------------------------------------------------------------------------

# 📁 Estrutura do projeto

    plugin/
    ├── plugin.php
    ├── includes/
    │   ├── admin-menu.php
    │   └── admin-page.php
    ├── templates/
    │   └── admin-page.php
    └── assets/
        └── admin.css

------------------------------------------------------------------------

# 🧠 Arquitetura

| Ficheiro                  | Responsabilidade         |
| ------------------------- | ------------------------ |
| `plugin.php`              | Bootstrap + constantes   |
| `includes/admin-menu.php` | Registo do menu no WP    |
| `includes/admin-page.php` | Lógica + assets + render |
| `templates/*.php`         | HTML (sem lógica pesada) |
| `assets/`                 | CSS/JS do admin          |

------------------------------------------------------------------------

# 🏷️ Convenções de naming

## Prefixos

| Tipo        | Prefixo             |
| ----------- | ------------------- |
| Funções PHP | `gig_`              |
| Constantes  | `GIGANTIC_PLUGIN_*` |
| CSS         | `gig-`              |


------------------------------------------------------------------------

## Exemplos

### PHP

    gig_register_admin_menu()
    gig_render_admin_page()
    gig_admin_assets()

### Constantes

    GIGANTIC_PLUGIN_VERSION
    GIGANTIC_PLUGIN_PATH
    GIGANTIC_PLUGIN_URL
    GIGANTIC_PLUGIN_SLUG

### CSS

    .gig-admin
    .gig-card
    .gig-button

------------------------------------------------------------------------

# ⚙️ Constantes base

    define( 'GIGANTIC_PLUGIN_VERSION', '0.1.0' );
    define( 'GIGANTIC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
    define( 'GIGANTIC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    define( 'GIGANTIC_PLUGIN_SLUG', 'gigantic-plugin-template' );

------------------------------------------------------------------------

# 🧩 Como criar um novo plugin

## 1. Copiar template

Duplica a pasta do plugin.

## 2. Atualizar metadados

No `plugin.php`:

-   Plugin Name
-   Description
-   Version
-   Text Domain
-   GIGANTIC_PLUGIN_SLUG

## 3. (Opcional) Alterar prefixo

Exemplo: - `gig_` → `seo_` - `gig-` → `seo-`

⚠️ Mantém consistência em todo o código

------------------------------------------------------------------------

# 🧭 Menu no Admin

Registado em:

    add_menu_page(...)

A página aparece em:

    WordPress Admin → Plugin Template

------------------------------------------------------------------------

# 🎨 Templates

-   Local: `templates/`
-   Apenas HTML + echo

❌ Não colocar lógica complexa

------------------------------------------------------------------------

# 🎯 Wrapper obrigatório

    <div class="wrap gig-admin">

------------------------------------------------------------------------

# 🎨 CSS

## Regras

-   Sempre scoped:

```
.gig-admin { ... }
```

-   Nunca usar:

```
.card ❌
.button ❌
```

-   Usar:

```
.gig-card ✔️
.gig-button ✔️
```

------------------------------------------------------------------------

# 📦 Assets

Carregados apenas na página do plugin:

    if ( 'toplevel_page_' . GIGANTIC_PLUGIN_SLUG !== $hook ) {
        return;
    }

------------------------------------------------------------------------

# 🔒 Segurança

Ao adicionar formulários:

-   sanitize_text_field()
-   esc_html()
-   wp_nonce_field()
-   check_admin_referer()

------------------------------------------------------------------------

# 🚀 Extensões futuras

-   settings pages
-   ferramentas SEO
-   dashboards
-   integrações API
-   import/export

------------------------------------------------------------------------

# 🧠 Boas práticas

-   separar lógica e apresentação
-   manter funções pequenas
-   usar prefixos sempre
-   manter consistência

------------------------------------------------------------------------

# ⚠️ Erros comuns

-   misturar prefixes
-   CSS global
-   lógica em templates
-   esquecer ABSPATH
-   carregar assets globalmente

------------------------------------------------------------------------

# 🏁

Template interno da Gigantic.
