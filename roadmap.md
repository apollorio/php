🔴 CRÍTICO - Bloqueadores Imediatos
php// 1. JWT EXISTENTE EM PRODUÇÃO (libera.apollo.*)
❌ Criar novo sistema de auth
❌ Modificar endpoints JWT atuais
❌ Alterar flow de autenticação
⚠️  RISCO: Quebrar produção, perder sessões ativas

// 2. ACF ECOSYSTEM CHAOS (2024)
⚠️  ACF vs ACF PRO vs Secure Custom Fields
⚠️  WordPress fork do ACF (outubro 2024)
⚠️  Incompatibilidade de versões
⚠️  Campos duplicados se usar múltiplas versões

// 3. CUSTOM POST TYPES - 20 CHAR LIMIT
❌ Nome longo: "apollo_rio_evento_principal" (27 chars)
✅ Correto: "ario_evento" (11 chars)
⚠️  RISCO: Truncamento silencioso, permalinks quebrados

// 4. NAMESPACE COLLISIONS
❌ Função global: event_get_data()
✅ Correto: Apollo\Base\Events\get_data()
⚠️  RISCO: Conflito com outros plugins de eventos
🟡 MÉDIO - Problemas de Performance/Integração
php// 5. DATABASE PERFORMANCE
⚠️  CPT + meta_query para >1000 eventos = 30-45s load
⚠️  Autoload options >800KB = degradação
⚠️  N+1 queries em relacionamentos ACF

// 6. REST API CONFLICTS
⚠️  Namespace collision: /wp-json/apollo/v1/events
⚠️  Cache headers interferindo com JWT
⚠️  Rate limiting entre plugins

// 7. COMMENT ENGINE CUSTOM
⚠️  Hook priorities com outros plugins de comentários
⚠️  Shortcode conflicts ([eva] pode colidir)
⚠️  Performance com tópicos múltiplos
🟢 BAIXO - Gerenciáveis
php// 8. ACF FIELD GROUP KEYS
⚠️  Chaves duplicadas entre componentes
✅ SOLUÇÃO: ACF Composer gera keys únicos

// 9. TRANSLATION/WPML
⚠️  String domain conflicts
⚠️  ACF fields não sincronizam entre idiomas

// 10. TEMA vs PLUGIN SEPARATION
⚠️  Flynt theme-based vs plugin independence
⚠️  Asset pipeline conflicts (Vite do Flynt)

🗺️ ROADMAP ESTRATÉGICO - SPRINTS DETALHADAS
SPRINT 0: PREPARAÇÃO (2-3 dias)
bash✅ DELIVERABLES:
1. Ambiente local configurado (Local by Flywheel)
2. WordPress 6.4.3+ instalado
3. Git repository estruturado
4. Documentação baseline Apollo

📊 ACCEPTANCE CRITERIA:
- [ ] WP instalado e acessível
- [ ] Git com .gitignore correto
- [ ] README.md com contexto Apollo
- [ ] Backup da instalação atual (se houver)
AÇÕES:
bash# 1. Setup local
brew install --cask local # Mac
# ou Docker: lando init --recipe wordpress

# 2. Clone boilerplate
cd wp-content/plugins
git clone https://github.com/WPBoilerplate/wordpress-plugin-boilerplate apollo-rio-core

# 3. Estrutura inicial
apollo-rio-core/
├── .env.example
├── composer.json
└── README-APOLLO.md

SPRINT 1: FOUNDATION - JWT Integration (3-5 dias)
php✅ DELIVERABLES:
1. Plugin base ativo (sem funcionalidades)
2. JWT healthcheck endpoint APENAS
3. ACF PRO instalado via Composer
4. Namespace Apollo\Rio\ funcionando

📊 ACCEPTANCE CRITERIA:
- [ ] Plugin ativa sem erros
- [ ] /wp-json/apollo/v1/health retorna 200
- [ ] JWT header detectado (não validado ainda)
- [ ] ACF PRO fields disponíveis no admin
- [ ] Zero conflitos com JWT produção
CÓDIGO MÍNIMO:
php// apollo-rio-core.php (main file)
<?php
/**
 * Plugin Name: Apollo Rio Core
 * Version: 0.1.0
 * Requires PHP: 8.0
 */

namespace Apollo\Rio;

defined('ABSPATH') || exit;

// Autoload
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap
add_action('plugins_loaded', function() {
    Core\Bootstrap::init();
});
php// includes/Core/Bootstrap.php
<?php
namespace Apollo\Rio\Core;

class Bootstrap {
    public static function init() {
        // APENAS healthcheck - ZERO auth logic
        add_action('rest_api_init', [self::class, 'register_health']);
    }
    
