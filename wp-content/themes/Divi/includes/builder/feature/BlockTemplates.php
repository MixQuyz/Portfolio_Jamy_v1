<?php
/**
 * Block Templates Compatibility.
 *
 * @package Builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Block Templates Compatibility Class.
 *
 * @since 4.9.8
 */
class ET_Builder_Block_Templates {
	/**
	 * Instance of `ET_Builder_Block_Templates`.
	 *
	 * @var ET_Builder_Block_Templates
	 */
	private static $_instance;

	/**
	 * ET_Builder_Block_Templates constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Get the class instance.
	 *
	 * @since 4.9.8
	 *
	 * @return ET_Builder_Block_Templates
	 */
	public static function instance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize some hooks to support compatibility with block templates.
	 *
	 * @since ??
	 */
	public function init_hooks() {
		// Bail early if block templates compatibility is not needed.
		if ( ! self::is_block_templates_compat_needed() ) {
			return;
		}

		// Use the same priority as TB, so this hook can be fired right after that.
		add_action( 'template_include', array( $this, 'override_block_template' ), 98 );

		// Disable deperecated files warnings.
		add_action( 'deprecated_file_included', array( $this, 'disable_deprecated_file_warnings' ) );

		// WooCommerce compatibility for themes that support FSE.
		if ( et_is_woocommerce_plugin_active() ) {
			add_action( 'template_redirect', array( $this, 'remove_unsupported_theme_filter' ), 12 );
		}
	}

	/**
	 * Determine whether block templates compatibility support is needed or not.
	 *
	 * Support block templates compatibility only if:
	 * - DBP is active
	 * - Current WordPress or Gutenberg supports block templates
	 * - Current theme supports block templates
	 *
	 * @since ??
	 *
	 * @return boolean Compatibility status.
	 */
	public static function is_block_templates_compat_needed() {
		// Bail early if current active builder is not DBP.
		if ( ! et_is_builder_plugin_active() ) {
			return false;
		}

		// Bail early if `locate_block_template` function doesn't exists (WP 5.8 above).
		if ( ! function_exists( 'locate_block_template' ) ) {
			return false;
		}

		// Bail early if current theme doesn't support block templates.
		$is_theme_supports_block_templates = current_theme_supports( 'block-templates' );
		$has_block_templates_index         = false;

		// Use `wp_is_block_theme` on WP 5.9 or manual check on WP 5.8 below.
		if ( function_exists( 'wp_is_block_theme' ) ) {
			$has_block_templates_index = wp_is_block_theme();
		} else {
			$block_templates_index_html_file = get_stylesheet_directory() . '/block-templates/index.html';
			$templates_index_html_file       = get_stylesheet_directory() . '/templates/index.html';
			$has_block_templates_index       = is_readable( $block_templates_index_html_file ) || is_readable( $templates_index_html_file );
		}

		if ( ! $is_theme_supports_block_templates && ! $has_block_templates_index ) {
			return false;
		}

		return true;
	}

	/**
	 * Maybe override block template.
	 *
	 * This action should be executed only when:
	 * - TB Template is active on current page
	 * - Current template is block template canvas
	 *
	 * @since ??
	 *
	 * @param string $template Current template path.
	 */
	public function override_block_template( $template ) {
		// Bail early if there is no TB templates for current page request.
		if ( empty( et_theme_builder_get_template_layouts() ) ) {
			return $template;
		}

		$override_header = et_theme_builder_overrides_layout( ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE );
		$override_body   = et_theme_builder_overrides_layout( ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE );
		$override_footer = et_theme_builder_overrides_layout( ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE );

		// Bail early if TB doesn't override any layouts.
		if ( ! $override_header && ! $override_body && ! $override_footer ) {
			return $template;
		}

		$base_template = basename( $template );

		// 1. Enqueue block templates compatibility styles only if:
		// - Current template is `template-canvas.php` to fix body styles
		// - Current template is `frontend-body-template.php` to fix header & footer styles
		if ( in_array( $base_template, array( 'template-canvas.php', 'frontend-body-template.php' ), true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'block_template_styles' ) );
		}

		// Bail early if current template is not `template-canvas.php`.
		if ( 'template-canvas.php' !== $base_template ) {
			return $template;
		}

		// 2. Override default template canvas with builder block template canvas.
		$original_template = $template;
		$template          = ET_BUILDER_DIR . 'templates/block-template-canvas.php';

		// 3. Add needed actions and remove default template canvas actions.
		// Remove viewport meta tag.
		if ( function_exists( '_block_template_viewport_meta_tag' ) ) {
			remove_action( 'wp_head', '_block_template_viewport_meta_tag', 0 );
		}

