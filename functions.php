<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function edu_theme_setup() {
	load_theme_textdomain( 'edu-theme', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );

	add_image_size( 'edu-card', 640, 360, true );
	add_image_size( 'edu-hero', 1200, 500, true );
	add_image_size( 'edu-thumb', 80, 80, true );

	register_nav_menus( array(
		'primary' => __( 'Menú principal', 'edu-theme' ),
		'footer'  => __( 'Menú footer', 'edu-theme' ),
	) );
}
add_action( 'after_setup_theme', 'edu_theme_setup' );

function edu_theme_content_width() {
	$GLOBALS['content_width'] = 800;
}
add_action( 'after_setup_theme', 'edu_theme_content_width', 0 );

function edu_theme_scripts() {
	$style_version  = file_exists( get_stylesheet_directory() . '/style.css' )
		? filemtime( get_stylesheet_directory() . '/style.css' )
		: '1.0.0';
	$script_version = file_exists( get_template_directory() . '/assets/js/theme.js' )
		? filemtime( get_template_directory() . '/assets/js/theme.js' )
		: '1.0.0';

	wp_enqueue_style(
		'edu-theme-style',
		get_stylesheet_uri(),
		array(),
		$style_version
	);

	wp_enqueue_script(
		'edu-theme-js',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		$script_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'edu_theme_scripts' );

function edu_strip_leading_escaped_newlines( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	$html = preg_replace(
		'/(<meta\s+name="nocatbasewpruntime\d+-wpversion"[^>]*>)\\\\n(<script>window\.nocatbasewpruntime\d+_wpversion = true;<\/script>)\\\\n/i',
		"$1\n$2\n",
		$html,
		1
	);

	return preg_replace(
		'/(<body\b[^>]*>\s*)(?:(?:\\\\r)?\\\\n\s*)+/i',
		'$1',
		$html,
		1
	);
}

function edu_start_output_buffer() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_robots() || is_trackback() ) {
		return;
	}

	ob_start( 'edu_strip_leading_escaped_newlines' );
}
add_action( 'template_redirect', 'edu_start_output_buffer', 0 );

function edu_theme_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar del blog', 'edu-theme' ),
		'id'            => 'sidebar-blog',
		'description'   => __( 'Widgets para el sidebar del blog.', 'edu-theme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<p class="widget-title">',
		'after_title'   => '</p>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer', 'edu-theme' ),
		'id'            => 'sidebar-footer',
		'description'   => __( 'Widgets del footer.', 'edu-theme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<p class="widget-title">',
		'after_title'   => '</p>',
	) );
}
add_action( 'widgets_init', 'edu_theme_widgets_init' );

function edu_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'edu_excerpt_length', 999 );

function edu_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'edu_excerpt_more' );

function edu_is_podcast( $post_id = null ) {
	if ( ! $post_id ) $post_id = get_the_ID();
	return has_category( 'podcast', $post_id ) || has_category( 'Podcast', $post_id );
}

function edu_get_post_categories_html( $post_id = null ) {
	if ( ! $post_id ) $post_id = get_the_ID();
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) ) return '';
	$out = '';
	foreach ( $cats as $cat ) {
		$out .= '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '" class="post-tag post-tag--cyan">' . esc_html( $cat->name ) . '</a> ';
	}
	return trim( $out );
}

function edu_split_title( $title ) {
	$parts = explode( ' ', trim( $title ), 2 );
	if ( count( $parts ) === 1 ) {
		return esc_html( $parts[0] );
	}
	return esc_html( $parts[0] ) . ' <span class="title-rest">' . esc_html( $parts[1] ) . '</span>';
}

function edu_get_audio_player_html( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	// 1. Powerpress: URL en powerpress_link_0 (o con prefijo _)
	$pp_url = '';
	for ( $i = 0; $i <= 2; $i++ ) {
		$pp_url = get_post_meta( $post_id, 'powerpress_link_' . $i, true );
		if ( ! $pp_url ) {
			$pp_url = get_post_meta( $post_id, '_powerpress_link_' . $i, true );
		}
		if ( $pp_url ) break;
	}

	if ( $pp_url ) {
		// Usar el shortcode de Powerpress si está disponible (renderiza su propio player)
		if ( shortcode_exists( 'powerpress' ) ) {
			global $post;
			$prev_post = $post;
			$post       = get_post( $post_id ); // phpcs:ignore
			setup_postdata( $post );
			$player = do_shortcode( '[powerpress]' );
			$post   = $prev_post; // phpcs:ignore
			wp_reset_postdata();
			if ( $player ) return $player;
		}
		// Fallback: audio nativo con la URL de Powerpress
		return '<audio controls preload="none" class="edu-audio-player"><source src="' . esc_url( $pp_url ) . '"></audio>';
	}

	// 2. Enclosure estándar de WordPress
	$enclosure = get_post_meta( $post_id, 'enclosure', true );
	if ( $enclosure ) {
		$enc_url = trim( explode( "\n", $enclosure )[0] );
		if ( $enc_url && preg_match( '/\.(mp3|m4a|ogg|wav|aac|flac)(\?|$)/i', $enc_url ) ) {
			return '<audio controls preload="none" class="edu-audio-player"><source src="' . esc_url( $enc_url ) . '"></audio>';
		}
	}

	// 3. Campos personalizados genéricos
	foreach ( array( 'audio_url', 'audio_file', 'mp3_url', 'podcast_url' ) as $key ) {
		$meta_url = get_post_meta( $post_id, $key, true );
		if ( $meta_url ) {
			return '<audio controls preload="none" class="edu-audio-player"><source src="' . esc_url( $meta_url ) . '"></audio>';
		}
	}

	return '';
}

