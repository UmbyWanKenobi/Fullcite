<?php
/*
Plugin Name: Fullcite – Citazioni bibliografiche Classic Editor
Description: Citazioni con stile, rimando SVG e lettura diretta via API Zotero. Include bottoni TinyMCE, export BibTeX e CSL JSON.
Version: 3.5
Author: Umberto
License: GPL2
*/

// ====================================================================
// ▶️ CONFIGURAZIONE API ZOTERO (con pagina impostazioni)
// ====================================================================

function fullcite_get_zotero_user_id() {
    $options = get_option('fullcite_settings');
    return $options['zotero_user_id'] ?? '';
}

function fullcite_get_zotero_api_key() {
    $options = get_option('fullcite_settings');
    return $options['zotero_api_key'] ?? '';
}

// Definisci le costanti solo se non sono già definite e se abbiamo valori salvati
if (!defined('ZOTERO_USER_ID') && fullcite_get_zotero_user_id()) {
    define('ZOTERO_USER_ID', fullcite_get_zotero_user_id());
}

if (!defined('ZOTERO_API_KEY') && fullcite_get_zotero_api_key()) {
    define('ZOTERO_API_KEY', fullcite_get_zotero_api_key());
}

// ====================================================================
// ▶️ PAGINA DI AMMINISTRAZIONE
// ====================================================================

// Aggiunge il menu di amministrazione
add_action('admin_menu', 'fullcite_add_admin_menu');
function fullcite_add_admin_menu() {
    add_options_page(
        'Impostazioni Fullcite', 
        'Fullcite', 
        'manage_options', 
        'fullcite-settings', 
        'fullcite_settings_page'
    );
}

// Registra le impostazioni
add_action('admin_init', 'fullcite_settings_init');
function fullcite_settings_init() {
    register_setting('fullcite_settings_group', 'fullcite_settings');
    
    add_settings_section(
        'fullcite_zotero_section', 
        'Configurazione API Zotero', 
        'fullcite_zotero_section_callback', 
        'fullcite-settings'
    );
    
    add_settings_field(
        'zotero_user_id', 
        'Zotero User ID', 
        'zotero_user_id_render', 
        'fullcite-settings', 
        'fullcite_zotero_section'
    );
    
    add_settings_field(
        'zotero_api_key', 
        'Zotero API Key', 
        'zotero_api_key_render', 
        'fullcite-settings', 
        'fullcite_zotero_section'
    );
}

// Callback per la sezione
function fullcite_zotero_section_callback() {
    echo '<p>Inserisci le credenziali per l\'API Zotero. Puoi trovare queste informazioni nel tuo <a href="https://www.zotero.org/settings/keys" target="_blank">account Zotero</a>.</p>';
}

// Render dei campi
function zotero_user_id_render() {
    $options = get_option('fullcite_settings');
    ?>
    <input type="text" name="fullcite_settings[zotero_user_id]" 
           value="<?php echo esc_attr($options['zotero_user_id'] ?? ''); ?>" 
           style="width: 300px;">
    <?php
}

function zotero_api_key_render() {
    $options = get_option('fullcite_settings');
    ?>
    <input type="text" name="fullcite_settings[zotero_api_key]" 
           value="<?php echo esc_attr($options['zotero_api_key'] ?? ''); ?>" 
           style="width: 300px;">
    <?php
}

// Pagina delle impostazioni
function fullcite_settings_page() {
    ?>
    <div class="wrap">
        <h1>Impostazioni Fullcite</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('fullcite_settings_group');
            do_settings_sections('fullcite-settings');
            submit_button('Salva Impostazioni');
            ?>
        </form>
    </div>
    <?php
}

// Aggiunge link alle impostazioni nella lista dei plugin
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'fullcite_add_settings_link');
function fullcite_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=fullcite-settings') . '">Impostazioni</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// ====================================================================
// ▶️ BOTTONI TINYMCE
// ====================================================================

