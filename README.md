# FypeiH CRUD Manager

Plugin WordPress para gerir tabelas e registos personalizados na base de dados, com integração com o Elementor.

---

## ✨ Funcionalidades

- Criar, editar e apagar tabelas personalizadas na base de dados WordPress
- Interface de administração própria no painel WP (sem dependência do Gutenberg)
- Renderização de campos dinâmicos no admin (`field-renderer`)
- Integração com o **Elementor Dynamic Data** — os dados das tabelas ficam disponíveis como fontes dinâmicas nos widgets do Elementor
- Limpeza automática de transients ao desinstalar o plugin

---

## ⚙️ Requisitos

| Requisito        | Versão mínima |
|------------------|---------------|
| WordPress        | 6.8           |
| PHP              | 7.4           |
| Elementor        | (recomendado) |

---

## 📦 Instalação

1. Faz download ou clone do repositório para a pasta de plugins do WordPress:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/FypeiH/fypeih-crud.git
   ```
2. Acede ao painel WordPress → **Plugins** → Ativa o **Fypeih CRUD Manager**.
3. Na ativação, a tabela base é criada automaticamente na base de dados (`fyp_crud_create_table`).
4. O plugin aparece no menu do admin em **WordPress Admin → CRUD Manager**.

---

## 📁 Estrutura do Projeto

```
fypeih-crud/
├── fypeih.php                          # Bootstrap, constantes e activation hook
├── uninstall.php                       # Limpeza de transients ao desinstalar
├── includes/
│   ├── database/
│   │   └── database.php               # Criação e gestão de tabelas
│   ├── admin/
│   │   ├── admin-menu.php             # Registo do menu no WP Admin
│   │   ├── admin-page.php             # Lógica, assets e render da página
│   │   └── field-renderer.php         # Renderização de campos dinâmicos
│   └── elementor/
│       └── elementor.php              # Integração com Elementor
├── templates/
│   └── admin-page.php                 # HTML da interface de administração
└── assets/
    ├── admin.css                       # Estilos do painel admin
    └── admin.js                        # Scripts do painel admin
```

---

## 🧠 Arquitetura

| Ficheiro                              | Responsabilidade                              |
|---------------------------------------|-----------------------------------------------|
| `fypeih.php`                          | Bootstrap, constantes e activation hook       |
| `includes/database/database.php`      | CRUD de tabelas na base de dados              |
| `includes/admin/admin-menu.php`       | Registo do menu no WP Admin                   |
| `includes/admin/admin-page.php`       | Lógica de negócio, assets e render            |
| `includes/admin/field-renderer.php`   | Renderização de campos dinâmicos              |
| `includes/elementor/elementor.php`    | Integração com Elementor Dynamic Data         |
| `templates/admin-page.php`            | HTML da interface (sem lógica pesada)         |
| `assets/`                             | CSS e JS carregados apenas na página do plugin|

---

## 🔌 Integração com o Elementor

O plugin expõe os dados das tabelas personalizadas como **fontes de Dynamic Data** no Elementor. Isto permite usar os registos das tabelas diretamente em widgets de texto, imagem, listas e outros, sem precisar de código extra na página.

A integração é inicializada via `includes/elementor/elementor.php` e está disponível assim que o plugin estiver ativo e o Elementor estiver instalado.

---

## 🏷️ Convenções de Naming

### Prefixos

| Tipo        | Prefixo           |
|-------------|-------------------|
| Funções PHP | `fyp_`            |
| Constantes  | `FYPEIH_CRUD_*`   |
| CSS classes | `fyp-`            |

### Exemplos

**PHP:**
```php
fyp_crud_create_table()
fyp_register_admin_menu()
fyp_render_admin_page()
```

**Constantes:**
```php
FYPEIH_CRUD_VERSION   // '0.2.0'
FYPEIH_CRUD_PATH      // plugin_dir_path( __FILE__ )
FYPEIH_CRUD_URL       // plugin_dir_url( __FILE__ )
FYPEIH_CRUD_SLUG      // 'fypeih-crud-manager'
```

**CSS:**
```css
.fyp-admin  { ... }
.fyp-card   { ... }
.fyp-button { ... }
```

---

## 🔒 Segurança

Todos os formulários de admin seguem as boas práticas do WordPress:

- `sanitize_text_field()` em inputs de texto
- `esc_html()` / `esc_attr()` no output
- `wp_nonce_field()` + `check_admin_referer()` em formulários
- Assets carregados apenas na página do plugin (sem impacto global):
  ```php
  if ( 'toplevel_page_' . FYPEIH_CRUD_SLUG !== $hook ) {
      return;
  }
  ```

---

## 🗑️ Desinstalação

Ao desinstalar o plugin via o painel WordPress, o `uninstall.php` remove automaticamente os transients criados pelo plugin:

```php
DELETE FROM wp_options
WHERE option_name LIKE '_transient_fypeih_psi_%'
OR option_name LIKE '_transient_timeout_fypeih_psi_%'
```

As tabelas personalizadas criadas na base de dados **não são removidas** na desinstalação (para preservar os dados).

---

## 📄 Licença

Distribuído sob a licença **GPL-2.0-or-later**.
Consulta [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html) para mais informações.

---

*Plugin desenvolvido por Filipe Bravo.*