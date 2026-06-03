<?php
// =============================================================================
// Tests de Seguridad para edu-theme
// =============================================================================

$themeDir = dirname(__DIR__);

function theme_php_files(string $themeDir): array {
    return array_merge(
        glob($themeDir . '/*.php') ?: [],
        glob($themeDir . '/template-parts/*.php') ?: []
    );
}

function assert_ordered(string $subject, array $needles, string $msg = ''): void
{
    $offset = -1;
    foreach ($needles as $needle) {
        $pos = strpos($subject, $needle);
        if ($pos === false || $pos <= $offset) {
            throw new AssertionError($msg ?: sprintf("'%s' no aparece en el orden esperado", $needle));
        }
        $offset = $pos;
    }
}

// -----------------------------------------------------------------------------
// Test 1: Todos los archivos PHP tienen proteccion ABSPATH
// -----------------------------------------------------------------------------

test('Todos los archivos PHP tienen proteccion ABSPATH', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        assert_contains("if ( ! defined( 'ABSPATH' ) ) exit;", $content,
            basename($file) . " falta proteccion ABSPATH");
    }
});

// -----------------------------------------------------------------------------
// Test 2: Cache keys usan json_encode en lugar de serialize
// -----------------------------------------------------------------------------

test('Cache keys usan json_encode en lugar de serialize', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_false(str_contains($fc, 'md5( serialize('), "serializado en cache keys");
    assert_true(str_contains($fc, 'md5( json_encode('), "json_encode en cache keys");
    assert_true(str_contains($fc, 'ksort('), "ksort en cache keys");
});

// -----------------------------------------------------------------------------
// Test 3: No se usa wp_cache_flush()
// -----------------------------------------------------------------------------

test('No se usa wp_cache_flush()', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_false(str_contains($fc, 'wp_cache_flush()'), "wp_cache_flush presente");
    assert_true(str_contains($fc, 'wp_cache_delete('), "wp_cache_delete ausente");
});

// -----------------------------------------------------------------------------
// Test 4: Validaciones de categoria en shortcodes
// -----------------------------------------------------------------------------

test('Shortcodes validan existencia de categorias', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_true(str_contains($fc, 'if ( get_category('), "falta get_category()");
    assert_true(str_contains($fc, 'term_exists('), "falta term_exists()");
});

// -----------------------------------------------------------------------------
// Test 5: Permisos de archivos seguros
// -----------------------------------------------------------------------------

test('Archivos PHP tienen permisos seguros', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        $perms = fileperms($file) & 0777;
        assert_true(in_array($perms, [0664, 0644, 0775, 0755]),
            basename($file) . " permisos invalidos");
    }
});

// -----------------------------------------------------------------------------
// Test 6: No hay funciones peligrosas
// -----------------------------------------------------------------------------

test('No hay funciones peligrosas', function() use ($themeDir) {
    $patterns = [
        '/eval\s*\\(\s*\$/',
        '/create_function\s*\\(\s*\$/', 
        '/assert\s*\\(\s*\$/',
        '/extract\s*\\(\s*\$/',
        '/parse_str\s*\\(\s*\$/'
    ];
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        foreach ($patterns as $p) {
            assert_false(preg_match($p, $content), basename($file) . " tiene funcion peligrosa");
        }
    }
});

// -----------------------------------------------------------------------------
// Test 7: Uso de funciones de escape
// -----------------------------------------------------------------------------

test('Se usan funciones de escape', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_true(str_contains($fc, 'esc_html('), "falta esc_html()");
    assert_true(str_contains($fc, 'esc_url('), "falta esc_url()");
    assert_true(str_contains($fc, 'esc_attr('), "falta esc_attr()");
});

// -----------------------------------------------------------------------------
// Test 8: Proteccion CSRF
// -----------------------------------------------------------------------------

test('Proteccion CSRF en acciones administrativas', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_true(str_contains($fc, 'wp_nonce_url('), "falta wp_nonce_url()");
    assert_true(str_contains($fc, 'check_admin_referer('), "falta check_admin_referer()");
    assert_true(str_contains($fc, 'current_user_can('), "falta current_user_can()");
});

// -----------------------------------------------------------------------------
// Test 9: Consultas SQL usan $wpdb->prepare()
// -----------------------------------------------------------------------------

test('Consultas SQL usan $wpdb->prepare()', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_true(str_contains($fc, '$wpdb->prepare('), "falta $wpdb->prepare()");
});

// -----------------------------------------------------------------------------
// Test 10: Funcion de borrado de transients existe
// -----------------------------------------------------------------------------

test('Funcion edu_delete_shortcode_transients existe', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_contains('function edu_delete_shortcode_transients()', $fc, "falta funcion");
    assert_true(str_contains($fc, 'wp_using_ext_object_cache()'), "falta verificacion object cache");
});

// -----------------------------------------------------------------------------
// Test 11: Todos los archivos PHP son sintacticamente validos
// -----------------------------------------------------------------------------