function edu_get_post_preview_image_html( $post_id = null, $size = 'thumbnail' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, array( 'alt' => '' ) );
	}

	$content = get_post_field( 'post_content', $post_id );
	if ( ! $content ) {
		return '';
	}

	preg_match( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );

	if ( empty( $matches[1] ) ) {
		return '';
	}

	return '<img src="' . esc_url( $matches[1] ) . '" alt="" loading="lazy">';
}

function edu_is_supported_social_image_mime_type( $mime_type ) {
	return in_array( $mime_type, array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ), true );
}

function edu_normalize_social_image_url( $image_url ) {
	$image_url = trim( (string) $image_url );

	if ( ! $image_url ) {
		return '';
	}

	if ( 0 === strpos( $image_url, '//' ) ) {
		return set_url_scheme( $image_url, is_ssl() ? 'https' : 'http' );
	}

	if ( 0 === strpos( $image_url, '/' ) ) {
		return home_url( $image_url );
	}

	return $image_url;
}

function edu_get_supported_social_image_data_from_attachment( $attachment_id ) {
	$attachment_id = (int) $attachment_id;

	if ( ! $attachment_id ) {
		return array();
	}

	$mime_type = get_post_mime_type( $attachment_id );

	if ( edu_is_supported_social_image_mime_type( $mime_type ) ) {
		$image_data = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( ! empty( $image_data[0] ) ) {
			$served_ext = strtolower( pathinfo( wp_parse_url( $image_data[0], PHP_URL_PATH ), PATHINFO_EXTENSION ) );

			// Plugins (ShortPixel, Imagify…) rewrite URLs to AVIF/WebP.
			// Mastodon and many social crawlers don't support AVIF previews,
			// so fall back to the original pre-conversion file when needed.
			if ( 'avif' === $served_ext ) {
				// Try the pre-conversion original (only useful when a plugin
				// rewrote a JPEG→AVIF; if the original is also AVIF, skip it).
				$original_url = function_exists( 'wp_get_original_image_url' )
					? wp_get_original_image_url( $attachment_id )
					: '';

				$original_ext = $original_url
					? strtolower( pathinfo( wp_parse_url( $original_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) )
					: '';

				if ( $original_url && 'avif' !== $original_ext ) {
					$meta = wp_get_attachment_metadata( $attachment_id );
					return array(
						'url'    => $original_url,
						'width'  => ! empty( $meta['width'] ) ? (int) $meta['width'] : 0,
						'height' => ! empty( $meta['height'] ) ? (int) $meta['height'] : 0,
					);
				}
				// Original is also AVIF (uploaded as AVIF) → fall through to
				// sizes loop, which will find nothing → no og:image output.
			} else {
				return array(
					'url'    => $image_data[0],
					'width'  => ! empty( $image_data[1] ) ? (int) $image_data[1] : 0,
					'height' => ! empty( $image_data[2] ) ? (int) $image_data[2] : 0,
				);
			}
		}
	}

	$metadata = wp_get_attachment_metadata( $attachment_id );
	$uploads  = wp_get_upload_dir();

	if ( empty( $metadata['file'] ) || empty( $metadata['sizes'] ) || empty( $uploads['baseurl'] ) ) {
		return array();
	}

	$relative_dir = dirname( $metadata['file'] );
	$relative_dir = '.' === $relative_dir ? '' : trim( $relative_dir, '/' );
	$candidates   = array();

	foreach ( $metadata['sizes'] as $size ) {
		if ( empty( $size['file'] ) ) {
			continue;
		}

		$size_mime_type = ! empty( $size['mime-type'] ) ? $size['mime-type'] : '';

		if ( ! $size_mime_type ) {
			$filetype       = wp_check_filetype( $size['file'] );
			$size_mime_type = ! empty( $filetype['type'] ) ? $filetype['type'] : '';
		}

		if ( ! edu_is_supported_social_image_mime_type( $size_mime_type ) ) {
			continue;
		}

		$image_url = trailingslashit( $uploads['baseurl'] );

		if ( $relative_dir ) {
			$image_url .= trailingslashit( $relative_dir );
		}

		$image_url .= ltrim( $size['file'], '/' );

		$candidates[] = array(
			'url'    => $image_url,
			'width'  => ! empty( $size['width'] ) ? (int) $size['width'] : 0,
			'height' => ! empty( $size['height'] ) ? (int) $size['height'] : 0,
		);
	}

	if ( empty( $candidates ) ) {
		return array();
	}

	usort(
		$candidates,
		static function ( $left, $right ) {
			$left_area  = (int) $left['width'] * (int) $left['height'];
			$right_area = (int) $right['width'] * (int) $right['height'];

			return $right_area <=> $left_area;
		}
	);

	return $candidates[0];
}

function edu_get_supported_social_image_data_from_url( $image_url ) {
	$image_url = edu_normalize_social_image_url( $image_url );

	if ( ! $image_url ) {
		return array();
	}

	$attachment_id = attachment_url_to_postid( $image_url );

	if ( $attachment_id ) {
		$image_data = edu_get_supported_social_image_data_from_attachment( $attachment_id );

		if ( ! empty( $image_data['url'] ) ) {
			return $image_data;
		}
	}

	$image_path = wp_parse_url( $image_url, PHP_URL_PATH );
	$filetype   = $image_path ? wp_check_filetype( $image_path ) : array();
	$mime_type  = ! empty( $filetype['type'] ) ? $filetype['type'] : '';

	if ( ! edu_is_supported_social_image_mime_type( $mime_type ) ) {
		return array();
	}

	return array(
		'url'    => $image_url,
		'width'  => 0,
		'height' => 0,
	);
}

function edu_get_social_share_image_data( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_queried_object_id();
	}

	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$image_data   = edu_get_supported_social_image_data_from_attachment( $thumbnail_id );

		if ( ! empty( $image_data['url'] ) ) {
			return $image_data;
		}
	}

	if ( $post_id ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( $content && preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $image_url ) {
				$image_data = edu_get_supported_social_image_data_from_url( $image_url );

				if ( ! empty( $image_data['url'] ) ) {
					return $image_data;
				}
			}
		}
	}

	$site_icon_id = (int) get_option( 'site_icon' );

	if ( $site_icon_id ) {
		$image_data = edu_get_supported_social_image_data_from_attachment( $site_icon_id );

		if ( ! empty( $image_data['url'] ) ) {
			return $image_data;
		}
	}

	$site_icon_url = get_site_icon_url( 512 );

	if ( $site_icon_url ) {
		return edu_get_supported_social_image_data_from_url( $site_icon_url );
	}

	return array();
}

