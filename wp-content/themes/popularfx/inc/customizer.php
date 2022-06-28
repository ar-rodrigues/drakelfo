<?php
/**
 * PopularFX Theme Customizer
 *
 * @package PopularFX
 */

add_action( 'wp_head', 'popularfx_global_styles', 4 );
function popularfx_global_styles(){
	
	global $pagelayer, $popularfx;
	
	$settings = ['body', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'color', 'max_width' => 'content_width', 'tablet_breakpoint', 'mobile_breakpoint', 'template'];
	
	foreach($settings as $k => $v){
		
		$set = get_theme_mod('popularfx_'.$v);
		
		if(empty($set)){
			continue;
		}
		
		$css[$v] = $set;
		
		$key = is_numeric($k) ? $v : $k;
		
		// We override pagelayer settings for widths and breakpoint
		if(in_array($v, ['content_width', 'tablet_breakpoint', 'mobile_breakpoint'])){
			$pagelayer->settings[$key] = $set;
		}
		
	}
	
	$styles = '<style id="popularfx-global-styles" type="text/css">'.PHP_EOL;
	
	// Add the max width only when we have no template
	if(empty($css['template']) && !file_exists(get_stylesheet_directory().'/pagelayer.conf')){
		$styles .= '.entry-content{ max-width: '.(empty($css['content_width']) ? 1170 : esc_attr($css['content_width'])).'px; margin-left: auto !important; margin-right: auto !important;}'.PHP_EOL;
	}
	
	// Colors
	if(!empty($css['color']['background'])){
		$css['body']['background-color'] = $css['color']['background'];
	}
	
	if(!empty($css['color']['text'])){
		$css['body']['color'] = $css['color']['text'];
	}
	
	// Global CSS settings
	$css_settings = ['body', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
	
	// PX suffix
	$pxs = ['font-size', 'letter-spacing', 'word-spacing'];
	
	foreach($css_settings as $k => $v){
		
		$key = is_numeric($k) ? $v : $k;
		$r = [];
			
		if(empty($css[$key])){
			continue;
		}
		
		if(!empty($css[$v]['font-family']) && strtolower($css[$v]['font-family']) == 'inherit'){
			unset($css[$v]['font-family']);
		}
	
		// Fetch body font if given
		if(!empty($css[$v]['font-family'])){
			$val = $css[$v]['font-family'];			
			$font_weight = empty($css[$v]['font-weight']) ? 400 : $css[$v]['font-weight'];
			$font_style = empty($css[$v]['font-style']) ? 'normal' : $css[$v]['font-style'];
			$font_style = in_array($font_style, ['italic', 'oblique']) ? 'i' : '';			
			$popularfx['runtime_fonts'][$val][$font_weight.$font_style] = $font_weight.$font_style;
		}
		
		foreach($css[$key] as $kk => $vv){
			
			if(empty($vv)){
				continue;
			}
			
			$r[] = $kk.':'.$vv.(in_array($kk, $pxs) ? 'px' : '');
			
		}
		
		if(empty($r)){
			continue;
		}
		
		$styles .= 'body.popularfx-body '.esc_attr($v == 'body' ? '' : $v).'{'.esc_attr(implode(';', $r))."}\n";
	}
	
	// Link Color
	if(!empty($css['color']['link'])){
		$styles .= 'body.popularfx-body a{color: '.esc_attr($css['color']['link']).'}'.PHP_EOL;
	}
	
	// Link Hover Color
	if(!empty($css['color']['link-hover'])){
		$styles .= 'body.popularfx-body a:hover{color: '.esc_attr($css['color']['link-hover']).'}'.PHP_EOL;
	}
	
	// Link Hover Color
	if(!empty($css['color']['heading'])){
		$styles .= 'body.popularfx-body h1,h2,h3,h4,h5,h6{color: '.esc_attr($css['color']['heading']).'}'.PHP_EOL;
	}
	
	// Header Background Color
	$header_bg_color = get_theme_mod('popularfx_header_bg_color', '#ffffff');
	$styles .= '.site-header {background-color:'.esc_attr($header_bg_color).'!important;}'.PHP_EOL;
	
	// Site Title Color
	$site_title_color = get_theme_mod('popularfx_site_title_color', '#171717');
	$styles .= '.site-title a {color:'.esc_attr($site_title_color).'!important;}'.PHP_EOL;
	
	// Site title size
	$site_title_size = get_theme_mod( 'popularfx_site_title_size', '30' );
	$styles.= '.site-title a { font-size: ' . esc_attr( $site_title_size ) .' px; }'.PHP_EOL;
	
	// Site Description Color
	$description_color = get_theme_mod('popularfx_site_tagline_color', '#171717');
	$styles .= '.site-description {color:'.esc_attr($description_color).' !important;}'.PHP_EOL;
	
	// Site Description size
	$tagline_size = get_theme_mod( 'popularfx_tagline_size', '15' );
	$styles .= '.site-description {font-size: ' . esc_attr($tagline_size) . 'px;}'.PHP_EOL;
	
	// Footer Background Color
	$footer_bg_color = get_theme_mod('popularfx_footer_bg_color', '#171717');
	$styles .= '.site-footer {background-color:'.esc_attr($footer_bg_color).'! important;}'.PHP_EOL;
	
	if ( get_header_image() ){
		$styles .= '.site-header {background-image: url("'.esc_url(get_header_image()).'");}'.PHP_EOL;
	}
	
	$styles .= PHP_EOL.'</style>';
	
	echo $styles;
	
	//pagelayer_print($pagelayer->settings);
}

add_filter('body_class', 'popularfx_body_class', 10, 2);
function popularfx_body_class($classes, $class){
	$classes[] = 'popularfx-body';
	return $classes;
}

// Load the google fonts
add_action('wp_footer', 'popularfx_enqueue_fonts', 4);
function popularfx_enqueue_fonts(){
	
	global $pagelayer, $popularfx;
	
	if(empty($popularfx['runtime_fonts'])){
		return;
	}
	
	$url = [];
	
	foreach($popularfx['runtime_fonts'] as $font => $weights){
		$url[] = $font.':'.implode(',', $weights);
	}
	
	// If no fonts are to be set, then we dont set
	if(empty($url)){
		return false;
	}
	
	wp_register_style('popularfx-google-font', 'https://fonts.googleapis.com/css?family='.rawurlencode(implode('|', $url)), array(), POPULARFX_VERSION);
	wp_enqueue_style('popularfx-google-font');
	
}

if(class_exists('WP_Customize_Section')){

	class WP_Customize_PFX_Pro_Section extends WP_Customize_Section {
		public $type = 'pfxpro';

		public function render() {
		?>
<style>

#customize-control-popularfx_sidebar_default{
border-bottom: 1px double #ccc;
padding-bottom: 15px;
}

#accordion-section-popularfx_pro_link{
padding:10px;
background: #fff;
font-size: 13px;
border: 1px solid #337ef4 !important;
font-weight: 600;
text-align: right;
}

#accordion-section-popularfx_pro_link a{
display: block;
text-decoration: none;
}

</style>
			<li id="accordion-section-popularfx_pro_link" class="accordion-section control-section control-section-<?php echo esc_html( $this->type ); ?>">
				<a href="<?php echo POPULARFX_WWW_URL; ?>/pricing?from=pfx-customizer" target="_blank"><?php _e('Get More Options with PopularFX Pro', 'popularfx'); ?><span class="dashicons dashicons-arrow-right-alt2"></span></a>
			</li>
		<?php
		}
	}

}

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function popularfx_customize_register( $wp_customize ) {
	
	global $popularfx;
	
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
	
	if(empty($popularfx['license']['status'])){
		
		$wp_customize->add_section( new WP_Customize_PFX_Pro_Section($wp_customize, 'popularfx_pro_link', array(
			'capability' => 'edit_theme_options',
			'priority'   => 1,
			'title'      => __('Get More Options with PopularFX Pro', 'popularfx')
		) ) );
	
		// Theme Header Footer Edit option of Pagelayer
		$wp_customize->add_setting('popularfx_pro_show', array(
			'capability' => 'edit_theme_options',
			'type'       => 'hidden',
			'autoload'   => false,
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control('popularfx_pro_show', array(
			'label'   => __('See Pro Features', 'popularfx'),
			'description' => 'test',
			'section' => 'popularfx_pro_link',
			'type'    => 'hidden',
		) );
	
	}
	
	wp_register_style('popularfx-customizer', get_stylesheet_directory_uri().'/customizer.css', POPULARFX_VERSION);
	wp_enqueue_style('popularfx-customizer');
	
	$pages = '';
	$templates = '';
	$html = '';	
	
	//---------------------------------
	// Edit Header Footer Pages option
	//---------------------------------
	
	$theme = wp_get_theme();
	
	$template = get_theme_mod('popularfx_template');
	
	// If there is a template in use
	if(!empty($template)){
		
		$wp_customize->add_section( 'popularfx_edit_links', array(
			'capability' => 'edit_theme_options',
			'priority'   => 1,
			'title'      => __( 'Header, Footer, Templates, Pages', 'popularfx' )
		) );
		 
		// Get list of pages and pagelayer templates to edit
		$args = array(
			'post_type' => ['page', 'pagelayer-template'],
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'pagelayer_imported_content',
					'value' => $template,
					'compare' => '='
				)
			)
		);
		
		$query = new WP_Query($args);

		foreach($query->posts as $k => $v){
			$a = '<a href="'.pagelayer_livelink($v->ID).'" class="popularfx-edit-link" target="_blank">'.$v->post_title.'</a>';
			
			if($v->post_type == 'page'){
				$pages .= $a;
			}else{
				
				$temp_type = get_post_meta( $v->ID, 'pagelayer_template_type', true );
				
				if(in_array($temp_type, ['header', 'footer'])){
					$html .= $a;
				}else{
					$templates .= $a;
				}
				
			}
		}
		
		$pages .= '<p>'.__('<b>Note:</b> The Pagelayer editor will open to edit these pages', 'popularfx').'</p>';
	
		// Theme Header Footer Edit option of Pagelayer
		$wp_customize->add_setting('popularfx_hf', array(
			'capability' => 'edit_theme_options',
			'type'       => 'hidden',
			'autoload'   => false,
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		) );	

		$wp_customize->add_control('popularfx_hf', array(
			'label'   => __('Edit Header / Footer', 'popularfx'),
			'description' => $html,
			'section' => 'popularfx_edit_links',
			'type'    => 'hidden',
		) );
		
		if(!empty($templates)){
		
			// Template edit options of Pagelayer
			$wp_customize->add_setting('popularfx_templates', array(
				'capability' => 'edit_theme_options',
				'type'       => 'hidden',
				'autoload'   => false,
				'transport' => 'refresh',
				'sanitize_callback' => 'sanitize_text_field',
			) );

			$wp_customize->add_control('popularfx_templates', array(
				'label'   => __('Edit Templates', 'popularfx'),
				'description' => $templates,
				'section' => 'popularfx_edit_links',
				'type'    => 'hidden',
			) );
		
		}
	
		// Theme Page edit options of Pagelayer
		$wp_customize->add_setting('popularfx_pages', array(
			'capability' => 'edit_theme_options',
			'type'       => 'hidden',
			'autoload'   => false,
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control('popularfx_pages', array(
			'label'   => __('Edit Pages', 'popularfx'),
			'description' => $pages,
			'section' => 'popularfx_edit_links',
			'type'    => 'hidden',
		) );
	
	// No template
	}else{
		
		$wp_customize->add_section( 'popularfx_edit_links', array(
			'capability' => 'edit_theme_options',
			'priority'   => 1,
			'title'      => __( 'Header & Footer Options', 'popularfx' )
		) );
		
		$wp_customize->add_setting('popularfx_header_bg_color', array(
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_hex_color',
		) );
		
		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize, 'popularfx_header_bg_color', array(
				'section' => 'popularfx_edit_links',
				'description' => $html,
				'settings' => 'popularfx_header_bg_color',
				'label' => __('Header Background Color', 'popularfx' )
			) )
		);
		
		//Site title font size
		$wp_customize->add_setting( 'popularfx_site_title_size', array(
			'capability' => 'edit_theme_options',
			'default' => '30',
			'transport' => 'refresh',
			'sanitize_callback' => 'absint',
		) );
		
		$wp_customize->add_control( 'popularfx_site_title_size', array(
			'type' => 'number',
			'section' => 'popularfx_edit_links',
			'settings' => 'popularfx_site_title_size',
			'label' => __( 'Font size', 'popularfx' ),
			'description' => __( 'Change font size of site title', 'popularfx' ),
			'input_attrs' => array(
				'min' => 0,
				'max' => 200,
				'step' => 1,
			),
		) );
				
		$wp_customize->add_setting('popularfx_site_title_color', array(
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_hex_color',
		) );
			
		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize, 'popularfx_site_title_color', array(
				'section' => 'popularfx_edit_links',
				'settings' => 'popularfx_site_title_color',
				'label' => __('Site Title Color', 'popularfx' )
			) )
		);
		
		//Tagline font size
		$wp_customize->add_setting( 'popularfx_tagline_size', array(
			'capability' => 'edit_theme_options',
			'default' => '15',
			'transport' => 'refresh',
			'sanitize_callback' => 'absint',
		) );
		
		$wp_customize->add_control( 'popularfx_tagline_size', array(
			'type' => 'number',
			'section' => 'popularfx_edit_links',
			'settings' => 'popularfx_tagline_size',
			'label' => __( 'Font size', 'popularfx' ),
			'description' => __( 'Change font size of site tagline', 'popularfx' ),
			'input_attrs' => array(
				'min' => 0,
				'max' => 200,
				'step' => 1,
			),
		) );
		
		//Tagline font size
		$wp_customize->add_setting( 'popularfx_tagline_size', array(
			'capability' => 'edit_theme_options',
			'default' => '15',
			'transport' => 'refresh',
			'sanitize_callback' => 'absint',
		) );
		
		$wp_customize->add_control( 'popularfx_tagline_size', array(
			'type' => 'number',
			'section' => 'popularfx_edit_links',
			'settings' => 'popularfx_tagline_size',
			'label' => __( 'Font size', 'popularfx' ),
			'description' => __( 'Change font size of site description', 'popularfx' ),
			'input_attrs' => array(
				'min' => 0,
				'max' => 200,
				'step' => 1,
			),
		) );
		
		$wp_customize->add_setting('popularfx_site_tagline_color', array(
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_hex_color',
		) );
			
		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize, 'popularfx_site_tagline_color', array(
				'section' => 'popularfx_edit_links',
				'settings' => 'popularfx_site_tagline_color',
				'label' => __('Tagline Color', 'popularfx' )
				) 
			)
		);
		
		// Footer text
		$wp_customize->add_setting( 'popularfx_footer_text', array(
			'capability' => 'edit_theme_options',
			'default' => '30',
			'transport' => 'refresh',
			'sanitize_callback' => 'absint',
		) );
		
		$wp_customize->add_control( 'popularfx_footer_text', array(
			'type' => 'text',
			'section' => 'popularfx_edit_links',
			'settings' => 'popularfx_footer_text',
			'label' => __( 'Footer Text / HTML', 'popularfx' ),
			'description' => __( 'Add any text to your footer. e.g. your copyright - &copy; Site Name', 'popularfx' ),
		) );
		
		$wp_customize->add_setting('popularfx_footer_bg_color', array(
			'capability' => 'edit_theme_options',
			'transport' => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
			) 
		);
		
		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize, 'popularfx_footer_bg_color', array(
				'section' => 'popularfx_edit_links',
				'settings' => 'popularfx_footer_bg_color',
				'label' => __('Footer Background Color', 'popularfx' )
				) )
			);

	}
		
	$wp_customize->add_panel( 'typography', array(
		'title' => __( 'Typography', 'popularfx'),
		'priority' => 2
	) );
	
	// Load the options
	//pagelayer_load_font_options();
	
	// Create the sections
	popularfx_customize_font('body', 'Body', $wp_customize);
	popularfx_customize_font('h1', 'H1', $wp_customize);
	popularfx_customize_font('h2', 'H2', $wp_customize);
	popularfx_customize_font('h3', 'H3', $wp_customize);
	popularfx_customize_font('h4', 'H4', $wp_customize);
	popularfx_customize_font('h5', 'H5', $wp_customize);
	popularfx_customize_font('h6', 'H6', $wp_customize);
	
	
	//---------------------------------
	// Colors
	//---------------------------------
	$wp_customize->add_section( 'popularfx_colors', array(
		'capability' => 'edit_theme_options',
		'priority'   => 2,
		'title'      => __( 'Colors','popularfx')
	) );
		
	// BG Color
	$wp_customize->add_setting( 'popularfx_color[background]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
		) );
	
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'popularfx_color[background]', array(
			'priority' => 2,
			'section' => 'popularfx_colors',
			'settings' => 'popularfx_color[background]',
			'label' => __( 'Background Color', 'popularfx' ),
			) 
		)
	);
	
	// text Color
	$wp_customize->add_setting( 'popularfx_color[text]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
		) );
	
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'popularfx_color[text]', array(
			'priority' => 2,
			'section' => 'popularfx_colors',
			'settings' => 'popularfx_color[text]',
			'label' => __( 'Text Color', 'popularfx' ),
			) 
		)
	);
		
	// link Color
	$wp_customize->add_setting( 'popularfx_color[link]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
		) );
	
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'popularfx_color[link]', array(
			'priority' => 2,
			'section' => 'popularfx_colors',
			'settings' => 'popularfx_color[link]',
			'label' => __( 'Link Color', 'popularfx' ),
			) 
		)
	);
		
	// link-hover Color
	$wp_customize->add_setting( 'popularfx_color[link-hover]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
		) );
	
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'popularfx_color[link-hover]', array(
			'priority' => 2,
			'section' => 'popularfx_colors',
			'settings' => 'popularfx_color[link-hover]',
			'label' => __( 'Link Hover Color', 'popularfx' ),
			) 
		)
	);
		
	// heading Color
	$wp_customize->add_setting( 'popularfx_color[heading]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
		) );
	
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'popularfx_color[heading]', array(
			'priority' => 2,
			'section' => 'popularfx_colors',
			'settings' => 'popularfx_color[heading]',
			'label' => __( 'Heading Color (H1-H6)', 'popularfx' ),
			) 
		)
	);
	
	
	//---------------------------------
	// Sidebar
	//---------------------------------
	$wp_customize->add_section( 'popularfx_sidebar', array(
		'capability' => 'edit_theme_options',
		'priority'   => 2,
		'title'      => __( 'Sidebar','popularfx')
	) );
		
	// Default Sidebar
	$wp_customize->add_setting( 'popularfx_sidebar_default', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => '0',
		'sanitize_callback' => 'sanitize_text_field',
	) );
		
	$wp_customize->add_control( 'popularfx_sidebar_default', array(
		'priority' => 1,
		'type' => 'select',
		'section' => 'popularfx_sidebar',
		'settings' => 'popularfx_sidebar_default',
		'label' => __( 'Default Sidebar', 'popularfx' ),
		'description' => __( 'Default layout for the Sidebar throughout the site', 'popularfx' ),
		'choices' => array(
			'0' => __( 'No Sidebar', 'popularfx' ),
			'left' => __( 'Left Sidebar', 'popularfx' ),
			'right' => __( 'Right Sidebar', 'popularfx' ),
		),
	) );
		
	// Page Sidebar
	$wp_customize->add_setting( 'popularfx_sidebar_page', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'default',
		'sanitize_callback' => 'sanitize_text_field',
		) );
		
	$wp_customize->add_control( 'popularfx_sidebar_page', array(
		'type' => 'select',
		'section' => 'popularfx_sidebar',
		'settings' => 'popularfx_sidebar_page',
		'label' => __( 'Page Sidebar', 'popularfx' ),
		'choices' => array(
			'default' => __( 'Default', 'popularfx' ),
			'0' => __( 'No Sidebar', 'popularfx' ),
			'left' => __( 'Left Sidebar', 'popularfx' ),
			'right' => __( 'Right Sidebar', 'popularfx' ),
		),
	) );
		
	// Posts Sidebar
	$wp_customize->add_setting( 'popularfx_sidebar_post', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'right',
		'sanitize_callback' => 'sanitize_text_field',
		) );
		
	$wp_customize->add_control( 'popularfx_sidebar_post', array(
		'type' => 'select',
		'section' => 'popularfx_sidebar',
		'settings' => 'popularfx_sidebar_post',
		'label' => __( 'Post Sidebar', 'popularfx' ),
		'choices' => array(
			'default' => __( 'Default', 'popularfx' ),
			'0' => __( 'No Sidebar', 'popularfx' ),
			'left' => __( 'Left Sidebar', 'popularfx' ),
			'right' => __( 'Right Sidebar', 'popularfx' ),
		),
	) );
		
	// Archives Sidebar
	$wp_customize->add_setting( 'popularfx_sidebar_archives', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'right',
		'sanitize_callback' => 'sanitize_text_field',
		) );
		
	$wp_customize->add_control( 'popularfx_sidebar_archives', array(
		'type' => 'select',
		'section' => 'popularfx_sidebar',
		'settings' => 'popularfx_sidebar_archives',
		'label' => __( 'Archives Sidebar', 'popularfx' ),
		'choices' => array(
			'default' => __( 'Default', 'popularfx' ),
			'0' => __( 'No Sidebar', 'popularfx' ),
			'left' => __( 'Left Sidebar', 'popularfx' ),
			'right' => __( 'Right Sidebar', 'popularfx' ),
		),
	) );
		
	// Sidebar Width
	$wp_customize->add_setting( 'popularfx_sidebar_width', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 20,
		'sanitize_callback' => 'absint',
	) );
		
	$wp_customize->add_control( 'popularfx_sidebar_width', array(
		'type' => 'number',
		'section' => 'popularfx_sidebar',
		'settings' => 'popularfx_sidebar_width',
		'label' => __( 'Sidebar Width', 'popularfx' ),
		'description' => __( 'Set the width of the Sidebar in percentage','popularfx'),
		'input_attrs' => array(
			'min' => 0,
			'max' => 100,
			'step' => 1,
		),
	) );
	
	
	//---------------------------------
	// Container
	//---------------------------------
	$wp_customize->add_section( 'popularfx_container', array(
		'capability' => 'edit_theme_options',
		'priority'   => 5,
		'title'      => __( 'Container', 'popularfx')
	) );
		
	// Container Width
	$wp_customize->add_setting( 'popularfx_content_width', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'absint',
		) );
		
	$wp_customize->add_control( 'popularfx_content_width', array(
		'type' => 'number',
		'section' => 'popularfx_container',
		'settings' => 'popularfx_content_width',
		'label' => __( 'Content Width', 'popularfx' ),
		'description' => __( 'The width of the content container. Default is 1170px', 'popularfx' ),
		'input_attrs' => array(
			'min' => 800,
			'step' => 1,
			'placeholder' => 1170
		),
	) );
		
	// Tablet Breakpoint
	$wp_customize->add_setting( 'popularfx_tablet_breakpoint', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'absint',
		) );
		
	$wp_customize->add_control( 'popularfx_tablet_breakpoint', array(
		'type' => 'number',
		'section' => 'popularfx_container',
		'settings' => 'popularfx_tablet_breakpoint',
		'label' => __( 'Tablet Breakpoint', 'popularfx' ),
		'description' => __( 'Set the breakpoint for tablet devices. The default breakpoint for tablet layout is 768px.','popularfx'),
		'input_attrs' => array(
			'min' => 500,
			'step' => 1,
			'placeholder' => 768
		),
	) );
		
	// Mobile Breakpoint
	$wp_customize->add_setting( 'popularfx_mobile_breakpoint', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'sanitize_callback' => 'absint',
		) );
		
	$wp_customize->add_control( 'popularfx_mobile_breakpoint', array(
		'type' => 'number',
		'section' => 'popularfx_container',
		'settings' => 'popularfx_mobile_breakpoint',
		'label' => __( 'Mobile Breakpoint', 'popularfx' ),
		'description' => __( 'Set the breakpoint for mobile devices. The default breakpoint for mobile layout is 360px.','popularfx'),
		'input_attrs' => array(
			'min' => 200,
			'step' => 1,
			'placeholder' => 360
		),
	) );

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'popularfx_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'popularfx_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'popularfx_customize_register' );