test('Todos los archivos PHP son sintacticamente validos', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        exec('php -l ' . escapeshellarg($file) . ' 2>&1', $output, $code);
        $result = implode('\n', $output);
        assert_false(str_contains($result, 'Parse error'),
            basename($file) . " error de sintaxis");
    }
});

// -----------------------------------------------------------------------------
// Test 12: Archivos con HTML despues de AB
// -----------------------------------------------------------------------------

test('Archivos con guard y HTML tienen cierre de etiqueta', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        if (str_contains($content, "if ( ! defined( 'ABSPATH' ) ) exit;")) {
            $afterGuard = substr($content, strpos($content, "exit;") + 5);
            $hasHtml = str_contains(trim($afterGuard), '<');
            $hasCloseTag = str_contains($afterGuard, '?>');
            if ($hasHtml && !$hasCloseTag) {
                throw new AssertionError(basename($file) . " necesita ?> antes de HTML");
            }
        }
    }
});

// -----------------------------------------------------------------------------
// Test 13: Archivos comienzan con PHP tag
// -----------------------------------------------------------------------------

test('Todos los archivos PHP comienzan con PHP', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        $trimmed = ltrim($content);
        assert_true(str_starts_with($trimmed, '<?php'),
            basename($file) . " debe comenzar con <?php");
    }
});

// -----------------------------------------------------------------------------
// Test 14: No hay salidas directas de titulos, enlaces o HTML media en listados
// -----------------------------------------------------------------------------

test('Salidas de listados usan escape contextual', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        assert_false(str_contains($content, '<?php the_title(); ?>'),
            basename($file) . " imprime the_title() sin esc_html()");
        assert_false(str_contains($content, '<?php the_permalink(); ?>'),
            basename($file) . " imprime the_permalink() sin esc_url()");
        assert_false(str_contains($content, 'echo $thumb;'),
            basename($file) . " imprime thumb sin wp_kses_post()");
        assert_false(str_contains($content, 'echo $thumb_html;'),
            basename($file) . " imprime thumb_html sin wp_kses_post()");
        assert_false(str_contains($content, 'echo $player_html;'),
            basename($file) . " imprime player_html sin wp_kses_post()");
    }
});

// -----------------------------------------------------------------------------
// Test 15: Walker del menu escapa URL y titulo
// -----------------------------------------------------------------------------

test('Walker del menu escapa URL y titulo', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_contains("'href' === \$attr ? esc_url( \$value ) : esc_attr( \$value )", $fc,
        "el walker debe usar esc_url() en href");
    assert_contains("esc_html( \$title )", $fc,
        "el walker debe escapar el titulo del enlace");
});

// -----------------------------------------------------------------------------
// Test 16: Atributos URL dinamicos usan esc_url()
// -----------------------------------------------------------------------------

test('Atributos URL dinamicos usan esc_url', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    $pattern = '/\b(?:href|src|action|formaction|poster)=["\']<\?php\s+(?!echo\s+esc_url\s*\()/i';
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        assert_false((bool) preg_match($pattern, $content, $matches),
            basename($file) . " tiene atributo URL dinamico sin esc_url(): " . ($matches[0] ?? ''));
    }
});

// -----------------------------------------------------------------------------
// Test 17: No hay atributos inline dinamicos sin esc_attr()
// -----------------------------------------------------------------------------

test('Atributos dinamicos no URL usan esc_attr', function() use ($themeDir) {
    $phpFiles = theme_php_files($themeDir);
    $pattern = '/\b(?:class|id|style|title|alt|aria-label|aria-controls|content|name|value)=["\']<\?php\s+(?!(?:echo\s+)?(?:esc_attr|esc_url|esc_attr_e|esc_html_e))/i';
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        assert_false((bool) preg_match($pattern, $content, $matches),
            basename($file) . " tiene atributo dinamico sin escape de atributo: " . ($matches[0] ?? ''));
    }
});

// -----------------------------------------------------------------------------
// Test 18: Accion admin de borrado exige capacidad, nonce y redireccion segura
// -----------------------------------------------------------------------------

test('Borrado de transients esta protegido en orden correcto', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_matches('/function\s+edu_handle_clear_transients\s*\(\)\s*\{.*?\}/s', $fc,
        "falta edu_handle_clear_transients()");
    preg_match('/function\s+edu_handle_clear_transients\s*\(\)\s*\{(?P<body>.*?)\n\}/s', $fc, $matches);
    $body = $matches['body'] ?? '';
    assert_ordered($body, [
        "isset( \$_GET['edu_clear_transients'] )",
        "current_user_can( 'manage_options' )",
        "check_admin_referer( 'edu_clear_transients' )",
        "edu_delete_shortcode_transients();",
        "wp_safe_redirect(",
        "exit;",
    ], "edu_handle_clear_transients() no valida capacidad/nonce antes de ejecutar");
    assert_false(str_contains($body, 'wp_redirect('), "usar wp_safe_redirect(), no wp_redirect()");
});