function edu_get_social_share_description( $post_id = 0 ) {
	if ( $post_id ) {
		$excerpt = get_the_excerpt( $post_id );

		if ( $excerpt ) {
			return trim( wp_strip_all_tags( $excerpt ) );
		}

		$content = get_post_field( 'post_content', $post_id );

		if ( $content ) {
			$content = strip_shortcodes( $content );
			$content = preg_replace( '/\s+/', ' ', wp_strip_all_tags( $content ) );
			$content = trim( $content );

			if ( $content ) {
				return wp_trim_words( $content, 30, '...' );
			}
		}
	}

	return trim( wp_strip_all_tags( get_bloginfo( 'description', 'display' ) ) );
}

function edu_output_social_meta_tags() {
	if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
		return;
	}

	if (
		defined( 'WPSEO_VERSION' ) ||
		defined( 'RANK_MATH_VERSION' ) ||
		defined( 'AIOSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' )
	) {
		return;
	}

	$post_id          = is_singular() ? get_queried_object_id() : 0;
	$title            = '';
	$description      = '';
	$url              = '';
	$type             = 'website';
	$archive_img_only = false; // archives use site icon as og:image fallback

	if ( $post_id ) {
		$title       = get_the_title( $post_id );
		$description = edu_get_social_share_description( $post_id );
		$url         = wp_get_canonical_url( $post_id );
		$type        = 'post' === get_post_type( $post_id ) ? 'article' : 'website';

		if ( ! $url ) {
			$url = get_permalink( $post_id );
		}
	} elseif ( is_home() || is_front_page() ) {
		$title       = get_bloginfo( 'name', 'display' );
		$description = edu_get_social_share_description();
		$url         = home_url( '/' );
	} elseif ( is_category() ) {
		$term             = get_queried_object();
		$title            = single_cat_title( '', false );
		$desc_raw         = category_description();
		$description      = $desc_raw ? wp_strip_all_tags( $desc_raw ) : edu_get_social_share_description();
		$link             = get_term_link( $term );
		$url              = ! is_wp_error( $link ) ? $link : home_url( '/' );
		$archive_img_only = true;
	} elseif ( is_tag() ) {
		$term             = get_queried_object();
		$title            = single_tag_title( '', false );
		$desc_raw         = tag_description();
		$description      = $desc_raw ? wp_strip_all_tags( $desc_raw ) : edu_get_social_share_description();
		$link             = get_term_link( $term );
		$url              = ! is_wp_error( $link ) ? $link : home_url( '/' );
		$archive_img_only = true;
	} elseif ( is_author() ) {
		$author           = get_queried_object();
		$title            = get_the_author_meta( 'display_name', $author->ID );
		$bio              = get_the_author_meta( 'description', $author->ID );
		$description      = $bio ? wp_strip_all_tags( $bio ) : edu_get_social_share_description();
		$url              = get_author_posts_url( $author->ID );
		$archive_img_only = true;
	} else {
		return;
	}

	$image = edu_get_social_share_image_data( $archive_img_only ? 0 : $post_id );

	if ( ! $title ) {
		$title = wp_get_document_title();
	}

	if ( ! $description ) {
		$description = $title;
	}

	if ( ! $url ) {
		$url = home_url( '/' );
	}

	// og:image:alt — use attachment alt text or fall back to page title
	$image_alt = '';
	if ( ! empty( $image['url'] ) ) {
		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$thumb_id  = get_post_thumbnail_id( $post_id );
			$image_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );
		}
		if ( ! $image_alt ) {
			$image_alt = $title;
		}
	}
	?>
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
	<meta property="og:locale" content="<?php echo esc_attr( get_locale() ); ?>">
	<meta property="og:type" content="<?php echo esc_attr( $type ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta name="twitter:card" content="<?php echo ! empty( $image['url'] ) ? 'summary_large_image' : 'summary'; ?>">
	<meta name="twitter:site" content="@ecollado">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
	<?php if ( ! empty( $image['url'] ) ) : ?>
		<?php
		$img_ext  = strtolower( pathinfo( wp_parse_url( $image['url'], PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		$img_mime = array(
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'webp' => 'image/webp',
			'avif' => 'image/avif',
		);
		$img_type = ! empty( $img_mime[ $img_ext ] ) ? $img_mime[ $img_ext ] : '';
		?>
		<meta property="og:image" content="<?php echo esc_url( $image['url'] ); ?>">
		<meta property="og:image:alt" content="<?php echo esc_attr( $image_alt ); ?>">
		<?php if ( $img_type ) : ?>
			<meta property="og:image:type" content="<?php echo esc_attr( $img_type ); ?>">
		<?php endif; ?>
		<meta name="twitter:image" content="<?php echo esc_url( $image['url'] ); ?>">
		<?php if ( ! empty( $image['width'] ) ) : ?>
			<meta property="og:image:width" content="<?php echo (int) $image['width']; ?>">
		<?php endif; ?>
		<?php if ( ! empty( $image['height'] ) ) : ?>
			<meta property="og:image:height" content="<?php echo (int) $image['height']; ?>">
		<?php endif; ?>
	<?php endif; ?>
	<?php if ( $post_id && 'article' === $type ) : ?>
		<meta property="article:published_time" content="<?php echo esc_attr( get_post_time( DATE_W3C, true, $post_id ) ); ?>">
		<meta property="article:modified_time" content="<?php echo esc_attr( get_post_modified_time( DATE_W3C, true, $post_id ) ); ?>">
	<?php endif; ?>
	<?php
}
add_action( 'wp_head', 'edu_output_social_meta_tags', 5 );

function edu_output_canonical() {
	if ( is_admin() || is_feed() || is_robots() || is_trackback() || is_singular() ) {
		return;
	}

	if (
		defined( 'WPSEO_VERSION' ) ||
		defined( 'RANK_MATH_VERSION' ) ||
		defined( 'AIOSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' )
	) {
		return;
	}

	$canonical = '';
	$paged     = (int) get_query_var( 'paged' );

	if ( is_front_page() || is_home() ) {
		$canonical = $paged > 1 ? get_pagenum_link( $paged ) : home_url( '/' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$canonical = $paged > 1 ? get_pagenum_link( $paged ) : $link;
			}
		}
	} elseif ( is_author() ) {
		$author = get_queried_object();
		if ( $author ) {
			$base      = get_author_posts_url( $author->ID );
			$canonical = $paged > 1 ? get_pagenum_link( $paged ) : $base;
		}
	}

	if ( $canonical ) {
		echo "\t" . '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'edu_output_canonical', 1 );

function edu_output_json_ld() {
	if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
		return;
	}

	if (
		defined( 'WPSEO_VERSION' ) ||
		defined( 'RANK_MATH_VERSION' ) ||
		defined( 'AIOSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' )
	) {
		return;
	}

	$site_name = get_bloginfo( 'name', 'display' );
	$site_url  = home_url( '/' );
	$graph     = array();
	$author    = array(
		'@type' => 'Person',
		'name'  => 'Eduardo Collado',
		'url'   => $site_url,
	);

	if ( is_front_page() || is_home() ) {
		$graph[] = array(
			'@type'           => 'WebSite',
			'name'            => $site_name,
			'url'             => $site_url,
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => $site_url . '?s={search_term_string}',
				),
				'query-input' => 'required name=search_term_string',
			),
		);
		$graph[] = array_merge( $author, array( 'sameAs' => array( 'https://twitter.com/ecollado' ) ) );

	} elseif ( is_singular() ) {
		$post_id = get_queried_object_id();
		$post    = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$desc     = edu_get_social_share_description( $post_id );
		$url      = wp_get_canonical_url( $post_id ) ?: get_permalink( $post_id );
		$pub      = get_post_time( DATE_W3C, true, $post );
		$mod      = get_post_modified_time( DATE_W3C, true, $post );
		$img_data = edu_get_social_share_image_data( $post_id );
		$img_url  = ! empty( $img_data['url'] ) ? $img_data['url'] : '';

		if ( edu_is_podcast( $post_id ) ) {
			$schema = array(
				'@type'         => 'PodcastEpisode',
				'name'          => get_the_title( $post_id ),
				'description'   => $desc,
				'url'           => $url,
				'datePublished' => $pub,
				'author'        => $author,
			);
		} else {
			$schema = array(
				'@type'            => 'BlogPosting',
				'headline'         => get_the_title( $post_id ),
				'description'      => $desc,
				'url'              => $url,
				'datePublished'    => $pub,
				'dateModified'     => $mod,
				'author'           => $author,
				'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => $url ),
			);
		}

		if ( $img_url ) {
			$schema['image'] = $img_url;
		}

		$graph[] = $schema;

	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$desc    = wp_strip_all_tags( term_description( $term ) );
				$desc    = $desc ?: edu_get_social_share_description();
				$graph[] = array(
					'@type'       => 'CollectionPage',
					'name'        => single_term_title( '', false ),
					'description' => $desc,
					'url'         => $link,
				);
			}
		}
	}

	if ( empty( $graph ) ) {
		return;
	}

	$ld = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo "\t" . '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'edu_output_json_ld', 6 );