// Shows the font settings
function popularfx_customize_font($prefix, $text, &$wp_customize){
	
	global $pagelayer, $pl_error;
	
	$popularfx_styles['font-weight'] = ['100', '200', '300', '400', '500', '600', '700', '800', '900', 'normal', 'lighter', 'bold', 'bolder', 'unset'];
	
	$popularfx_styles['transform'] = ['Inherit', 'Initial', 'Capitalize', 'Uppercase', 'Lowercase', 'None'];
	
	$popularfx_styles['font-family'] = ['Inherit','ABeeZee', 'Abel', 'Abhaya Libre', 'Abril Fatface', 'Aclonica', 'Acme', 'Actor', 'Adamina', 'Advent Pro', 'Aguafina Script', 'Akronim', 'Aladin', 'Aldrich', 'Alef', 'Alegreya', 'Alegreya SC', 'Alegreya Sans', 'Alegreya Sans SC', 'Aleo', 'Alex Brush', 'Alfa Slab One', 'Alice', 'Alike', 'Alike Angular', 'Allan', 'Allerta', 'Allerta Stencil', 'Allura', 'Almendra', 'Almendra Display', 'Almendra SC', 'Amarante', 'Amaranth', 'Amatic SC', 'Amethysta', 'Amiko', 'Amiri', 'Amita', 'Anaheim', 'Andada', 'Andika', 'Angkor', 'Annie Use Your Telescope', 'Anonymous Pro', 'Antic', 'Antic Didone', 'Antic Slab', 'Anton', 'Arapey', 'Arbutus', 'Arbutus Slab', 'Architects Daughter', 'Archivo', 'Archivo Black', 'Archivo Narrow', 'Aref Ruqaa', 'Arima Madurai', 'Arimo', 'Arizonia', 'Armata', 'Arsenal', 'Artifika', 'Arvo', 'Arya', 'Asap', 'Asap Condensed', 'Asar', 'Asset', 'Assistant', 'Astloch', 'Asul', 'Athiti', 'Atma', 'Atomic Age', 'Aubrey', 'Audiowide', 'Autour One', 'Average', 'Average Sans', 'Averia Gruesa Libre', 'Averia Libre', 'Averia Sans Libre', 'Averia Serif Libre', 'B612', 'B612 Mono', 'Bad Script', 'Bahiana', 'Bai Jamjuree', 'Baloo', 'Baloo Bhai', 'Baloo Bhaijaan', 'Baloo Bhaina', 'Baloo Chettan', 'Baloo Da', 'Baloo Paaji', 'Baloo Tamma', 'Baloo Tammudu', 'Baloo Thambi', 'Balthazar', 'Balsamiq Sans', 'Bangers', 'Barlow', 'Barlow Condensed', 'Barlow Semi Condensed', 'Barrio', 'Basic', 'Battambang', 'Baumans', 'Bayon', 'Belgrano', 'Bellefair', 'Belleza', 'Bellota', 'BenchNine', 'Bentham', 'Berkshire Swash', 'Bevan', 'Bigelow Rules', 'Bigshot One', 'Bilbo', 'Bilbo Swash Caps', 'BioRhyme', 'BioRhyme Expanded', 'Biryani', 'Bitter', 'Black And White Picture', 'Black Han Sans', 'Black Ops One', 'Bokor', 'Bonbon', 'Boogaloo', 'Bowlby One', 'Bowlby One SC', 'Brawler', 'Bree Serif', 'Bubblegum Sans', 'Bubbler One', 'Buda', 'Buenard', 'Bungee', 'Bungee Hairline', 'Bungee Inline', 'Bungee Outline', 'Bungee Shade', 'Butcherman', 'Butterfly Kids', 'Cabin', 'Cabin Condensed', 'Cabin Sketch', 'Caesar Dressing', 'Cagliostro', 'Cairo', 'Calligraffitti', 'Cambay', 'Cambo', 'Candal', 'Cantarell', 'Cantata One', 'Cantora One', 'Capriola', 'Cardo', 'Carme', 'Carrois Gothic', 'Carrois Gothic SC', 'Carter One', 'Catamaran', 'Caudex', 'Caveat', 'Caveat Brush', 'Cedarville Cursive', 'Ceviche One', 'Chakra Petch', 'Changa', 'Changa One', 'Chango', 'Charm', 'Charmonman', 'Chathura', 'Chau Philomene One', 'Chela One', 'Chelsea Market', 'Chenla', 'Cherry Cream Soda', 'Cherry Swash', 'Chewy', 'Chicle', 'Chivo', 'Chonburi', 'Cinzel', 'Cinzel Decorative', 'Clicker Script', 'Coda', 'Coda Caption', 'Codystar', 'Coiny', 'Combo', 'Comfortaa', 'Coming Soon', 'Concert One', 'Condiment', 'Content', 'Contrail One', 'Convergence', 'Cookie', 'Copse', 'Corben', 'Cormorant', 'Cormorant Garamond', 'Cormorant Infant', 'Cormorant SC', 'Cormorant Unicase', 'Cormorant Upright', 'Courgette', 'Cousine', 'Coustard', 'Covered By Your Grace', 'Crafty Girls', 'Creepster', 'Crete Round', 'Crimson Text', 'Croissant One', 'Crushed', 'Cuprum', 'Cute Font', 'Cutive', 'Cutive Mono', 'Damion', 'Dancing Script', 'Dangrek', 'David Libre', 'Dawning of a New Day', 'Days One', 'Dekko', 'Delius', 'Delius Swash Caps', 'Delius Unicase', 'Della Respira', 'Denk One', 'Devonshire', 'Dhurjati', 'Didact Gothic', 'Diplomata', 'Diplomata SC', 'Do Hyeon', 'Dokdo', 'Domine', 'Donegal One', 'Doppio One', 'Dorsa', 'Dosis', 'Dr Sugiyama', 'Duru Sans', 'Dynalight', 'EB Garamond', 'Eagle Lake', 'East Sea Dokdo', 'Eater', 'Economica', 'Eczar', 'El Messiri', 'Electrolize', 'Elsie', 'Elsie Swash Caps', 'Emblema One', 'Emilys Candy', 'Encode Sans', 'Encode Sans Condensed', 'Encode Sans Expanded', 'Encode Sans Semi Condensed', 'Encode Sans Semi Expanded', 'Engagement', 'Englebert', 'Enriqueta', 'Epilogue',  'Erica One', 'Esteban', 'Euphoria Script', 'Ewert', 'Exo', 'Exo 2', 'Expletus Sans', 'Fahkwang', 'Fanwood Text', 'Farsan', 'Fascinate', 'Fascinate Inline', 'Faster One', 'Fasthand', 'Fauna One', 'Faustina', 'Federant', 'Federo', 'Felipa', 'Fenix', 'Finger Paint', 'Fira Mono', 'Fira Sans', 'Fira Sans Condensed', 'Fira Sans Extra Condensed', 'Fjalla One', 'Fjord One', 'Flamenco', 'Flavors', 'Fondamento', 'Fontdiner Swanky', 'Forum', 'Francois One', 'Frank Ruhl Libre', 'Freckle Face', 'Fredericka the Great', 'Fredoka One', 'Freehand', 'Fresca', 'Frijole', 'Fruktur', 'Fugaz One', 'GFS Didot', 'GFS Neohellenic', 'Gabriela', 'Gaegu', 'Gafata', 'Galada', 'Galdeano', 'Galindo', 'Gamja Flower', 'Gentium Basic', 'Gentium Book Basic', 'Geo', 'Geostar', 'Geostar Fill', 'Germania One', 'Gidugu', 'Gilda Display', 'Give You Glory', 'Glass Antiqua', 'Glegoo', 'Gloria Hallelujah', 'Goblin One', 'Gochi Hand', 'Gorditas', 'Gothic A1', 'Goudy Bookletter 1911', 'Graduate', 'Grand Hotel', 'Gravitas One', 'Great Vibes', 'Griffy', 'Gruppo', 'Gudea', 'Gugi', 'Gurajada', 'Habibi', 'Halant', 'Hammersmith One', 'Hanalei', 'Hanalei Fill', 'Handlee', 'Hanuman', 'Happy Monkey', 'Harmattan', 'Headland One', 'Heebo', 'Henny Penny', 'Herr Von Muellerhoff', 'Hi Melody', 'Hind', 'Hind Guntur', 'Hind Madurai', 'Hind Siliguri', 'Hind Vadodara', 'Holtwood One SC', 'Homemade Apple', 'Homenaje', 'IBM Plex Mono', 'IBM Plex Sans', 'IBM Plex Sans Condensed', 'IBM Plex Serif', 'IM Fell DW Pica', 'IM Fell DW Pica SC', 'IM Fell Double Pica', 'IM Fell Double Pica SC', 'IM Fell English', 'IM Fell English SC', 'IM Fell French Canon', 'IM Fell French Canon SC', 'IM Fell Great Primer', 'IM Fell Great Primer SC', 'Iceberg', 'Iceland', 'Imprima', 'Inconsolata', 'Inder', 'Indie Flower', 'Inika', 'Inknut Antiqua', 'Irish Grover', 'Istok Web', 'Italiana', 'Italianno', 'Itim', 'Jacques Francois', 'Jacques Francois Shadow', 'Jaldi', 'Jim Nightshade', 'Jockey One', 'Jolly Lodger', 'Jomhuria', 'Josefin Sans', 'Josefin Slab', 'Joti One', 'Jua', 'Judson', 'Julee', 'Julius Sans One', 'Junge', 'Jura', 'Just Another Hand', 'Just Me Again Down Here', 'K2D', 'Kadwa', 'Kalam', 'Kameron', 'Kanit', 'Kantumruy', 'Karla', 'Karma', 'Katibeh', 'Kaushan Script', 'Kavivanar', 'Kavoon', 'Kdam Thmor', 'Keania One', 'Kelly Slab', 'Kenia', 'Khand', 'Khmer', 'Khula', 'Kirang Haerang', 'Kite One', 'Knewave', 'KoHo', 'Kodchasan', 'Kosugi', 'Kosugi Maru', 'Kotta One', 'Koulen', 'Kranky', 'Kreon', 'Kristi', 'Krona One', 'Krub', 'Kumar One', 'Kumar One Outline', 'Kurale', 'La Belle Aurore', 'Laila', 'Lakki Reddy', 'Lalezar', 'Lancelot', 'Lateef', 'Lato', 'League Script', 'Leckerli One', 'Ledger', 'Lekton', 'Lemon', 'Lemonada', 'Libre Barcode 128', 'Libre Barcode 128 Text', 'Libre Barcode 39', 'Libre Barcode 39 Extended', 'Libre Barcode 39 Extended Text', 'Libre Barcode 39 Text', 'Libre Baskerville', 'Libre Franklin', 'Life Savers', 'Lilita One', 'Lily Script One', 'Limelight', 'Linden Hill', 'Lobster', 'Lobster Two', 'Londrina Outline', 'Londrina Shadow', 'Londrina Sketch', 'Londrina Solid', 'Lora', 'Love Ya Like A Sister', 'Loved by the King', 'Lovers Quarrel', 'Luckiest Guy', 'Lusitana', 'Lustria', 'M PLUS 1p', 'M PLUS Rounded 1c', 'Macondo', 'Macondo Swash Caps', 'Mada', 'Magra', 'Maiden Orange', 'Maitree', 'Major Mono Display', 'Mako', 'Mali', 'Mallanna', 'Mandali', 'Manuale', 'Marcellus', 'Marcellus SC', 'Marck Script', 'Margarine', 'Markazi Text', 'Marko One', 'Marmelad', 'Martel', 'Martel Sans', 'Marvel', 'Mate', 'Mate SC', 'Maven Pro', 'McLaren', 'Meddon', 'MedievalSharp', 'Medula One', 'Meera Inimai', 'Megrim', 'Meie Script', 'Merienda', 'Merienda One', 'Merriweather', 'Merriweather Sans', 'Metal', 'Metal Mania', 'Metamorphous', 'Metrophobic', 'Michroma', 'Milonga', 'Miltonian', 'Miltonian Tattoo', 'Mina', 'Miniver', 'Miriam Libre', 'Mirza', 'Miss Fajardose', 'Mitr', 'Modak', 'Modern Antiqua', 'Mogra', 'Molengo', 'Molle', 'Monda', 'Monofett', 'Monoton', 'Monsieur La Doulaise', 'Montaga', 'Montez', 'Montserrat', 'Montserrat Alternates', 'Montserrat Subrayada', 'Moul', 'Moulpali', 'Mountains of Christmas', 'Mouse Memoirs', 'Mr Bedfort', 'Mr Dafoe', 'Mr De Haviland', 'Mrs Saint Delafield', 'Mrs Sheppards', 'Mukta', 'Mukta Mahee', 'Mukta Malar', 'Mukta Vaani', 'Muli', 'Mulish', 'Mystery Quest', 'NTR', 'Nanum Brush Script', 'Nanum Gothic', 'Nanum Gothic Coding', 'Nanum Myeongjo', 'Nanum Pen Script', 'Neucha', 'Neuton', 'New Rocker', 'News Cycle', 'Niconne', 'Niramit', 'Nixie One', 'Nobile', 'Nokora', 'Norican', 'Nosifer', 'Notable', 'Nothing You Could Do', 'Noticia Text', 'Noto Sans', 'Noto Sans JP', 'Noto Sans KR', 'Noto Sans SC', 'Noto Sans TC', 'Noto Serif', 'Noto Serif JP', 'Noto Serif KR', 'Noto Serif SC', 'Noto Serif TC', 'Nova Cut', 'Nova Flat', 'Nova Mono', 'Nova Oval', 'Nova Round', 'Nova Script', 'Nova Slim', 'Nova Square', 'Numans', 'Nunito', 'Nunito Sans', 'Odor Mean Chey', 'Offside', 'Old Standard TT', 'Oldenburg', 'Oleo Script', 'Oleo Script Swash Caps', 'Open Sans', 'Open Sans Condensed', 'Oranienbaum', 'Orbitron', 'Oregano', 'Orienta', 'Original Surfer', 'Oswald', 'Over the Rainbow', 'Overlock', 'Overlock SC', 'Overpass', 'Overpass Mono', 'Ovo', 'Oxygen', 'Oxygen Mono', 'PT Mono', 'PT Sans', 'PT Sans Caption', 'PT Sans Narrow', 'PT Serif', 'PT Serif Caption', 'Pacifico', 'Padauk', 'Palanquin', 'Palanquin Dark', 'Pangolin', 'Paprika', 'Parisienne', 'Passero One', 'Passion One', 'Pathway Gothic One', 'Patrick Hand', 'Patrick Hand SC', 'Pattaya', 'Patua One', 'Pavanam', 'Paytone One', 'Peddana', 'Peralta', 'Permanent Marker', 'Petit Formal Script', 'Petrona', 'Philosopher', 'Piedra', 'Pinyon Script', 'Pirata One', 'Plaster', 'Play', 'Playball', 'Playfair Display', 'Playfair Display SC', 'Podkova', 'Poiret One', 'Poller One', 'Poly', 'Pompiere', 'Pontano Sans', 'Poor Story', 'Poppins', 'Port Lligat Sans', 'Port Lligat Slab', 'Pragati Narrow', 'Prata', 'Preahvihear', 'Press Start 2P', 'Pridi', 'Princess Sofia', 'Prociono', 'Prompt', 'Prosto One', 'Proza Libre', 'Puritan', 'Purple Purse', 'Quando', 'Quantico', 'Quattrocento', 'Quattrocento Sans', 'Questrial', 'Quicksand', 'Quintessential', 'Qwigley', 'Racing Sans One', 'Radley', 'Rajdhani', 'Rakkas', 'Raleway', 'Raleway Dots', 'Ramabhadra', 'Ramaraja', 'Rambla', 'Rammetto One', 'Ranchers', 'Rancho', 'Ranga', 'Rasa', 'Rationale', 'Ravi Prakash', 'Redressed', 'Reem Kufi', 'Reenie Beanie', 'Revalia', 'Rhodium Libre', 'Ribeye', 'Ribeye Marrow', 'Righteous', 'Risque', 'Roboto', 'Roboto Condensed', 'Roboto Mono', 'Roboto Slab', 'Rochester', 'Rock Salt', 'Rokkitt', 'Romanesco', 'Ropa Sans', 'Rosario', 'Rosarivo', 'Rouge Script', 'Rozha One', 'Rubik', 'Rubik Mono One', 'Ruda', 'Rufina', 'Ruge Boogie', 'Ruluko', 'Rum Raisin', 'Ruslan Display', 'Russo One', 'Ruthie', 'Rye', 'Sacramento', 'Sahitya', 'Sail', 'Saira', 'Saira Condensed', 'Saira Extra Condensed', 'Saira Semi Condensed', 'Salsa', 'Sanchez', 'Sancreek', 'Sansita', 'Sarabun', 'Sarala', 'Sarina', 'Sarpanch', 'Satisfy', 'Sawarabi Gothic', 'Sawarabi Mincho', 'Scada', 'Scheherazade', 'Schoolbell', 'Scope One', 'Seaweed Script', 'Secular One', 'Sedgwick Ave', 'Sedgwick Ave Display', 'Sen', 'Sevillana', 'Seymour One', 'Shadows Into Light', 'Shadows Into Light Two', 'Shanti', 'Share', 'Share Tech', 'Share Tech Mono', 'Shojumaru', 'Short Stack', 'Shrikhand', 'Siemreap', 'Sigmar One', 'Signika', 'Signika Negative', 'Simonetta', 'Sintony', 'Sirin Stencil', 'Six Caps', 'Skranji', 'Slabo 13px', 'Slabo 27px', 'Slackey', 'Smokum', 'Smythe', 'Sniglet', 'Snippet', 'Snowburst One', 'Sofadi One', 'Sofia', 'Song Myung', 'Sonsie One', 'Sora', 'Sorts Mill Goudy', 'Source Code Pro', 'Source Sans Pro', 'Source Serif Pro', 'Space Mono', 'Special Elite', 'Spectral', 'Spectral SC', 'Spicy Rice', 'Spinnaker', 'Spirax', 'Squada One', 'Sree Krushnadevaraya', 'Sriracha', 'Srisakdi', 'Staatliches', 'Stalemate', 'Stalinist One', 'Stardos Stencil', 'Stint Ultra Condensed', 'Stint Ultra Expanded', 'Stoke', 'Strait', 'Stylish', 'Sue Ellen Francisco', 'Suez One', 'Sumana', 'Sunflower', 'Sunshiney', 'Supermercado One', 'Sura', 'Suranna', 'Suravaram', 'Suwannaphum', 'Swanky and Moo Moo', 'Syncopate', 'Tajawal', 'Tangerine', 'Taprom', 'Tauri', 'Taviraj', 'Teko', 'Telex', 'Tenali Ramakrishna', 'Tenor Sans', 'Text Me One', 'Thasadith', 'The Girl Next Door', 'Tienne', 'Tillana', 'Timmana', 'Tinos', 'Titan One', 'Titillium Web', 'Trade Winds', 'Trirong', 'Trocchi', 'Trochut', 'Trykker', 'Tulpen One', 'Ubuntu', 'Ubuntu Condensed', 'Ubuntu Mono', 'Ultra', 'Uncial Antiqua', 'Underdog', 'Unica One', 'UnifrakturCook', 'UnifrakturMaguntia', 'Unkempt', 'Unlock', 'Unna', 'VT323', 'Vampiro One', 'Varela', 'Varela Round', 'Vast Shadow', 'Vesper Libre', 'Vibur', 'Vidaloka', 'Viga', 'Voces', 'Volkhov', 'Vollkorn', 'Vollkorn SC', 'Voltaire', 'Waiting for the Sunrise', 'Wallpoet', 'Walter Turncoat', 'Warnes', 'Wellfleet', 'Wendy One', 'Wire One', 'Work Sans', 'Yanone Kaffeesatz', 'Yantramanav', 'Yatra One', 'Yellowtail', 'Yeon Sung', 'Yeseva One', 'Yesteryear', 'Yrsa', 'ZCOOL KuaiLe', 'ZCOOL QingKe HuangYou', 'ZCOOL XiaoWei', 'Zeyada', 'Zilla Slab', 'Zilla Slab Highlight'];
	
		
	foreach($popularfx_styles['font-family'] as $k => $font){	
		$r[$font] = esc_attr($font);
	}
	
	$wp_customize->add_section( 'popularfx_'.$prefix.'_typo', array(
		'title' => $text,
		'panel' => 'typography',
	) );
	
	// Font Family
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[font-family]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
		
	$wp_customize->add_control( 'popularfx_'.$prefix.'[font-family]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[font-family]',
		'label' => __( 'Font Family', 'popularfx' ),
		'choices' => $r,
	) );
	
	$fsizes = [];
	$fsizes[0] = 'Default';
	for($i = 8; $i <= 75; $i++){
		$fsizes[$i] = esc_attr($i.'px');
	}
	
	// Font Size
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[font-size]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 20,
		'sanitize_callback' => 'absint',
		)
	);
		
	$wp_customize->add_control( 'popularfx_'.$prefix.'[font-size]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[font-size]',
		'label' => __( 'Font Size', 'popularfx' ),
		'choices' => $fsizes,
	) );
	
	// Font Style
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[font-style]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
		
	$wp_customize->add_control( 'popularfx_'.$prefix.'[font-style]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[font-style]',
		'label' => __( 'Font Style', 'popularfx' ),
		'choices' => array(
					'Normal' => __( 'Normal', 'popularfx' ),
					'Italic' => __( 'Italic', 'popularfx' ),
					'Oblique' => __( 'Oblique', 'popularfx' )
				),
	) );
	
	$popularfx_weight = array();	
	foreach($popularfx_styles['font-weight'] as $k => $weight){
		$popularfx_weight[$weight] = esc_attr($weight);
	}

	// Font Weight
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[font-weight]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
		
	$wp_customize->add_control( 'popularfx_'.$prefix.'[font-weight]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[font-weight]',
		'label' => __( 'Font Weight', 'popularfx' ),
		'choices' => $popularfx_weight,
	) );
	
	$popularfx_transform = array();	
	foreach($popularfx_styles['transform'] as $k => $transform){
		$popularfx_transform[$transform] = esc_attr($transform);
	}
	
	// Text Transform
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[text-transform]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
		
	$wp_customize->add_control( 'popularfx_'.$prefix.'[text-transform]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[text-transform]',
		'label' => __( 'Text Transform', 'popularfx' ),
		'choices' => $popularfx_transform,
	) );
	
	
	$r = [];
	$r[0] = 'Default';
	for($i = 7; $i <= 35; $i++){
		$v = (string) round($i/10, 1);
		$r[$v] = esc_attr($v);
	}
	
	//pagelayer_print($r);
	
	// Line Height
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[line-height]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
	
	$wp_customize->add_control( 'popularfx_'.$prefix.'[line-height]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[line-height]',
		'label' => __( 'Line Height', 'popularfx' ),
		'choices' => $r,
	) );
	
	// Text Spacing
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[letter-spacing]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
	
	$wp_customize->add_control( 'popularfx_'.$prefix.'[letter-spacing]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[letter-spacing]',
		'label' => __( 'Text Spacing', 'popularfx' ),
		'choices' => $fsizes,
	) );
	
	// Word Spacing
	$wp_customize->add_setting( 'popularfx_'.$prefix.'[word-spacing]', array(
		'capability' => 'edit_theme_options',
		'transport' => 'refresh',
		'default' => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		)
	);
	
	$wp_customize->add_control( 'popularfx_'.$prefix.'[word-spacing]', array(
		'type' => 'select',
		'section' => 'popularfx_'.$prefix.'_typo',
		'settings' => 'popularfx_'.$prefix.'[word-spacing]',
		'label' => __( 'Word Spacing', 'popularfx' ),
		'choices' => $fsizes,
	) );
	
}

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function popularfx_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function popularfx_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function popularfx_customize_preview_js() {
	wp_enqueue_script( 'popularfx-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'popularfx_customize_preview_js' );