// -----------------------------------------------------------------------------
// Test 19: No hay superglobales nuevos fuera del flujo permitido
// -----------------------------------------------------------------------------

test('Superglobales publicos estan acotados', function() use ($themeDir) {
    $allowed = [
        'functions.php' => [
            "\$_GET['edu_clear_transients']",
        ],
    ];
    foreach (theme_php_files($themeDir) as $file) {
        $content = file_get_contents($file);
        preg_match_all('/\$_(?:GET|POST|REQUEST|COOKIE|SERVER)\[[^\]]+\]/', $content, $matches);
        $found = array_values(array_unique($matches[0]));
        $expected = $allowed[basename($file)] ?? [];
        sort($found);
        sort($expected);
        assert_eq($expected, $found, basename($file) . " usa superglobales no permitidos");
    }
});

// -----------------------------------------------------------------------------
// Test 20: Consultas SQL directas solo se ejecutan con prepare()
// -----------------------------------------------------------------------------

test('SQL directo requiere prepare', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    preg_match_all('/\$wpdb->query\s*\((.*?)\)\s*;/s', $fc, $matches);
    assert_true(count($matches[0]) >= 1, "se esperaba al menos una query directa conocida");
    foreach ($matches[1] as $queryCall) {
        assert_true(str_contains($queryCall, '$wpdb->prepare('),
            "hay una llamada a \$wpdb->query() sin \$wpdb->prepare()");
    }
    assert_false((bool) preg_match('/\$wpdb->(?:get_results|get_row|get_col|get_var|delete|update|insert|replace)\s*\(/', $fc),
        "hay otro acceso SQL directo no auditado");
});

// -----------------------------------------------------------------------------
// Test 21: Shortcodes limitan entradas numericas y ordenacion
// -----------------------------------------------------------------------------

test('Shortcodes limitan atributos de consulta', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_contains("'posts_per_page' => (int) \$atts['count']", $fc,
        "edu_recent_posts debe castear count a int");
    assert_contains("'posts_per_page' => max( 1, (int) \$atts['count'] )", $fc,
        "edu_latest_post debe limitar count a minimo 1");
    assert_contains("'orderby'        => sanitize_key( \$atts['orderby'] )", $fc,
        "orderby debe pasar por sanitize_key()");
    assert_contains("in_array( \$atts['img_position'], array( 'left', 'right', 'up', 'down' ), true )", $fc,
        "img_position debe tener allowlist estricta");
});

// -----------------------------------------------------------------------------
// Test 22: Customizer sanitiza todos los settings
// -----------------------------------------------------------------------------

test('Customizer tiene sanitize_callback en cada setting', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_matches("/add_setting\\s*\\(\\s*'edu_social_'\\s*\\.\\s*\\\$key\\s*,\\s*array\\s*\\(.*?'sanitize_callback'\\s*=>\\s*'esc_url_raw'/s", $fc,
        "redes sociales deben sanitizar URLs con esc_url_raw");
    assert_matches("/add_setting\\s*\\(\\s*'hero_bg_image'\\s*,\\s*array\\s*\\(.*?'sanitize_callback'\\s*=>\\s*'esc_url_raw'/s", $fc,
        "hero_bg_image debe sanitizar URL con esc_url_raw");
    assert_matches("/add_setting\\s*\\(\\s*'hero_sub_text'\\s*,\\s*array\\s*\\(.*?'sanitize_callback'\\s*=>\\s*'sanitize_text_field'/s", $fc,
        "hero_sub_text debe sanitizar texto plano");
    assert_matches("/add_setting\\s*\\(\\s*'hero_cred_text'\\s*,\\s*array\\s*\\(.*?'sanitize_callback'\\s*=>\\s*'wp_kses_post'/s", $fc,
        "hero_cred_text debe permitir solo HTML seguro");
});

// -----------------------------------------------------------------------------
// Test 23: HTML de audio e imagenes construidas a mano escapa src
// -----------------------------------------------------------------------------

test('HTML media construido a mano escapa src', function() use ($themeDir) {
    $fc = file_get_contents($themeDir . '/functions.php');
    assert_false(str_contains($fc, '<source src="\' . $'),
        "source src construido a mano no debe concatenar variables crudas");
    assert_false(str_contains($fc, '<img src="\' . $'),
        "img src construido a mano no debe concatenar variables crudas");
    assert_contains('<source src="\' . esc_url( $pp_url ) . \'">', $fc,
        "fallback Powerpress debe escapar URL");
    assert_contains('<source src="\' . esc_url( $enc_url ) . \'">', $fc,
        "enclosure debe escapar URL");
    assert_contains('<img src="\' . esc_url( $matches[1] ) . \'"', $fc,
        "preview image debe escapar URL");
});