// Shortcode: [edu_recent_posts count="5" title="Últimas entradas" category="" orderby="date"]
function edu_recent_posts_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'count'   => 5,
		'title'   => '',
		'category'=> '',
		'orderby' => 'date',
	), $atts, 'edu_recent_posts' );

	$args = array(
		'posts_per_page' => (int) $atts['count'],
		'post_status'    => 'publish',
		'orderby'        => sanitize_key( $atts['orderby'] ),
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);

	if ( ! empty( $atts['category'] ) ) {
		if ( is_numeric( $atts['category'] ) ) {
			$args['cat'] = (int) $atts['category'];
		} else {
			$args['category_name'] = sanitize_text_field( $atts['category'] );
		}
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<div class="widget">
		<?php if ( $atts['title'] ) : ?>
			<h2 class="section-heading"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>
		<ul class="sidebar-posts-list">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php $thumb_html = edu_get_post_preview_image_html( get_the_ID(), 'thumbnail' ); ?>
				<li class="sidebar-post-item">
					<?php if ( $thumb_html ) : ?>
						<a href="<?php the_permalink(); ?>" class="sidebar-post-thumb" tabindex="-1" aria-hidden="true">
							<?php echo $thumb_html; ?>
						</a>
					<?php endif; ?>
					<div class="sidebar-post-info">
						<a href="<?php the_permalink(); ?>" class="sidebar-post-title"><?php the_title(); ?></a>
						<span class="sidebar-post-date"><?php echo esc_html( get_the_date() ); ?></span>
					</div>
				</li>
			<?php endwhile; ?>
		</ul>
	</div>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'edu_recent_posts', 'edu_recent_posts_shortcode' );

