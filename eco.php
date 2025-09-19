<?php
/**
* Plugin Name: Ecosystem
* Description: A plugin to manage a custom 'Ecosystem' of code snippets and design templates.
* Version: 1.0
* Author: Apollo::rio
*/

// Security: Prevent direct file access
if (!defined('ABSPATH')) {
exit;
}

// =============================================================================
// 1. REGISTER CUSTOM POST TYPE & TAXONOMIES
// =============================================================================

function apollo_register_post_type() {
$labels = [
name' => _x('Ecosystem', 'Post type general name', 'eco'),
singular_name' => _x('Ecosystem', 'Post type singular name', 'eco'),
menu_name' => _x('Ecosystem', 'Admin Menu text', 'apollo-eco'),
name_admin_bar' => _x('Ecosystem Item', 'Add New on Toolbar', 'apollo-eco'),
add_new' => __('Add New', 'apollo-eco'),
add_new_item' => __('Add New Ecosystem Item', 'apollo-eco'),
new_item' => __('New Ecosystem Item', 'apollo-eco'),
edit_item' => __('Edit Ecosystem Item', 'apollo-eco'),
view_item' => __('View Ecosystem Item', 'apollo-eco'),
all_items' => __('All Ecosystem Items', 'apollo-eco'),
search_items' => __('Search Ecosystem Items', 'apollo-eco'),
parent_item_colon' => __('Parent Ecosystem Items:', 'apollo-eco'),
not_found' => __('No Ecosystem items found.', 'apollo-eco'),
not_found_in_trash' => __('No Ecosystem items found in Trash.', 'apollo-eco'),
];

$args = [
labels' => $labels,
public' => true,
publicly_queryable' => true,
show_ui' => true,
show_in_menu' => false, // We will show it under our custom menu page
query_var' => true,
rewrite' => ['slug' => 'ecosystem'],
capability_type' => 'post',
has_archive' => true,
hierarchical' => false,
menu_position' => null,
supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
menu_icon' => 'dashicons-planet',
];

register_post_type('eco', $args);
}
add_action('init', 'apollo_register_post_type');

function apollo_register_taxonomies() {
// Fit Cultural (Plugins) Taxonomy
$plugin_labels = [
name' => _x('Plugins', 'taxonomy general name'),
singular_name' => _x('Plugin', 'taxonomy singular name'),
search_items' => __('Search Plugins'),
all_items' => __('All Plugins'),
parent_item' => __('Parent Plugin'),
parent_item_colon' => __('Parent Plugin:'),
edit_item' => __('Edit Plugin'),
update_item' => __('Update Plugin'),
add_new_item' => __('Add New Plugin'),
new_item_name' => __('New Plugin Name'),
menu_name' => __('Plugins'),
];
register_taxonomy('fit_cultural', ['eco'], [
hierarchical' => false,
labels' => $plugin_labels,
show_ui' => true,
show_admin_column' => true,
query_var' => true,
rewrite' => ['slug' => 'plugin'],
]);

// Item Type Taxonomy
$type_labels = [
name' => _x('Item Types', 'taxonomy general name'),
singular_name' => _x('Item Type', 'taxonomy singular name'),
search_items' => __('Search Item Types'),
all_items' => __('All Item Types'),
edit_item' => __('Edit Item Type'),
update_item' => __('Update Item Type'),
add_new_item' => __('Add New Item Type'),
new_item_name' => __('New Item Type Name'),
menu_name' => __('Item Types'),
];
register_taxonomy('item_type', ['eco'], [
hierarchical' => false,
labels' => $type_labels,
show_ui' => true,
show_admin_column' => true,
query_var' => true,
rewrite' => ['slug' => 'item-type'],
]);
}
add_action('init', 'apollo_register_taxonomies');