    public static function register_health() {
        register_rest_route('apollo/v1', '/health', [
            'methods' => 'GET',
            'callback' => function() {
                return [
                    'status' => 'ok',
                    'version' => '0.1.0',
                    'jwt_detected' => isset($_SERVER['HTTP_AUTHORIZATION'])
                ];
            },
            'permission_callback' => '__return_true'
        ]);
    }
}
COMPOSER.JSON:
json{
    "name": "apollo-rio/core",
    "require": {
        "php": ">=8.0",
        "composer/installers": "^2.0",
        "philippbaschke/acf-pro-installer": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "Apollo\\Rio\\": "includes/"
        }
    },
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "advanced-custom-fields/advanced-custom-fields-pro",
                "version": "6.4.3",
                "type": "wordpress-plugin",
                "dist": {
                    "type": "zip",
                    "url": "https://connect.advancedcustomfields.com/index.php?p=pro&a=download"
                },
                "require": {
                    "philippbaschke/acf-pro-installer": "^1.0"
                }
            }
        }
    ]
}
.env:
bashACF_PRO_KEY=seu-key-aqui

SPRINT 2: COMMENT ENGINE MVP (5-7 dias)
php✅ DELIVERABLES:
1. Tópicos de comentários (@briefing, @crew)
2. Shortcode [eva] funcionando
3. Permissões por role nativo WP
4. Query otimizada (1 query por tópico)

📊 ACCEPTANCE CRITERIA:
- [ ] Admin pode criar tópico via código
- [ ] [eva="12" comment="@briefing"] renderiza
- [ ] Apenas editor+ pode postar
- [ ] <100ms query time
- [ ] Zero estilos inline (CSS separado)
ESTRUTURA:
phpincludes/
├── Base/
│   ├── Comments/
│   │   ├── Engine.php      // Core logic
│   │   ├── Shortcodes.php  // [eva] [form]
│   │   └── Query.php       // Otimizado
│   └── ACF/
│       └── Health.php      // Dashboard
EXEMPLO - ENGINE MÍNIMO:
php// includes/Base/Comments/Engine.php
<?php
namespace Apollo\Rio\Base\Comments;

class Engine {
    private static $active_topics = [];
    
    public static function init() {
        add_action('init', [self::class, 'register_topics']);
        add_filter('comments_clauses', [self::class, 'filter_by_topic'], 10, 2);
    }
    
    public static function register_topics() {
        // Define tópicos Apollo
        self::$active_topics = apply_filters('apollo/comment_topics', [
            '@briefing' => [
                'label' => 'Briefing',
                'roles' => ['editor', 'administrator']
            ],
            '@crew' => [
                'label' => 'Crew Notes',
                'roles' => ['author', 'editor', 'administrator']
            ]
        ]);
    }
    
    public static function can_user_post($topic, $user_id = null) {
        $user_id = $user_id ?: get_current_user_id();
        $user = get_userdata($user_id);
        
        if (!$user) return false;
        
        $allowed_roles = self::$active_topics[$topic]['roles'] ?? [];
        return !empty(array_intersect($allowed_roles, $user->roles));
    }
}

SPRINT 3: ACF HEALTH MONITOR (3-4 dias)
php✅ DELIVERABLES:
1. Dashboard com status de fields
2. Cache de snapshot (transient)
3. Indicators: ⚫ Grey | 🔴 Red | 🟢 Green

📊 ACCEPTANCE CRITERIA:
- [ ] Admin vê tabela de fields
- [ ] Status atualiza a cada 5min (cache)
- [ ] Force refresh button funciona
- [ ] Zero queries na listagem (cached)

SPRINT 4: EVENTO CPT + ACF (5-7 dias)
php✅ DELIVERABLES:
1. CPT: ario_evento (eventos)
2. CPT: ario_venue (locais)
3. CPT: ario_dj (djs)
4. ACF relationships funcionando
5. REST endpoints básicos

📊 ACCEPTANCE CRITERIA:
- [ ] Criar evento via admin
- [ ] Relacionar venue + DJ
- [ ] GET /wp-json/apollo/v1/events retorna JSON
- [ ] Permalink: /eventos/nome-do-evento
- [ ] Meta box com ACF aparece
CPT REGISTRATION:
php// includes/Evento/PostTypes.php
<?php
namespace Apollo\Rio\Evento;

class PostTypes {
    public static function init() {
        add_action('init', [self::class, 'register_evento']);
        add_action('init', [self::class, 'register_venue']);
        add_action('init', [self::class, 'register_dj']);
    }
    
    public static function register_evento() {
        register_post_type('ario_evento', [
            'labels' => [
                'name' => 'Eventos',
                'singular_name' => 'Evento'
            ],
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'eventos'],
            'show_in_rest' => true, // Importante!
            'supports' => ['title', 'editor', 'thumbnail'],
            'menu_icon' => 'dashicons-calendar-alt'
        ]);
    }
    
    // Similar para venue e dj...
}

