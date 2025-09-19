README.md — Apollo Ecosystem (eco)
O Ecosystem (eco) é o plugin-mãe do Apollo.
Ele é a biblioteca privada de código, templates, campos ACF e roles do sistema.

Tudo que for criado no eco é salvo como registro do CPT eco, e exportado automaticamente para ser replicado em qualquer instalação WordPress (via install.php).
Assim o eco garante consistência entre ambientes e evita retrabalho.

Funcionalidades Principais
CPT eco: armazena todos os itens (roles, ACF, templates, snippets).
Taxonomias:
fit_cultural: plugin relacionado (EVE, GES, EXP…).
item_type: classifica (code, design, acf, role, template).
Admin Tabs:
Ecosystem Dashboard → visão geral + registros.
ACF Types → registrar e gerenciar ACFs.
Templates → biblioteca de layouts e códigos visuais.
Roles → criar/editar roles e capabilities.
Capabilities Matrix → ligar roles a CPTs, taxonomias e ACFs.
Export/Install:
Gera install.php que recria roles, ACFs e templates em outra instalação.

📂 Estrutura de Pastas
/eco
│
├── eco.php              # Bootstrap do plugin
├── install.php          # Exporta e recria roles + acf + templates
│
├── /cpt
│   └── eco.php          # Registro do CPT eco
│
├── /admin
│   ├── menu.php         # Cria menu Apollo e abas
│   ├── roles-page.php   # Form de criar/editar roles e caps
│   ├── acf-page.php     # Form de registrar grupos/campos ACF
│   ├── template-page.php# Biblioteca de templates com preview
│   └── caps-page.php    # Matriz de capabilities
│
├── /acf
│   ├── register.php     # Usa acf_add_local_field_group()
│   ├── export.php       # Exporta config em JSON/PHP
│   └── sync.php         # Importa configs salvas
│
├── /roles
│   └── register.php     # Criação/validação de roles
│
├── /templates
│   └── library.php      # Salvar/carregar templates .php
│
└── /assets
    ├── admin.css        # Estilos das páginas
    └── admin.js         # Tabs, sortable, lightbox

Fluxo de Trabalho
Criar novo item no Eco
Preenche formulário (título, resumo, tipo, plugins usados, código, URL origem).
Marca categoria: acf item, template, role, code.
Salva no CPT eco.

Exportação automática
Ao salvar, eco gera definição em JSON/PHP.
install.php é atualizado.

Sincronização
Se instalar o eco em outro WP, install.php recria:
Roles + capabilities.
Grupos ACF + campos.
Templates PHP (salvos em /templates).
Permissões da matriz.

Segurança
Todos os registros ficam privados no wp-admin.
Sem exposição pública do CPT eco.
install.php apenas replica roles/ACFs/templates — nunca dados sensíveis.

Roadmap Produção
Fase 4 — Templates
Criar biblioteca de templates (designs, grids, sections).
Preview em lightbox (já existe).
Exportar .php para /templates.

🔒 Fase 5 — Capabilities Matrix
Criar tabela dinâmica roles × CPT/tax/meta.
Salvar no eco.
Hook user_has_cap aplica restrições.

🛠️ Fase 6 — Install/Sync
Finalizar install.php para recriar tudo.
Testar em instalação limpa.
Garantir performance (get_option + cache leve).

