add_action('admin_init', function() {
    if (!current_user_can('edit_posts') || get_user_option('rich_editing') !== 'true') {
        return;
    }
    
    add_filter('mce_buttons', function($buttons) {
        return array_merge($buttons, ['fullcite','bibliografia','bibtex','zoterojson']);
    });
    
    add_filter('mce_external_plugins', function($plugins) {
        $base = plugin_dir_url(__FILE__);
        return array_merge($plugins, [
            'fullcite'     => $base . 'fullcite.js',
            'bibliografia' => $base . 'bibliografia.js',
            'bibtex'       => $base . 'bibtex.js',
            'zoterojson'   => $base . 'zoterojson.js',
        ]);
    });
});

// ====================================================================
// ▶️ SHORTCODES
// ====================================================================

// ── Shortcode [fullcite] ───────────────────────────────────────────
function fullcite_shortcode($atts) {
    static $notes_raw = [], $notes_formatted = [];
    $a = shortcode_atts([
        'author'   => '', 'title'    => '', 'date'     => '', 'location' => '',
        'uri'      => '', 'style'    => 'apa', 'showicon' => 'yes',
        'zoteroid' => ''
    ], $atts);

    if (!empty($a['zoteroid'])) {
        $z = fullcite_zotero_get($a['zoteroid']);
        if (is_array($z)) {
            $a = array_merge($a, $z);
        } else {
            error_log("fullcite: token '{$a['zoteroid']}' non trovato via API Zotero");
        }
    }

    switch (strtolower($a['style'])) {
        case 'apa':
            $note = "{$a['author']} ({$a['date']}). <em>{$a['title']}</em>. {$a['location']}";
            break;
        case 'mla':
            $note = "{$a['author']}. <em>{$a['title']}</em>. {$a['location']}, {$a['date']}";
            break;
        case 'ama':
            $note = "{$a['author']}. <em>{$a['title']}</em>. {$a['location']}; {$a['date']}.";
            break;
        default:
            $note = "{$a['author']}, <em>{$a['title']}</em>, {$a['location']}, {$a['date']}";
    }

    if (!empty($a['uri'])) {
        $note .= '. <a href="' . esc_url($a['uri'])
              . '" target="_blank" rel="noopener">Fonte</a>';
    }

    $notes_raw[] = $a;
    $notes_formatted[] = $note;
    $GLOBALS['fullcite_notes_raw'] = $notes_raw;
    $GLOBALS['fullcite_notes_formatted'] = $notes_formatted;
    $index = count($notes_formatted);

    $icon = '';
    if (strtolower($a['showicon']) === 'yes') {
        $icon = '<svg viewBox="0 0 24 24" width="14" height="14" '
              . 'style="vertical-align:middle;margin-right:0.15em;fill:currentColor;opacity:0.5">'
              . '<path d="M3 6v15a1 1 0 001.32.95l5.68-1.9 '
              . '5.68 1.9A1 1 0 0017 21V6a1 1 0 00-1-1H4a1 1 '
              . '0 00-1 1z"/></svg>';
    }

    return '<sup><a href="#fullcite-note-' . $index
         . '" id="fullcite-ref-' . $index . '">[' . $icon . $index . ']</a></sup>';
}

// ── Shortcode [bibliografia] ────────────────────────────────────────
function fullcite_bibliografia_shortcode() {
    $list = $GLOBALS['fullcite_notes_formatted'] ?? [];
    if (empty($list)) return '';
    $out = '<div class="fullcite-bibliografia"><hr><h3>Bibliografia</h3><ol>';
    foreach ($list as $i => $note) {
        $ref = $i + 1;
        $out .= '<li id="fullcite-note-' . $ref . '">'
              . $note . ' <a href="#fullcite-ref-' . $ref . '">↩︎</a></li>';
    }
    $out .= '</ol></div>';
    return $out;
}

