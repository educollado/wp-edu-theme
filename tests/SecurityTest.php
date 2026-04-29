<?php
// =============================================================================
// Tests de Seguridad para edu-theme
// =============================================================================

$themeDir = dirname(__DIR__);

// -----------------------------------------------------------------------------
// Test 1: Todos los archivos PHP tienen proteccion ABSPATH
// -----------------------------------------------------------------------------

test('Todos los archivos PHP tienen proteccion ABSPATH', function() use ($themeDir) {
    $phpFiles = glob($themeDir . '/*.php') + glob($themeDir . '/template-parts/*.php');
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
    $phpFiles = glob($themeDir . '/*.php') + glob($themeDir . '/template-parts/*.php');
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
    $phpFiles = glob($themeDir . '/*.php') + glob($themeDir . '/template-parts/*.php');
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
    $phpFiles = glob($themeDir . '/*.php') + glob($themeDir . '/template-parts/*.php');
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
    $phpFiles = glob($themeDir . '/*.php') + glob($themeDir . '/template-parts/*.php');
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
    $phpFiles = glob($themeDir . '/*.php') + glob($themeDir . '/template-parts/*.php');
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        $trimmed = ltrim($content);
        assert_true(str_starts_with($trimmed, '<?php'),
            basename($file) . " debe comenzar con <?php");
    }
});