// Shortcode: [edu_latest_post title="" category="" count="1"]
function edu_latest_post_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'title'    => '',
		'category' => '',
		'count'    => 1,
	), $atts, 'edu_latest_post' );

	$cache_key = 'edu_latest_post_v2_' . md5( serialize( $atts ) );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$args = array(
		'posts_per_page' => max( 1, (int) $atts['count'] ),
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);

	if ( ! empty( $atts['category'] ) ) {
		if ( is_numeric( $atts['category'] ) ) {
			$args['cat'] = (int) $atts['category'];
		} else {
			$args['category_name'] = sanitize_text_field( $atts['category'] );
		}
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$posts = $query->posts;

	ob_start();
	?>
	<div class="edu-post-listing">
		<?php if ( $atts['title'] ) : ?>
			<h2 class="edu-post-listing__heading"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>

		<?php
		$index = 0;
		foreach ( $posts as $post ) :
			setup_postdata( $post );
			$post_id        = $post->ID;
			$thumb_html     = edu_get_post_preview_image_html( $post_id, $index === 0 ? 'edu-hero' : 'edu-card' );
			$player_html    = edu_get_audio_player_html( $post_id );
			$is_podcast     = edu_is_podcast( $post_id );
			$categories     = get_the_category( $post_id );
			$category_label = $categories ? $categories[0]->name : __( 'Entrada', 'edu-theme' );
			$cta_text       = $player_html ? __( 'Escuchar', 'edu-theme' ) : __( 'Leer', 'edu-theme' );
			$excerpt        = get_the_excerpt();
			$permalink      = get_permalink( $post_id );

			if ( $index === 0 ) :
		?>
		<article class="edu-post-listing__featured<?php echo $thumb_html ? ' edu-post-listing__featured--split' : ''; ?><?php echo $is_podcast ? ' is-podcast' : ''; ?>">
			<?php if ( $thumb_html ) : ?>
				<div class="edu-post-listing__media">
					<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
						<?php echo $thumb_html; ?>
					</a>
				</div>
			<?php endif; ?>
			<div class="edu-post-listing__body">
				<div class="edu-post-listing__meta">
					<span class="edu-post-listing__cat<?php echo $is_podcast ? ' is-podcast' : ''; ?>"><?php echo esc_html( $category_label ); ?></span>
					<span class="edu-post-listing__date"><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
				</div>
				<h3 class="edu-post-listing__title edu-post-listing__title--featured">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
				</h3>
				<?php if ( $excerpt ) : ?>
					<p class="edu-post-listing__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
				<?php if ( $player_html ) : ?>
					<div class="edu-post-listing__player"><?php echo $player_html; ?></div>
				<?php endif; ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__cta"><?php echo esc_html( $cta_text ); ?> &rarr;</a>
			</div>
		</article>

		<?php
			elseif ( $index === 1 ) :
		?>
		<div class="edu-post-listing__grid">
		<?php endif; ?>

		<?php if ( $index >= 1 ) : ?>
			<article class="edu-post-listing__item<?php echo $is_podcast ? ' is-podcast' : ''; ?>">
				<?php if ( $thumb_html ) : ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
						<?php echo $thumb_html; ?>
					</a>
				<?php endif; ?>
				<div class="edu-post-listing__body">
					<div class="edu-post-listing__meta">
						<span class="edu-post-listing__cat<?php echo $is_podcast ? ' is-podcast' : ''; ?>"><?php echo esc_html( $category_label ); ?></span>
						<span class="edu-post-listing__date"><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
					</div>
					<h3 class="edu-post-listing__title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
					</h3>
					<?php if ( $excerpt ) : ?>
						<p class="edu-post-listing__excerpt edu-post-listing__excerpt--small"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<?php if ( $player_html ) : ?>
						<div class="edu-post-listing__player edu-post-listing__player--small"><?php echo $player_html; ?></div>
					<?php endif; ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__cta"><?php echo esc_html( $cta_text ); ?> &rarr;</a>
				</div>
			</article>
		<?php endif; ?>

		<?php $index++; endforeach; ?>

		<?php if ( count( $posts ) > 1 ) : ?>
		</div><!-- .edu-post-listing__grid -->
		<?php endif; ?>
	</div><!-- .edu-post-listing -->
	<?php
	wp_reset_postdata();
	$html = ob_get_clean();
	set_transient( $cache_key, $html, HOUR_IN_SECONDS * 6 );
	return $html;
}
add_shortcode( 'edu_latest_post', 'edu_latest_post_shortcode' );

// Shortcode: [edu_dos_columnas]...[/edu_dos_columnas]
// Uso: envuelve dos bloques [edu_col]...[/edu_col] para mostrarlos en dos columnas.
function edu_dos_columnas_shortcode( $atts, $content = '' ) {
	// wpautop inserta <p> y <br> entre los [edu_col] internos, rompiendo el grid.
	// Los eliminamos antes de procesar los shortcodes hijos.
	$content = preg_replace( '/<p>\s*<\/p>/i', '', $content );
	$content = preg_replace( '/<br\s*\/?>/i', '', $content );
	return '<div class="edu-dos-columnas">' . do_shortcode( trim( $content ) ) . '</div>';
}
add_shortcode( 'edu_dos_columnas', 'edu_dos_columnas_shortcode' );

// Shortcode interno: [edu_col]...[/edu_col]
// Cada uno representa una columna dentro de [edu_dos_columnas].
function edu_col_shortcode( $atts, $content = '' ) {
	return '<div class="edu-dos-columnas__col">' . do_shortcode( wpautop( $content ) ) . '</div>';
}
add_shortcode( 'edu_col', 'edu_col_shortcode' );