// ── Shortcode [bibtex] ──────────────────────────────────────────────
function fullcite_bibtex_shortcode() {
    $notes = $GLOBALS['fullcite_notes_raw'] ?? [];
    if (empty($notes)) return '';
    $out = '<pre><code>';
    foreach ($notes as $n) {
        $key = strtolower(preg_replace('/[^a-z0-9]/', '', $n['author'])) . $n['date'];
        $out .= "@misc{{$key},\n"
              . "  author = {" . $n['author']   . "},\n"
              . "  title  = {" . $n['title']    . "},\n"
              . "  year   = {" . $n['date']     . "},\n"
              . "  institution = {" . $n['location'] . "},\n";
        if (!empty($n['uri'])) {
            $out .= "  url    = {" . $n['uri'] . "},\n";
        }
        $out .= "}\n\n";
    }
    $out .= '</code></pre>';
    $out .= '<button onclick="navigator.clipboard.writeText(this.previousElementSibling.innerText)">📋 Copia BibTeX</button>';
    return $out;
}

// ── Shortcode [zoterojson] ──────────────────────────────────────────
function fullcite_zoterojson_shortcode() {
    $notes = $GLOBALS['fullcite_notes_raw'] ?? [];
    if (empty($notes)) return '';
    $items = [];
    foreach ($notes as $n) {
        $items[] = [
            'type'      => 'article',
            'title'     => $n['title'],
            'author'    => [['family' => $n['author']]],
            'issued'    => ['date-parts' => [[$n['date']]]],
            'publisher' => $n['location'],
            'URL'       => $n['uri'],
        ];
    }
    $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $out  = '<pre><code>' . $json . '</code></pre>';
    $out .= '<button onclick="navigator.clipboard.writeText(this.previousElementSibling.innerText)">📋 Copia CSL JSON</button>';
    return $out;
}

// ====================================================================
// ▶️ API ZOTERO CON CACHING
// ====================================================================

function fullcite_zotero_get($token) {
    $cache_key = 'fc_zotero_' . $token;
    if (false !== ($cached = get_transient($cache_key))) {
        return $cached;
    }

    $user_id = fullcite_get_zotero_user_id();
    $api_key = fullcite_get_zotero_api_key();
    
    if (empty($user_id) || empty($api_key)) {
        error_log("fullcite: credenziali Zotero non configurate");
        return null;
    }

    $url = sprintf(
        'https://api.zotero.org/users/%s/items/%s?format=json',
        urlencode($user_id),
        urlencode($token)
    );
    $args = [
        'headers' => [
            'Zotero-API-Key' => $api_key,
            'Accept'         => 'application/json',
        ],
        'timeout' => 8,
    ];

    $res = wp_remote_get($url, $args);
    if (is_wp_error($res)) {
        error_log("fullcite: errore HTTP Zotero ({$token}): " . $res->get_error_message());
        return null;
    }
    if (wp_remote_retrieve_response_code($res) !== 200) {
        error_log("fullcite: risposta API Zotero {" . wp_remote_retrieve_response_code($res) . "} per token {$token}");
        return null;
    }

    $body = wp_remote_retrieve_body($res);
    $data = json_decode($body, true);
    if (empty($data['data'])) {
        error_log("fullcite: nessun campo data per token {$token}");
        return null;
    }

    $item = $data['data'];
    $author = '';
    if (!empty($item['creators'][0])) {
        $c = $item['creators'][0];
        $author = trim(($c['lastName'] ?? '') . ' ' . ($c['firstName'] ?? ''));
    }
    $result = [
        'author'   => $author,
        'title'    => $item['title']     ?? '',
        'date'     => $item['date']      ?? '',
        'location' => $item['publisher'] ?? '',
        'uri'      => $item['url']       ?? '',
        'style'    => 'apa',
    ];

    set_transient($cache_key, $result, 12 * HOUR_IN_SECONDS);
    return $result;
}

// ====================================================================
// ▶️ REGISTRAZIONE SHORTCODES
// ====================================================================

add_shortcode('fullcite',     'fullcite_shortcode');
add_shortcode('bibliografia', 'fullcite_bibliografia_shortcode');
add_shortcode('bibtex',       'fullcite_bibtex_shortcode');
add_shortcode('zoterojson',   'fullcite_zoterojson_shortcode');