		// Render conditional title tag for `title-tag` support.
		add_action( 'wp_head', '_wp_render_title_tag', 1 );

		// Remove unconditional title tag.
		if ( function_exists( '_block_template_render_title_tag' ) ) {
			remove_action( 'wp_head', '_block_template_render_title_tag', 1 );
		}

		// Add opening and closing wrappers for block template canvas because there is no
		// specific wrappers found when a page use block template canvas.
		add_action( 'et_theme_builder_template_after_header', array( $this, 'main_content_opening_wrapper' ) );
		add_action( 'et_theme_builder_template_before_footer', array( $this, 'main_content_closing_wrapper' ) );

		/**
		 * Fires additional actions after builder override block template.
		 *
		 * @since ??
		 *
		 * @param string $template          New processed block template.
		 * @param string $original_template Original block template.
		 */
		do_action( 'et_after_override_block_template', $template, $original_template );

		return $template;
	}

	/**
	 * Set main content opening wrapper.
	 *
	 * Provide the opening wrapper tags only to ensure TB layout works smoothly. The same
	 * wrapper is being used on Divi theme.
	 *
	 * @since ??
	 */
	public function main_content_opening_wrapper() {
		// By default, content class is `builder-content`. This class has no style at all
		// because it's controlled by the builder itself. This class can be useful as an
		// indicator and selector for the content built with builder.
		$content_class = 'builder-content';

		// When current page is singular page, check builder and divi/layout block usage.
		if ( is_singular() ) {
			$post_id              = get_the_ID();
			$is_page_builder_used = et_pb_is_pagebuilder_used( $post_id );

			// The `block-content wp-site-blocks` classes will added on current page when:
			// - Builder is not used.
			// - Builder is used but it's coming from Divi Layout block.
			// The `block-content` class has style to reset content width. The `wp-site-blocks`
			// class is needed to mimic default block content styles.
			if ( ! $is_page_builder_used || ( $is_page_builder_used && has_block( 'divi/layout', $post_id ) ) ) {
				$content_class = 'block-content wp-site-blocks';
			}
		}
		?>
			<div id="et-main-area">
				<div id="main-content" class="<?php echo esc_attr( $content_class ); ?>">
		<?php
	}

	/**
	 * Set main content closing wrapper.
	 *
	 * Provide the closing wrapper tag only to ensure TB layout works smoothly. The same
	 * wrapper is being used on Divi theme.
	 *
	 * @since ??
	 */
	public function main_content_closing_wrapper() {
		?>
				</div><!-- #main-content -->
			</div><!-- #et-main-area -->
		<?php
	}

	/**
	 * Enqueue block templates compatibility styles.
	 *
	 * @since ??
	 */
	public function block_template_styles() {
		wp_enqueue_style( 'et-block-templates-styles', ET_BUILDER_URI . '/styles/block_templates.css', array(), ET_BUILDER_PRODUCT_VERSION );
	}

	/**
	 * Disable deprecated files warnings.
	 *
	 * Since themes that support block template may don't have some files, the template
	 * may fall into backward compatibility for those files and trigger warnings. Hence,
	 * we need to disable them temporarily. The list of files:
	 * - header
	 * - footer
	 * - comments
	 *
	 * @since ??
	 *
	 * @param string $file File info.
	 */
	public function disable_deprecated_file_warnings( $file ) {
		if ( strpos( $file, 'header.php' ) || strpos( $file, 'footer.php' ) || strpos( $file, 'comments.php' ) ) {
			add_filter( 'deprecated_file_trigger_error', '__return_false' );
		} else {
			add_filter( 'deprecated_file_trigger_error', '__return_true' );
		}
	}

	/**
	 * Remove unsupported theme filters for WooCommerce.
	 *
	 * When current theme supports FSE, WooCommerce will mark it as unsupported theme and
	 * overrides some filters and few of them are related to builder. Hence, we need to
	 * remove those filters to ensure Divi Builder works normally.
	 *
	 * @since ??
	 */
	public function remove_unsupported_theme_filter() {
		// Single Product.
		if ( is_product() ) {
			global $post;

			$post_id = $post ? $post->ID : 0;

			// Only remove those filters when current product uses builder.
			if ( et_pb_is_pagebuilder_used( $post_id ) ) {
				remove_filter( 'the_content', array( 'WC_Template_Loader', 'unsupported_theme_product_content_filter' ), 10 );
				remove_filter( 'woocommerce_product_tabs', array( 'WC_Template_Loader', 'unsupported_theme_remove_review_tab' ), 10 );
			}
		}
	}
}

ET_Builder_Block_Templates::instance();
