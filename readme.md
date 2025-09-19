# Apollo Ecosystem Plugin (`eco`)

📦 **Descrição**  
Ecosystem é o **núcleo de registro** de *templates, snippets de código, funções e ACF fields* do Apollo::rio.  
Ele serve como **biblioteca interna** para manter toda a lógica de **roles, post types e permissões** bem organizada, permitindo replicar facilmente a instalação em outros WordPress.  

---

## 🚀 Objetivos
- Centralizar toda a **definição inicial do Apollo** (user roles, post types, taxonomias).  
- Manter um **repositório de código e design templates** em um post type próprio (`eco`).  
- Gerenciar, via admin, os registros que deverão ser exportados/sincronizados para novas instalações.  
- Criar **tela para gerenciar roles/capabilities** no admin → ao criar uma role no ECO, ela já é salva no `install.php` para ser replicada em qualquer instalação futura.  
- Ser a **primeira fundação** para depois conectar os demais plugins Apollo (EXP, EVE, GES, etc.).

---

## 🗂 Estrutura de Pastas e Arquivos

/eco
│
├── eco.php // Arquivo principal do plugin (já pronto no seu código)
│ ├─ registra post_type 'eco' (biblioteca interna, não pública)
│ ├─ registra taxonomias ('fit_cultural', 'item_type')
│ ├─ cria admin menu + abas (Dashboard / Codes / Designs)
│ ├─ meta boxes extras (URL, Tipo de Conteúdo, Rating)
│ ├─ substitui editor por PHP Generator
│ └─ inclui CSS/JS custom para admin
│
├── /admin
│ ├── class-dashboard.php // futuro: lógica separada da página principal
│ ├── class-user-roles.php // futura tela: criar user roles + capabilities
│ ├── class-capabilities.php // futura tela: match de roles com CPTs/tax/metas
│
├── /assets
│ ├── css/
│ │ └── admin.css // estilos adicionais do painel
│ └── js/
│ └── admin.js // interações extra além do Sortable
│
├── /install
│ └── install.php // guarda definições de roles e ACF registradas para replicar
│
├── /acf
│ └── register.php // (via Extended ACF) define fields/blocks em OOP
│
└── readme.md // este arquivo de documentação


---

## 📑 Fluxo das Páginas no WP-Admin

- **Página Principal (Dashboard)**  
  - Cards de estatísticas (Total de Itens, Meus Itens, To-Do, Focus).  
  - Tabs: *Ecosystem, Codes, Designs*.  
  - Cada tab lista posts do CPT `eco` filtrados por meta `_apollo_content_type`.

- **Página 2 (Register User Role)**  
  - Form para criar nova role + definir capabilities.  
  - Ao salvar:
    1. Registra no WP (via `add_role`).  
    2. Salva no `install/install.php` para replicar em futuras instalações.

- **Página 3 (Register ACF Field)**  
  - Interface para registrar ACF em OOP (via Extended ACF).  
  - Conditional Logic (A → B → C) para definir onde aplicar o field.  
  - Permissões: tabela com todas roles → Full / Read / Hidden.  
  - Ao salvar:
    1. Registra em `acf/register.php`.  
    2. Salva também no `install/install.php`.

- **Página Extra (User Capabilities Mapping)**  
  - Lista todas roles já criadas.  
  - Lista todos CPTs, taxonomias e metakeys.  
  - Permite vincular capabilities a roles.  
  - Salvo no plugin → replicado em `install/install.php`.

---

## 🔧 Diferenciais Técnicos

- **Custom Post Type `eco`:**  
  - Clone da config de `post` mas privado (só wp-admin).  
  - Serve como repositório da biblioteca.

- **Taxonomias:**  
  - `fit_cultural` → Plugins/áreas culturais do Apollo.  
  - `item_type` → Tipos de item (Code, Design, etc.).

- **Meta Boxes:**  
  - Origem URL, Content Type (code/design), Rating.  

- **PHP Generator:**  
  - Substitui editor do CPT por interface para gerar snippet PHP a partir de HTML/CSS/JS inserido.  
  - Salva diretamente como conteúdo do item.

- **Roles & Capabilities:**  
  - Ao criar role → `add_role` no WP + salva no `install.php`.  
  - Permissões por field definidas com `acf/prepare_field`.  

- **Portable / Sync:**  
  - Tudo registrado via código (roles + ACF) → versionado.  
  - Em nova instalação, basta ativar plugin → roles e ACF replicados.

---

## 🔗 Refs Técnicas

- Extended ACF (OOP Fields): [github.com/vinkla/extended-acf](https://github.com/vinkla/extended-acf)  
- Role Management API: [`add_role()`, `remove_role()`, `get_role()`]  
- ACF Filters: `acf/init`, `acf/prepare_field`  
- WP Roles API: `$wp_roles->get_names()`  

---

✍️ **Autor:** Apollo::rio  
📅 **Versão:** 1.0  
🛠 **Status:** MVP em desenvolvimento – pronto para receber módulos de Roles & ACF Registration.