// Shortcode: [edu_latest_audio cat="308" title="Último Audio del Podcast" img_position="right|left|up|down"]
function edu_latest_audio_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'cat'          => '',
		'title'        => '',
		'img_position' => 'right',
	), $atts, 'edu_latest_audio' );
	$img_pos = in_array( $atts['img_position'], array( 'left', 'right', 'up', 'down' ), true )
		? $atts['img_position']
		: 'right';

	$cache_key = 'edu_latest_audio_v2_' . md5( serialize( $atts ) );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$args = array(
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'post_type'      => 'any',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);

	if ( ! empty( $atts['cat'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field'    => is_numeric( $atts['cat'] ) ? 'term_id' : 'slug',
				'terms'    => is_numeric( $atts['cat'] ) ? (int) $atts['cat'] : sanitize_text_field( $atts['cat'] ),
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$query->the_post();
	$post_id        = get_the_ID();
	$post_title     = get_the_title();
	$permalink      = get_permalink();
	$thumb_html     = edu_get_post_preview_image_html( $post_id, 'edu-hero' );
	$player_html    = edu_get_audio_player_html( $post_id );
	$excerpt        = get_the_excerpt();
	$categories     = get_the_category( $post_id );
	$category_label = $categories ? $categories[0]->name : '';
	$post_date      = get_the_date( '', $post_id );
	wp_reset_postdata();

	$article_class = 'edu-post-listing__featured is-podcast edu-post-listing__featured--img-' . $img_pos;
	$has_side_media = $thumb_html && in_array( $img_pos, array( 'left', 'right' ), true );

	ob_start();
	?>
	<div class="edu-post-listing edu-post-listing--compact">
		<?php if ( $atts['title'] ) : ?>
			<h2 class="edu-post-listing__heading"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>
		<article class="<?php echo esc_attr( $article_class ); ?>">
			<?php if ( $has_side_media ) : ?>
				<div class="edu-post-listing__media">
					<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
						<?php echo $thumb_html; ?>
					</a>
				</div>
			<?php elseif ( $thumb_html ) : ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
					<?php echo $thumb_html; ?>
				</a>
			<?php endif; ?>
			<div class="edu-post-listing__body">
				<div class="edu-post-listing__meta">
					<?php if ( $category_label ) : ?>
						<span class="edu-post-listing__cat is-podcast"><?php echo esc_html( $category_label ); ?></span>
					<?php endif; ?>
					<span class="edu-post-listing__date"><?php echo esc_html( $post_date ); ?></span>
				</div>
				<h3 class="edu-post-listing__title edu-post-listing__title--featured">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $post_title ); ?></a>
				</h3>
				<?php if ( $excerpt ) : ?>
					<p class="edu-post-listing__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
				<?php if ( $player_html ) : ?>
					<div class="edu-post-listing__player"><?php echo $player_html; ?></div>
				<?php endif; ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__cta"><?php esc_html_e( 'Escuchar', 'edu-theme' ); ?> &rarr;</a>
			</div>
		</article>
	</div>
	<?php
	$html = ob_get_clean();
	set_transient( $cache_key, $html, HOUR_IN_SECONDS * 6 );
	return $html;
}
add_shortcode( 'edu_latest_audio', 'edu_latest_audio_shortcode' );

// Shortcode: [edu_latest_article cat="" title="" img_position="right|left|up|down"]
function edu_latest_article_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'cat'          => '',
		'title'        => '',
		'img_position' => 'right',
	), $atts, 'edu_latest_article' );
	$img_pos = in_array( $atts['img_position'], array( 'left', 'right', 'up', 'down' ), true )
		? $atts['img_position']
		: 'right';

	$cache_key = 'edu_latest_article_v2_' . md5( serialize( $atts ) );
	$cached    = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$args = array(
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);

	if ( ! empty( $atts['cat'] ) ) {
		if ( is_numeric( $atts['cat'] ) ) {
			$args['cat'] = (int) $atts['cat'];
		} else {
			$args['category_name'] = sanitize_text_field( $atts['cat'] );
		}
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	$query->the_post();
	$post_id        = get_the_ID();
	$post_title     = get_the_title();
	$permalink      = get_permalink();
	$thumb_html     = edu_get_post_preview_image_html( $post_id, 'edu-hero' );
	$excerpt        = get_the_excerpt();
	$categories     = get_the_category( $post_id );
	$category_label = $categories ? $categories[0]->name : '';
	$post_date      = get_the_date( '', $post_id );
	wp_reset_postdata();

	$article_class = 'edu-post-listing__featured edu-post-listing__featured--img-' . $img_pos;
	$has_side_media = $thumb_html && in_array( $img_pos, array( 'left', 'right' ), true );

	ob_start();
	?>
	<div class="edu-post-listing edu-post-listing--compact">
		<?php if ( $atts['title'] ) : ?>
			<h2 class="edu-post-listing__heading"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>
		<article class="<?php echo esc_attr( $article_class ); ?>">
			<?php if ( $has_side_media ) : ?>
				<div class="edu-post-listing__media">
					<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
						<?php echo $thumb_html; ?>
					</a>
				</div>
			<?php elseif ( $thumb_html ) : ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__img" tabindex="-1" aria-hidden="true">
					<?php echo $thumb_html; ?>
				</a>
			<?php endif; ?>
			<div class="edu-post-listing__body">
				<div class="edu-post-listing__meta">
					<?php if ( $category_label ) : ?>
						<span class="edu-post-listing__cat"><?php echo esc_html( $category_label ); ?></span>
					<?php endif; ?>
					<span class="edu-post-listing__date"><?php echo esc_html( $post_date ); ?></span>
				</div>
				<h3 class="edu-post-listing__title edu-post-listing__title--featured">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $post_title ); ?></a>
				</h3>
				<?php if ( $excerpt ) : ?>
					<p class="edu-post-listing__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>
				<a href="<?php echo esc_url( $permalink ); ?>" class="edu-post-listing__cta"><?php esc_html_e( 'Leer', 'edu-theme' ); ?> &rarr;</a>
			</div>
		</article>
	</div>
	<?php
	$html = ob_get_clean();
	set_transient( $cache_key, $html, HOUR_IN_SECONDS * 6 );
	return $html;
}
add_shortcode( 'edu_latest_article', 'edu_latest_article_shortcode' );