SPRINT 5: CALENDAR API (4-5 dias)
php✅ DELIVERABLES:
1. GET /events/calendar?month=09&year=2025
2. JSON com eventos do mês
3. Cache de 1h para calendário
4. Suporte a filtros (venue, tipo)

📊 ACCEPTANCE CRITERIA:
- [ ] API retorna eventos corretos
- [ ] Response time <200ms (cached)
- [ ] Paginação funciona
- [ ] Frontend pode consumir

SPRINT 6: FRONTEND BÁSICO (3-4 dias)
php✅ DELIVERABLES:
1. Template: single-ario_evento.php
2. Template: archive-ario_evento.php  
3. Cards de evento responsivos
4. Calendário mensal (HTML simples)

📊 ACCEPTANCE CRITERIA:
- [ ] Evento individual exibe dados
- [ ] Lista de eventos funciona
- [ ] Mobile responsive
- [ ] SEO tags corretos

🤖 GUIA CLAUDE - QUANDO USAR QUAL?
CLAUDE WEB (Este aqui) - $20/mês Pro
✅ USE PARA:

Planejamento e estratégia (como agora)
Code review de arquivos pequenos
Debugging de erros específicos
Perguntas conceituais
Documentação e README

❌ NÃO USE PARA:

Editar múltiplos arquivos
Refactoring grande
Setup inicial complexo

CUSTO/BENEFÍCIO: ⭐⭐⭐⭐⭐ (melhor para Apollo)

CLAUDE DESKTOP - $20/mês (mesmo Pro)
✅ USE PARA:

Manipular arquivos locais
Ver estrutura de pastas
Editar 2-3 arquivos por vez
Integração com Local by Flywheel

❌ NÃO USE PARA:

Desenvolvimento full workflow
Múltiplas dependências
Build systems complexos

CUSTO/BENEFÍCIO: ⭐⭐⭐ (útil mas não essencial)
DIFERENÇA DO WEB:

Desktop = acessa seus arquivos via MCP
Web = você cola código manualmente
MESMO CHAT? NÃO! São sessões separadas


CLAUDE CODE (CLI) - $100-200/mês
✅ USE PARA:

Criar plugin completo do zero
Refactoring massivo
Múltiplos arquivos simultâneos
Autonomous workflow

❌ NÃO USE PARA:

Projeto pequeno/voluntário
Aprendizado inicial
Budget limitado

CUSTO/BENEFÍCIO: ⭐⭐ (overkill para Apollo)

🎯 RECOMENDAÇÃO FINAL APOLLO::RIO
SETUP IDEAL (Econômico):
bash1. CLAUDE WEB PRO ($20/mês) ← VOCÊ JÁ TEM
   → Planejamento + code review + debugging
   
2. VS CODE (FREE)
   → Editor principal
   → Extensions: PHP Intelephense, ACF Snippets
   
3. LOCAL BY FLYWHEEL (FREE)
   → Ambiente WordPress local
   → Zero config, zero Docker stress
   
4. GIT + GITHUB (FREE)
   → Version control essencial
TOTAL: $20/mês (só Claude Web)

WORKFLOW SPRINT:
bash# SEGUNDA - Planejamento
→ Claude Web: "Sprint 2 começando, me dê checklist"

# TERÇA-QUINTA - Desenvolvimento  
→ VS Code: Codar baseado no plano
→ Claude Web: Debugging pontual (erros, dúvidas)

# SEXTA - Review
→ Claude Web: "Code review deste arquivo"
→ Git commit + push

# LOOP para próxima sprint

⚠️ RISCOS ESPECÍFICOS APOLLO
TOP 3 QUE PODEM QUEBRAR:

JWT em Produção

php   ❌ NUNCA: Modificar libera.apollo.* endpoints
   ✅ SEMPRE: Apenas referenciar para validação

ACF Version Conflicts

bash   ❌ NUNCA: Instalar ACF + ACF PRO + SCF juntos
   ✅ SEMPRE: Apenas ACF PRO via Composer

CPT Name Length

php   ❌ apollo_rio_evento (16 chars - limite 20, mas perigoso)
   ✅ ario_evento (10 chars - seguro)

🎵 PRÓXIMOS PASSOS IMEDIATOS
bash1. ✅ Confirmar ACF PRO key disponível
2. ✅ Decidir: Usar Flynt OU plugin puro?
3. ✅ Setup Local environment
4. ✅ Começar Sprint 0 (2-3 dias)

📚 RESPOSTA SOBRE ACF OPTIONS:
RECOMENDAÇÃO: A + Flynt Concepts
USE:

✅ A) ACF PRO Installer (Composer)
✅ Conceitos do Flynt (component structure)
❌ NÃO use Flynt completo (é theme, Apollo é plugin)

PORQUÊ:
php// Flynt = Theme-based (assets, Timber, Twig)
// Apollo = Plugin-based (independente de tema)

// MAS aproveite:
- Estrutura de componentes
- ACF field organization
- getACFLayout() pattern