// =============================================================================
// 2. CREATE ADMIN MENU PAGE (THE DASHBOARD)
// =============================================================================
function apollo_admin_menu() {
add_menu_page(
Apollo Ecosystem',
Apollo',
manage_options',
apollo_dashboard',
apollo_dashboard_page_html',
dashicons-planet',
20
);
// Sub-menu to list all ecosystem items
add_submenu_page(
apollo_dashboard',
All Ecosystem Items',
All Items',
manage_options',
edit.php?post_type=eco'
);
// Sub-menu to add a new item
add_submenu_page(
apollo_dashboard',
Add New Item',
Add New',
manage_options',
post-new.php?post_type=eco'
);
}
add_action('admin_menu', 'apollo_admin_menu');

// =============================================================================
// 3. ADMIN PAGE HTML & LOGIC
// =============================================================================
function apollo_enqueue_admin_scripts($hook) {
// Only load on our plugin's pages
if ('toplevel_page_apollo_dashboard' !== $hook && 'eco' !== get_post_type()) {
return;
}
// Tailwind CSS
wp_enqueue_script('apollo-tailwind', 'https://cdn.tailwindcss.com', [], null, false);
// SortableJS for drag and drop
wp_enqueue_script('apollo-sortable', 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js', [], '1.15.0', true);

// Custom CSS and JS
wp_add_inline_style('apollo-tailwind', apollo_get_admin_css());
wp_add_inline_script('apollo-sortable', apollo_get_admin_js());
}
add_action('admin_enqueue_scripts', 'apollo_enqueue_admin_scripts');

function apollo_dashboard_page_html() {
?>
<div class="wrap apollo-wrapper text-gray-800 p-4 sm:p-6 lg:p-8">
<main class="max-w-7xl mx-auto">
<!-- Navigation Tabs -->
<div class="mb-8 bg-white p-2 rounded-xl shadow-md border border-gray-200 flex space-x-2">
<button class="apollo-tab-link active flex-1 text-center py-3 px-4 rounded-lg font-semibold" data-tab="eco">Ecosystem</button>
<button class="apollo-tab-link flex-1 text-center py-3 px-4 rounded-lg font-semibold" data-tab="codes">Code Registers</button>
<button class="apollo-tab-link flex-1 text-center py-3 px-4 rounded-lg font-semibold" data-tab="designs">Design Registers</button>
</div>

<!-- Tab Content Container -->
<div>
<!-- Ecosystem Tab (Dashboard) -->
<div id="tab-eco" class="apollo-tab-pane active">
<div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-200">
<div class="mb-8 flex justify-between items-center">
<div>
<h1 class="text-3xl font-bold text-gray-900">Ecosystem Dashboard</h1>
<p class="mt-1 text-gray-500">Your content creation hub.</p>
</div>
<a href="<?php echo admin_url('post-new.php?post_type=eco'); ?>" class="bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:bg-indigo-700">Add New Item</a>
</div>
<!-- Overview Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
<?php
$total_items = wp_count_posts('eco');
$active_projects = count_user_posts(get_current_user_id(), 'eco');
?>
<div class="bg-indigo-50 p-6 rounded-xl border border-indigo-200"><span class="block text-sm font-medium text-indigo-700">Total de Itens</span><span class="block text-3xl font-bold text-indigo-900 mt-1"><?php echo $total_items->publish; ?></span></div>
<div class="bg-green-50 p-6 rounded-xl border border-green-200"><span class="block text-sm font-medium text-green-700">Meus Itens</span><span class="block text-3xl font-bold text-green-900 mt-1"><?php echo $active_projects; ?></span></div>
<!-- To-Do List Card -->
<div class="bg-amber-50 p-6 rounded-xl border border-amber-200">
<span class="block text-sm font-medium text-amber-700">To-Do</span>
<div id="todo-list" class="min-h-[100px] mt-2 space-y-2">
<div class="todo-item p-2 bg-white rounded shadow-sm cursor-move">Test drag and drop</div>
<div class="todo-item p-2 bg-white rounded shadow-sm cursor-move">Check new plugin button</div>
</div>
</div>
<!-- Focus Card -->
<div class="bg-rose-50 p-6 rounded-xl border border-rose-200">
<span class="block text-sm font-medium text-rose-700">Focar aqui:</span>
<div id="focus-list" class="min-h-[100px] mt-2 space-y-2">
<div class="p-2 bg-white/50 rounded text-gray-500">Drop tasks here</div>
</div>
</div>
</div>
</div>
</div>

<!-- Code Registers Tab -->
<div id="tab-codes" class="apollo-tab-pane">
<div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-200">
<h1 class="text-3xl font-bold text-gray-900 mb-6">Code Registers</h1>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php
$args = ['post_type' => 'eco', 'posts_per_page' => -1, 'meta_query' => [['key' => '_apollo_content_type', 'value' => 'code']]];
$code_query = new WP_Query($args);
if ($code_query->have_posts()) : while ($code_query->have_posts()) : $code_query->the_post();
?>
<div class="code-card cursor-pointer" data-content="<?php echo htmlspecialchars(get_the_content()); ?>">
<div class="code-card__header">
<svg class="code-card__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
<h3><?php the_title(); ?></h3>
</div>
<div class="code-card__body">
<p><?php echo get_the_excerpt(); ?></p>
</div>
</div>
<?php endwhile; wp_reset_postdata(); else: ?>
<p>No code items found.</p>
<?php endif; ?>
</div>
</div>
</div>
<!-- Designs Registers Tab -->
<div id="tab-designs" class="apollo-tab-pane">
<div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-200">
<h1 class="text-3xl font-bold text-gray-900 mb-6">Design Registers</h1>
<section class="design-card-list">
<?php
$args = ['post_type' => 'eco', 'posts_per_page' => -1, 'meta_query' => [['key' => '_apollo_content_type', 'value' => 'design']]];
$design_query = new WP_Query($args);
if ($design_query->have_posts()) : while ($design_query->have_posts()) : $design_query->the_post();
$plugins = get_the_term_list(get_the_ID(), 'fit_cultural', '', ', ');
?>
<article class="design-card">
<header class="design-card-header" style="background-image: url('<?php echo get_the_post_thumbnail_url() ?: 'https://placehold.co/600x400/e0e7ff/4f46e5?text=Design'; ?>')">
<h2><?php the_title(); ?></h2>
</header>
<div class="design-card-author">
<div class="design-author-name">
<div class="design-author-name-prefix">Plugin</div>
<?php echo strip_tags($plugins) ?: 'General'; ?>
</div>
</div>
</article>
<?php endwhile; wp_reset_postdata(); else: ?>
<p>No design items found.</p>
<?php endif; ?>
</section>
</div>
</div>

</div>
</main>
</div>

<!-- Lightbox Modal -->
<div id="apollo-lightbox" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center p-4 z-[9999]">
<div class="relative bg-[#0d1117] text-white max-w-4xl w-full max-h-[80vh] rounded-lg shadow-xl">
<button id="close-lightbox" class="absolute top-2 right-4 text-2xl text-gray-400 hover:text-white">&times;</button>
<div class="p-6">
<h3 class="text-lg font-mono text-gray-300 mb-4">// Generated PHP Code</h3>
<div class="overflow-auto max-h-[calc(80vh-80px)]">
<pre class="language-php"><code id="lightbox-content"></code></pre>
</div>
</div>
</div>
</div>
<?php
}


// =============================================================================
// 4. CUSTOM META BOXES FOR 'ECO' POST TYPE
// =============================================================================
function apollo_add_meta_boxes() {
add_meta_box(
apollo_eco_details',
Ecosystem Item Details',
apollo_meta_box_html',
eco',
normal',
high'
);
}
add_action('add_meta_boxes', 'apollo_add_meta_boxes');

function apollo_meta_box_html($post) {
// Retrieve existing values from the database
$origem_url = get_post_meta($post->ID, '_apollo_origem_url', true);
$content_type = get_post_meta($post->ID, '_apollo_content_type', true);
$rating = get_post_meta($post->ID, '_apollo_rating', true);
wp_nonce_field('apollo_update_post_meta', 'apollo_meta_nonce');
?>
<div class="space-y-6 p-4">
<!-- Row 1 -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div>
<label for="apollo_origem_url" class="block text-sm font-medium text-gray-700 mb-1">Origem (URL)</label>
<input type="url" id="apollo_origem_url" name="apollo_origem_url" class="w-full" value="<?php echo esc_attr($origem_url); ?>" placeholder="https://example.com">
</div>
<div>
<label for="apollo_rating" class="block text-sm font-medium text-gray-700 mb-1">Rate</label>
<select id="apollo_rating" name="apollo_rating" class="w-full">
<?php for ($i = 5; $i >= 1; $i -= 0.5): ?>
<option value="<?php echo $i; ?>" <?php selected($rating, $i); ?>><?php echo $i; ?> Stars</option>
<?php endfor; ?>
</select>
</div>
</div>
<!-- Row 2 -->
<div>
<label for="apollo_content_type" class="block text-sm font-medium text-gray-700 mb-1">Conteúdo</label>
<select id="apollo_content_type" name="apollo_content_type" class="w-full">
<option value="code" <?php selected($content_type, 'code'); ?>>Code</option>
<option value="design" <?php selected($content_type, 'design'); ?>>Design</option>
</select>
</div>
<!-- Note about the code generator -->
<p class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border border-blue-200">
<strong>Note:</strong> Use the main content editor below to access the PHP Code Generator. The output from the generator will be saved as the main content of this item.
</p>
</div>
<?php
}

function apollo_save_meta_box_data($post_id) {
// Check for nonce and autosave
if (!isset($_POST['apollo_meta_nonce']) || !wp_verify_nonce($_POST['apollo_meta_nonce'], 'apollo_update_post_meta') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
return;
}
// Save Origem URL
if (isset($_POST['apollo_origem_url'])) {
update_post_meta($post_id, '_apollo_origem_url', sanitize_text_field($_POST['apollo_origem_url']));
}
// Save Content Type
if (isset($_POST['apollo_content_type'])) {
update_post_meta($post_id, '_apollo_content_type', sanitize_text_field($_POST['apollo_content_type']));
}
// Save Rating
if (isset($_POST['apollo_rating'])) {
update_post_meta($post_id, '_apollo_rating', sanitize_text_field($_POST['apollo_rating']));
}
}
add_action('save_post', 'apollo_save_meta_box_data');

// =============================================================================
// 5. REPLACE EDITOR WITH PHP CODE GENERATOR
// =============================================================================
function apollo_replace_editor_with_generator($content) {
global $post;
if ($post->post_type == 'eco') {
// Keep existing content if available, otherwise show generator
$generator_html = '
<div class="apollo-generator-wrapper p-4 border rounded-lg bg-gray-50">
<style>
.apollo-generator-wrapper .form-group { margin-bottom: 1.5rem; }
.apollo-generator-wrapper label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.apollo-generator-wrapper textarea { width: 100%; min-height: 120px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; }
.apollo-generator-wrapper .hidden { display: none; }
.apollo-generator-wrapper #output { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 4px; white-space: pre-wrap; margin-top: 1rem; }
.apollo-generator-wrapper .btn-b { background-color: #4f46e5; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
</style>
<div class="converter-form">
<div class="form-group">
<label for="header-imports">HEADER ~ Import and Definition</label>
<textarea id="header-imports" placeholder="e.g., <meta name=\'viewport\' content=\'width=device-width, initial-scale=1.0\'>"></textarea>
</div>
<div class="form-group">
<label for="css">STYLE / CSS</label>
<textarea id="css" placeholder="e.g., .card { border: 1px solid #ddd; }"></textarea>
</div>
<div class="form-group conditional-sections">
<label>Select Components to Include:</label>
<label class="font-normal inline-flex items-center mr-4"><input type="checkbox" class="mr-2" id="html-element" value="html-element"> HTML Element</label>
<label class="font-normal inline-flex items-center mr-4"><input type="checkbox" class="mr-2" id="html-layout" value="html-layout"> HTML Layout/Grid</label>
<label class="font-normal inline-flex items-center mr-4"><input type="checkbox" class="mr-2" id="html-section" value="html-section"> HTML Section</label>
<label class="font-normal inline-flex items-center mr-4"><input type="checkbox" class="mr-2" id="html-uikit" value="html-uikit"> HTML UI KIT</label>
<label class="font-normal inline-flex items-center"><input type="checkbox" class="mr-2" id="html-widget" value="html-widget"> HTML Widget</label>
</div>
<div id="html-element-group" class="form-group hidden"><label for="html-element-input">HTML Element:</label><textarea id="html-element-input" placeholder="e.g., <div class=\'card\'>...</div>"></textarea></div>
<div id="html-layout-group" class="form-group hidden"><label for="html-layout-input">HTML Layout/Grid:</label><textarea id="html-layout-input" placeholder="e.g., <div class=\'grid\'>...</div>"></textarea></div>
<div id="html-section-group" class="form-group hidden"><label for="html-section-input">HTML Section:</label><textarea id="html-section-input" placeholder="e.g., <section>...</section>"></textarea></div>
<div id="html-uikit-group" class="form-group hidden"><label for="html-uikit-input">HTML UI KIT:</label><textarea id="html-uikit-input" placeholder="e.g., buttons, forms..."></textarea></div>
<div id="html-widget-group" class="form-group hidden"><label for="html-widget-input">HTML Widget:</label><textarea id="html-widget-input" placeholder="e.g., custom components..."></textarea></div>
<div class="form-group">
<label for="script">Script (JS)</label>
<textarea id="script" placeholder="e.g., console.log(\'Hello\');"></textarea>
</div>
<button type="button" class="btn-b" onclick="generateAndSetPhp()">Generate & Set Content</button>
<p class="text-sm mt-2">Clicking the button will generate PHP and place it in the textarea below. You can then edit it before saving the post.</p>
</div>
<script>
// JS for PHP Generator
document.addEventListener("DOMContentLoaded", function() {
document.querySelectorAll(\'.apollo-generator-wrapper input[type="checkbox"]\').forEach(checkbox => {
checkbox.addEventListener("change", function() {
const groupId = this.id + "-group";
const group = document.getElementById(groupId);
if (group) group.classList.toggle("hidden", !this.checked);
});
});
});

function generateAndSetPhp() {
const header = document.getElementById("header-imports").value.trim();
const css = document.getElementById("css").value.trim();
const script = document.getElementById("script").value.trim();
let htmlParts = "";
if (document.getElementById("html-element").checked) htmlParts += document.getElementById("html-element-input").value.trim() + "\\n";
if (document.getElementById("html-layout").checked) htmlParts += document.getElementById("html-layout-input").value.trim() + "\\n";
if (document.getElementById("html-section").checked) htmlParts += document.getElementById("html-section-input").value.trim() + "\\n";
if (document.getElementById("html-uikit").checked) htmlParts += document.getElementById("html-uikit-input").value.trim() + "\\n";
if (document.getElementById("html-widget").checked) htmlParts += document.getElementById("html-widget-input").value.trim() + "\\n";
const isCard = htmlParts.includes("class=\\"card\\"") || htmlParts.includes("class=\'card\'");
let phpContent = `<?php\\n// PHP Template Generated by Apollo\\nget_header();\\n?>\\n`;
if (header) phpContent += `${header}\\n`;
if (css) phpContent += `<style>\\n${css}\\n</style>\\n`;
if (isCard) {
phpContent += `<?php\\n$args = [\'post_type\' => \'post\', \'posts_per_page\' => -1];\\n$query = new WP_Query($args);\\nif ($query->have_posts()) : ?>\\n<div class="cards-container">\\n<?php while ($query->have_posts()) : $query->the_post(); ?>\\n`;
}
phpContent += `${htmlParts}\\n`;
if (isCard) {
phpContent += `<?php endwhile; ?>\\n</div>\\n<?php endif; wp_reset_postdata(); ?>\\n`;
}
if (script) {
phpContent += `<script>\\n${script}\\n</script>\\n`;
}
phpContent += `<?php get_footer(); ?>\\n`;

// Set content to the main WP editor
const editor = document.getElementById("content");
if (editor) {
editor.value = phpContent;
}
}
</script>
</div>
;
return $generator_html . $content; // Show generator above the editor
}
return $content;
}
add_filter('the_editor', 'apollo_replace_editor_with_generator');


// =============================================================================
// 6. ADMIN CSS & JS
// =============================================================================

function apollo_get_admin_css() {
return '
body { font-family: "Inter", sans-serif; }
.apollo-tab-pane { display: none; animation: fadeIn 0.5s ease-in-out; }
.apollo-tab-pane.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
/* Lightbox styles */
#apollo-lightbox pre { background: #1e293b !important; }
/* Code Card styles (from Codepen) */
.code-card { background: #fff; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s ease; }
.code-card:hover { transform: translateY(-5px); box-shadow: 0 8px 12px rgba(0,0,0,0.15); }
.code-card__header { padding: 20px; display: flex; align-items: center; border-bottom: 1px solid #eee; }
.code-card__logo { width: 30px; height: 30px; margin-right: 15px; fill: #4f46e5; }
.code-card__header h3 { margin: 0; font-size: 1.1em; font-weight: 600; }
.code-card__body { padding: 20px; font-size: 0.9em; color: #555; }

/* Design Card styles (from Codepen) */
.design-card-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.design-card { display: flex; flex-direction: column; background-color: #fff; cursor: pointer; border-radius: 10px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
.design-card-header { padding-top: 20px; padding-bottom: 20px; background-size: cover; background-position: center; border-radius: 10px 10px 0 0; }
.design-card-header h2 { color: white; font-size: 20px; font-weight: 600; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); text-align: center; }
.design-card-author { display: flex; align-items: center; padding: 15px; }
.design-author-name { text-align: left; }
.design-author-name-prefix { color: #777; font-size: 12px; }
;
}

function apollo_get_admin_js() {
return '
document.addEventListener("DOMContentLoaded", function() {
const tabLinks = document.querySelectorAll(".apollo-tab-link");
const tabPanes = document.querySelectorAll(".apollo-tab-pane");

const activeTabClasses = ["bg-indigo-600", "text-white"];
const inactiveTabClasses = ["text-gray-600", "hover:bg-indigo-100"];

function setActiveTab(tabId) {
tabLinks.forEach(link => {
if (link.getAttribute("data-tab") === tabId) {
link.classList.add(...activeTabClasses);
link.classList.remove(...inactiveTabClasses);
} else {
link.classList.remove(...activeTabClasses);
link.classList.add(...inactiveTabClasses);
}
});
tabPanes.forEach(pane => {
pane.classList.toggle("active", pane.id === "tab-" + tabId);
});
}

tabLinks.forEach(link => {
link.addEventListener("click", function(e) {
e.preventDefault();
const targetTabId = this.getAttribute("data-tab");
setActiveTab(targetTabId);
});
});
// Drag and Drop ToDo List
const todoList = document.getElementById("todo-list");
const focusList = document.getElementById("focus-list");
if (todoList && focusList) {
new Sortable(todoList, {
group: "shared",
animation: 150
});
new Sortable(focusList, {
group: "shared",
animation: 150
});
}

// Lightbox logic
const lightbox = document.getElementById("apollo-lightbox");
const lightboxContent = document.getElementById("lightbox-content");
const closeLightboxBtn = document.getElementById("close-lightbox");

document.querySelectorAll(".code-card").forEach(card => {
card.addEventListener("click", function() {
const content = this.dataset.content;
lightboxContent.textContent = content;
lightbox.classList.remove("hidden");
lightbox.classList.add("flex");
});
});
if (closeLightboxBtn) {
closeLightboxBtn.addEventListener("click", () => {
lightbox.classList.add("hidden");
lightbox.classList.remove("flex");
});
}
if (lightbox) {
lightbox.addEventListener("click", (e) => {
if (e.target === lightbox) {
lightbox.classList.add("hidden");
lightbox.classList.remove("flex");
}
});
}
});
;
}

?>