function edu_delete_shortcode_transients() {
	if ( wp_using_ext_object_cache() ) {
		// Redis/Memcached: los transients viven en el object cache, no en la BD.
		// wp_cache_flush() los elimina; WP los regenera en la siguiente petición.
		wp_cache_flush();
	} else {
		global $wpdb;
		$prefix = $wpdb->esc_like( '_transient_edu_latest_' );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$prefix . '%',
			'_transient_timeout_edu_latest_%'
		) );
	}
}

function edu_clear_shortcode_transients( $post_id ) {
	// Ignorar autosaves y revisiones
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	edu_delete_shortcode_transients();

	// WP Super Cache
	if ( function_exists( 'wp_cache_post_change' ) ) {
		wp_cache_post_change( $post_id );
	}

	// Autoptimize
	if ( class_exists( 'autoptimizeCache' ) ) {
		autoptimizeCache::clearall();
	}
}
add_action( 'save_post', 'edu_clear_shortcode_transients' );
add_action( 'deleted_post', 'edu_clear_shortcode_transients' );

function edu_admin_bar_clear_transients( WP_Admin_Bar $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$url = add_query_arg( 'edu_clear_transients', '1', remove_query_arg( 'edu_clear_transients' ) );
	$wp_admin_bar->add_node( array(
		'id'    => 'edu-clear-transients',
		'title' => '🗑 Borrar transients',
		'href'  => wp_nonce_url( $url, 'edu_clear_transients' ),
	) );
}
add_action( 'admin_bar_menu', 'edu_admin_bar_clear_transients', 999 );

