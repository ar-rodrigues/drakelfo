<?php
/**
 * Wizard to guide new users, 5 star rating stuff and news/notices system
 *
 * @package Fish and Ships
 * @since 1.0.0
 * @version 1.3
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Fish_n_Ships_Wizard' ) ) {

	class Fish_n_Ships_Wizard {

		var $options = array();
		
		public function __construct() {
			
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}

		public function admin_init() {
			
			global $Fish_n_Ships;
			
			$this->options = $Fish_n_Ships->get_options();

			// We should show now / later / hide wizard forever?
			if (isset($_GET['wc-fns-wizard']) ) {
				
				$when = sanitize_key( $_GET['wc-fns-wizard'] );
				
				if ( in_array( $when, array( 'now', 'later', 'off' ), true ) ) {
					$Fish_n_Ships->update_wizard_opts('wizard', $when);
				}
			}

			// We should show later / hide five stars forever? (failed AJAX)
			if (isset($_GET['wc-fns-five-stars']) ) {

				$when = sanitize_key( $_GET['wc-fns-five-stars'] );
				
				if ( in_array( $when, array( 'later', 'off' ), true ) ) {
					$Fish_n_Ships->update_wizard_opts('five-stars', $when);
				}
			}

			// We should show later / hide a fns notice forever? (failed AJAX)
			if ( $Fish_n_Ships->im_pro() && isset($_GET['wc-fns-notice-dimiss']) ) {

				$key  = sanitize_key( $_GET['wc-fns-notice-dimiss'] );
				$when = sanitize_key( isset($_GET['time']) ? $_GET['time'] : 0 );

				$Fish_n_Ships->update_news_opts($key, $when, false);
			}

			//Maybe options has updated? get it again
			$this->options = $Fish_n_Ships->get_options();

			// Is wizard pending to show?
			if ( $this->options['show_wizard'] < time() ) {

				// We are on WooCommerce>Settings>Shipping>Shipping zones ?
				if ( isset($_GET['page'] ) && $_GET['page'] == 'wc-settings' && 
					 isset( $_GET['tab'] ) &&  $_GET['tab'] == 'shipping' && 
					 (!isset( $_GET['section'] ) ||  $_GET['section'] == '') ) {

					// We are on shipping method configuration screen?
					if (isset($_GET['instance_id'])) {
						
						add_action('admin_notices', array( $this, 'woocommerce_fns_wizard_notice_4' ) );
						
					// We are on a shipping zone creation screen?
					} elseif (isset($_GET['zone_id']) && $_GET['zone_id']=='new') {

						add_action('admin_notices', array( $this, 'woocommerce_fns_wizard_notice_3' ) );
						
					// We are on a shipping zone edition screen?
					} elseif (isset($_GET['zone_id'])) {

						add_action('admin_notices', array( $this, 'woocommerce_fns_wizard_notice_2' ) );

					// So, we are on the main shipping zones screen
					} else {
				
						add_action('admin_notices', array( $this, 'woocommerce_fns_wizard_notice_1') );
					}
					
				} else {
				
					// Let's show the welcome after activation message
					add_action('admin_notices', array( $this, 'woocommerce_fns_wizard_notice_0' ) );
				}

			// Is wordpress repository rate pending to show? (only on free version)
			} else if ( $this->options['five_stars'] < time() && !$Fish_n_Ships->im_pro() ) {

				add_action('admin_notices', array( $this, 'woocommerce_fns_five_stars_notice' ) );

			} else {

				// Then maybe should show some fish and ships news?
				add_action('admin_notices', array( $this, 'woocommerce_fns_news' ) );
			}
		}
		
		function woocommerce_fns_five_stars_notice() {

			global $Fish_n_Ships;

			if ( !current_user_can('manage_options') || !$Fish_n_Ships->is_wc() ) return;

			echo '<div class="notice wc-fns-wizard wc-fns-five-stars">'
				//. '<a class="notice-dismiss" href="#">' . esc_html__('Dismiss') . '</a>'
				. '<h3>'. esc_html__('Do you like Fish and Ships?', 'fish-and-ships') . '</h3>'
				. '<p>' . esc_html__('Thank you for your continued use of our plugin.', 'fish-and-ships') . '</p><p>' . 
				wp_kses( __('Please, rate <strong>Fish and Ships</strong> on the WordPress repository, it will help us a lot :)', 'fish-and-ships'),
							 array('strong'=>array())
				) . '</p>'
				. '<p><a href="' . esc_url('https://wordpress.org/support/plugin/fish-and-ships/reviews/?rate=5#new-post') . '" class="button-primary" target="_blank" data-ajax="five-stars" data-param="later">' . esc_html__('Rate the plugin', 'fish-and-ships') . '</a> &nbsp;'
				  . '<a href="' . add_query_arg('wc-fns-five-stars', 'later') . '" class="button" data-ajax="five-stars" data-param="later">' . esc_html__('Remind later', 'fish-and-ships') . '</a> &nbsp;'
				 . '<a href="' . add_query_arg('wc-fns-five-stars', 'off') . '" class="button" data-ajax="five-stars" data-param="off">' . esc_html__('Don\'t show again', 'fish-and-ships') . '</a>'

				  . '</p></div>';
		}

		function woocommerce_fns_news() {

			global $Fish_n_Ships;

			if ( !current_user_can('manage_options') || !$Fish_n_Ships->is_wc() ) return;

			$wc_fns_news = get_option( 'fish-and-ships-woocommerce-news', array() );

			if ( is_array($wc_fns_news) ) {
				foreach ($this->options['closed_news'] as $key => $value ) {
					if ( $value > time() && isset($wc_fns_news[$key]) ) unset ( $wc_fns_news[$key] );
				}

				if (count($wc_fns_news) > 0 ) {

					$notice = reset($wc_fns_news);

					echo '<div class="wc-fns-news notice ' . esc_attr($notice['type']) . '">'
						//. '<a class="notice-dismiss" href="#">' . esc_html__('Dismiss') . '</a>'
						. wp_kses_post($notice['message']);

					$buttons = '';

					if ( isset ($notice['call_to_action']) ) $buttons .= wp_kses_post($notice['call_to_action']) . '&nbsp;';

					if ( isset ($notice['later']) ) $buttons .= '<a href="' . add_query_arg(array('wc-fns-notice-dimiss' => key($wc_fns_news), 'time' => intval($notice['later']) ) ) . '" class="button" data-ajax="fns-news" data-key ="' . esc_html(key($wc_fns_news)) . '" data-param="' . intval($notice['later']) . '">' . esc_html__('Remind later', 'fish-and-ships') . '</a>&nbsp;';

					if ( isset ($notice['dimissable']) ) $buttons .= '<a href="' . add_query_arg(array('wc-fns-notice-dimiss' => key($wc_fns_news), 'time' => 'never' ) ) . '" class="button" data-ajax="fns-news" data-key ="' . esc_html(key($wc_fns_news)) . '" data-param="never">' . esc_html__('Don\'t show again', 'fish-and-ships') . '</a>';

					if ($buttons != '') echo '<p>' . $buttons . '</p>';

					echo '</div>';
				}
			}
		}

		function woocommerce_fns_wizard_notice_0() {

			global $Fish_n_Ships;

			if ( !current_user_can('manage_options') || !$Fish_n_Ships->is_wc() ) return;

			echo '<div class="notice wc-fns-wizard must wc-fns-wizard-notice-0">'
				. '<h3>'. esc_html__('Welcome to Fish and Ships:', 'fish-and-ships') . '</h3>'
				. '<p>' . esc_html__('A WooCommerce shipping method. Easy to understand and easy to use, it gives you an incredible flexibility.', 'fish-and-ships') . '</p>'
			  . '<p><a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&wc-fns-wizard=now') . '" class="button-primary">' . esc_html__('Start wizard', 'fish-and-ships') . '</a> &nbsp;'
				. '<a href="' . add_query_arg('wc-fns-wizard', 'later') . '" class="button" data-ajax="wizard" data-param="later">' . esc_html__('Remind later', 'fish-and-ships') . '</a> &nbsp;'
				. '<a href="' . add_query_arg('wc-fns-wizard', 'off') . '" class="button" data-ajax="wizard" data-param="off">' . esc_html__('Thanks, I know how to use it', 'fish-and-ships') . '</a></p>'
				. '<p class="fns-space-up">' . esc_html__('...or maybe you prefer to see our introductory video before (it will only take 7 minutes):', 'fish-and-ships') . '</p>'
				. '<p><a href="https://www.youtube.com/watch?v=sjQKbt2Nn7k" target="_blank" alt="See video on YouTube" class="fns-video-link"><img src="' . WC_FNS_URL . 'assets/img/video_preview.gif" width="260" height="145" /></a></p>'
			  . '</div>';
		}

		function woocommerce_fns_wizard_notice_1() {

			echo '<div class="notice wc-fns-wizard must wc-fns-wizard-notice-1">'
				. '<h3>' . esc_html__('Fish and Ships Wizard:', 'fish-and-ships') . '</h3>' 
				. '<p>' . esc_html__('First, select one shipping zone, or create a new one:', 'fish-and-ships')
				. '</p></div>';
		}

		function woocommerce_fns_wizard_notice_2() {

			echo '<div class="notice wc-fns-wizard must wc-fns-wizard-notice-2">'
				. '<h3>' . esc_html__('Fish and Ships Wizard:', 'fish-and-ships') . '</h3>' 
				. '<p>' . esc_html__('Now add a new shipping method:', 'fish-and-ships') . ' ' .
				wp_kses( __('add <strong>Fish and Ships</strong>, and edit it.', 'fish-and-ships'),
						 array('strong'=>array())
				) . '</p></div>';
		}

		function woocommerce_fns_wizard_notice_3() {

			echo '<div class="notice wc-fns-wizard must wc-fns-wizard-notice-3">'
				. '<h3>' . esc_html__('Fish and Ships Wizard:', 'fish-and-ships') . '</h3>' 
				. '<p>' . esc_html__('Configure the new zone, and then:', 'fish-and-ships') . ' ' .
				wp_kses( __('add <strong>Fish and Ships</strong>, and edit it.', 'fish-and-ships'),
						 array('strong'=>array())
				) . '</p></div>';
		}

		function woocommerce_fns_wizard_notice_4() {

			echo '<div class="notice wc-fns-wizard must wc-fns-wizard-notice-4">'
				. '<h3>' . esc_html__('Fish and Ships Wizard:', 'fish-and-ships') . '</h3>' 
				. '<p>' . esc_html__('And finally, here you can configure your Fish and Ships shipping method. Please, open the main help:', 'fish-and-ships') . '</p>'
				. '<p><a href="#" class="button-primary woocommerce-fns-help-popup" data-fns-tip="main">' . esc_html__('Open main help', 'fish-and-ships') . '</a> &nbsp;' 
				. '<a href="https://www.youtube.com/watch?v=sjQKbt2Nn7k" target="_blank" title="' . esc_html__('Watch 7 minutes video introduction on YouTube', 'fish-and-ships') . '" class="button"><span class="dashicons-before dashicons-video-alt3 fns-yt-on-button"></span>' . esc_html__('Watch introductory video', 'fish-and-ships') . '</a> &nbsp;' 
				. '<a href="' . add_query_arg('wc-fns-wizard', 'later') . '" class="button" data-ajax="wizard" data-param="later">' . esc_html__('Remind later', 'fish-and-ships') . '</a> &nbsp;'
				. '<a href="' . add_query_arg('wc-fns-wizard', 'off') . '" class="button" data-ajax="wizard" data-param="off">' . esc_html__('Thanks, I know how to use it', 'fish-and-ships') . '</a></p>'
				. '</div>';
		}

	}

	$Fish_n_Ships_Wizard = new Fish_n_Ships_Wizard();
}