function edu_handle_clear_transients() {
	if ( ! isset( $_GET['edu_clear_transients'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'edu_clear_transients' );
	edu_delete_shortcode_transients();
	wp_safe_redirect( remove_query_arg( array( 'edu_clear_transients', '_wpnonce' ) ) );
	exit;
}
add_action( 'init', 'edu_handle_clear_transients' );

// ============================================================
// Redes Sociales — Personalizador, shortcode y widget
// ============================================================

function edu_social_customizer( $wp_customize ) {
	$wp_customize->add_section( 'edu_social', array(
		'title'    => __( 'Redes Sociales', 'edu-theme' ),
		'priority' => 130,
	) );

	$networks = array(
		'mastodon'  => 'Mastodon (ej: https://mastodon.social/@usuario)',
		'twitter'   => 'X / Twitter',
		'linkedin'  => 'LinkedIn',
		'github'    => 'GitHub',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'spotify'   => 'Spotify',
		'twitch'    => 'Twitch',
		'pinterest' => 'Pinterest',
		'goodreads' => 'Goodreads',
		'pixelfed'  => 'Pixelfed',
	);

	foreach ( $networks as $key => $label ) {
		$wp_customize->add_setting( 'edu_social_' . $key, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'edu_social_' . $key, array(
			'label'   => $label,
			'section' => 'edu_social',
			'type'    => 'url',
		) );
	}
}
add_action( 'customize_register', 'edu_social_customizer' );

function edu_hero_customizer( $wp_customize ) {
	$wp_customize->add_section( 'edu_hero', array(
		'title'    => __( 'Hero (portada)', 'edu-theme' ),
		'priority' => 125,
	) );

	$wp_customize->add_setting( 'hero_bg_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_bg_image', array(
		'label'   => __( 'Imagen de fondo', 'edu-theme' ),
		'section' => 'edu_hero',
	) ) );

	$wp_customize->add_setting( 'hero_sub_text', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'hero_sub_text', array(
		'label'   => __( 'Subtítulo', 'edu-theme' ),
		'section' => 'edu_hero',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'hero_cred_text', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'hero_cred_text', array(
		'label'       => __( 'Credenciales', 'edu-theme' ),
		'description' => __( 'HTML básico permitido. Ejemplo: Cofundador de &lt;a href="https://..."&gt;Tecnocrática&lt;/a&gt; · Infraestructura en producción', 'edu-theme' ),
		'section'     => 'edu_hero',
		'type'        => 'textarea',
	) );
}
add_action( 'customize_register', 'edu_hero_customizer' );

function edu_get_social_svg( $network ) {
	$svgs = array(
		'mastodon'  => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.61 7.837.661 9.143c.074 1.874.088 3.745.26 5.611.118 1.24.325 2.47.62 3.68.55 2.237 2.777 4.098 4.96 4.857 2.336.792 4.849.923 7.256.38.265-.061.527-.132.786-.213.585-.184 1.27-.39 1.774-.753a.057.057 0 0 0 .023-.043v-1.809a.052.052 0 0 0-.02-.041.053.053 0 0 0-.046-.01 20.282 20.282 0 0 1-4.709.545c-2.73 0-3.463-1.284-3.674-1.818a5.593 5.593 0 0 1-.319-1.433.053.053 0 0 1 .066-.054c1.517.363 3.072.546 4.632.546.376 0 .75 0 1.125-.01 1.57-.044 3.224-.124 4.768-.422.038-.008.077-.015.11-.024 2.435-.464 4.753-1.92 4.989-5.604.008-.145.03-1.52.03-1.67.002-.512.167-3.63-.024-5.545zm-3.748 9.195h-2.561V8.29c0-1.309-.55-1.976-1.67-1.976-1.23 0-1.846.79-1.846 2.35v3.403h-2.546V8.663c0-1.56-.617-2.35-1.848-2.35-1.112 0-1.668.668-1.67 1.977v6.218H4.822V8.102c0-1.31.337-2.35 1.011-3.12.696-.77 1.608-1.164 2.74-1.164 1.311 0 2.302.5 2.962 1.498l.638 1.06.638-1.06c.66-.999 1.65-1.498 2.96-1.498 1.13 0 2.043.395 2.74 1.164.675.77 1.012 1.81 1.012 3.12z"/></svg>',
		'twitter'   => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
		'github'    => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>',
		'spotify'   => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>',
		'twitch'    => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>',
		'pinterest' => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',
		'goodreads' => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M11.43 23.995c-3.608-.208-6.274-2.077-6.448-5.065.695.015 1.375-.013 2.07-.006.224 1.342 1.065 2.43 2.683 3.026 1.583.496 3.737.46 5.082-.174 2.218-1.168 3.182-3.763 3.02-5.834l-.004-.975c-1.07 1.671-3.249 2.498-5.373 2.498-4.222 0-7.498-3.176-7.498-7.842 0-4.894 3.07-8.405 7.577-8.405 2.1 0 4.053.928 5.135 2.682l.059-2.308h2.016c-.048 1.261-.097 2.405-.097 3.678v15.318c0 4.534-3.024 6.815-8.222 7.407zm.158-10.208c2.716 0 5.025-2.086 5.025-5.933 0-4.037-2.206-6.3-5.025-6.3-2.79 0-4.823 2.218-4.823 6.3 0 3.557 1.928 5.933 4.823 5.933z"/></svg>',
		'pixelfed'  => '<svg viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12.12 5.04c2.688 0 4.84 2.152 4.84 4.84 0 2.688-2.152 4.84-4.84 4.84H9.36v4.24H6.96V5.04h5.16zm0 2.32H9.36v5.04h2.76c1.376 0 2.52-1.128 2.52-2.52s-1.144-2.52-2.52-2.52zM12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2.4c5.302 0 9.6 4.298 9.6 9.6s-4.298 9.6-9.6 9.6S2.4 17.302 2.4 12 6.698 2.4 12 2.4z"/></svg>',
	);
	return isset( $svgs[ $network ] ) ? $svgs[ $network ] : '';
}

function edu_get_social_icons_html() {
	$networks = array(
		'mastodon'  => 'Mastodon',
		'twitter'   => 'X / Twitter',
		'linkedin'  => 'LinkedIn',
		'github'    => 'GitHub',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'spotify'   => 'Spotify',
		'twitch'    => 'Twitch',
		'pinterest' => 'Pinterest',
		'goodreads' => 'Goodreads',
		'pixelfed'  => 'Pixelfed',
	);

	$items = '';
	foreach ( $networks as $key => $label ) {
		$url = get_theme_mod( 'edu_social_' . $key, '' );
		if ( ! $url ) {
			continue;
		}
		$svg    = edu_get_social_svg( $key );
		$items .= sprintf(
			'<a href="%s" class="social-icon social-icon--%s" target="_blank" rel="noopener noreferrer me" aria-label="%s">%s</a>',
			esc_url( $url ),
			esc_attr( $key ),
			esc_attr( $label ),
			$svg
		);
	}

	if ( ! $items ) {
		return '';
	}
	return '<div class="social-icons">' . $items . '</div>';
}

function edu_social_icons_shortcode() {
	return edu_get_social_icons_html();
}
add_shortcode( 'edu_social_icons', 'edu_social_icons_shortcode' );

function edu_fediverse_creator_meta() {
	$url = get_theme_mod( 'edu_social_mastodon', '' );
	if ( ! $url ) {
		return;
	}
	$parsed = wp_parse_url( $url );
	if ( empty( $parsed['host'] ) || empty( $parsed['path'] ) ) {
		return;
	}
	$domain   = $parsed['host'];
	$username = ltrim( $parsed['path'], '/' ); // "@ecollado"
	if ( ! $username ) {
		return;
	}
	$handle = $username . '@' . $domain; // "@ecollado@mastodon.social"
	echo '<meta name="fediverse:creator" content="' . esc_attr( $handle ) . '">' . "\n";
}
add_action( 'wp_head', 'edu_fediverse_creator_meta' );

class Edu_Social_Icons_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'edu_social_icons',
			__( 'Edu — Iconos Sociales', 'edu-theme' ),
			array( 'description' => __( 'Muestra los iconos de redes sociales (configurados en el Personalizador).', 'edu-theme' ) )
		);
	}

	public function widget( $args, $instance ) {
		$html = edu_get_social_icons_html();
		if ( ! $html ) {
			return;
		}
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
		}
		echo $html;
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Título:', 'edu-theme' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p><em><?php esc_html_e( 'Las URLs se configuran en Apariencia → Personalizar → Redes Sociales.', 'edu-theme' ); ?></em></p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array( 'title' => sanitize_text_field( $new_instance['title'] ) );
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Edu_Social_Icons_Widget' );
} );

// ============================================================

class Edu_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '<ul class="nav__dropdown">';
		}
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $depth === 0 ) {
			$output .= '</ul>';
		}
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item = $data_object;
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes );

		if ( $depth === 0 && $has_children ) {
			$output .= '<li class="nav__item--dropdown">';
		} else {
			$output .= '<li>';
		}

		$atts = array();
		$atts['href'] = ! empty( $item->url ) ? $item->url : '';
		if ( ! empty( $item->target ) ) $atts['target'] = $item->target;
		if ( ! empty( $item->xfn ) )    $atts['rel']    = $item->xfn;

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value       = esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title   = apply_filters( 'the_title', $item->title, $item->ID );
		$output .= '<a' . $attributes . '>' . $title . '</a>';
	}

	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
