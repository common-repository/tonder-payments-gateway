<?php

/**
 * Plugin Name: tonder 
 * Plugin URI: https://tonder.io/
 * Author: tonder
 * Author URI: https://tonder.io/
 * Description: Local Payments Gateway for tonder.
 * Version: 1.0.4
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: tonder-woo
 *
 * Class WC_Gateway_Zplit file.
 *
 * @package WooCommerce\Zplit
 */

// use Automattic\Jetpack\Constants;
add_filter('allowed_http_origins', 'tonder_api_origins');
/**
 * Add origins for CORS
 */
function tonder_api_origins($origins)
{
	$origins[] = 'https://stage.tonder.io/';
	$origins[] = 'https://app.tonder.io/';
	return $origins;
}


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	return;
}

/**
 * Cash on Delivery Gateway.
 *
 * Provides a Cash on Delivery Payment Gateway.
 *
 * @class       WC_Gateway_Zplit
 * @extends     WC_Payment_Gateway
 * @version     0.1.0
 * @package     WooCommerce\Classes\Payment
 */

add_filter('woocommerce_payment_gateways', 'tonder_add_payment_gateway');

function tonder_add_payment_gateway($gateways)
{
	$gateways[] = 'WC_Gateway_Zplit';
	return $gateways;
}

add_action( 'plugins_loaded', 'tonder_init_custom_payment_gateway_class' );

function tonder_init_custom_payment_gateway_class() {
	class WC_Gateway_Zplit extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct()
		{
			// Setup general properties.
			$this->setup_properties();

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Get settings.
			$this->title           = "Pagar con tarjeta de crédito/débito";
			$this->enabled         = $this->get_option('enabled');
			$this->api_key         = $this->get_option('api_key');

			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
			add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);
			// Js
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			// Customer Emails.
			add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
		}

		/**
		 * Setup general properties for the gateway.
		 */

		protected function setup_properties()
		{
			$this->id                 = 'zplit';
			$this->method_title       = __('tonder Pagos', 'woocommerce');
			$this->method_description = __('Permite que los clientes usen tonder como método de pago', 'woocommerce');
			$this->has_fields         = true;
			$this->icon               = apply_filters('woocommerce_zplit_icon', plugins_url('/assets/img/logo-payments.png', __FILE__));
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */

		# https://woocommerce.wp-a2z.org/oik_api/wc_gateway_codinit_form_fields/

		public function init_form_fields()
		{
			$this->form_fields = array(
				'enabled'            => array(
					'title'       => __('Enable/Disable', 'woocommerce'),
					'label'       => __('Enable payments with tonder', 'woocommerce'),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'api_key'              => array(
					'title'       => __('API key', 'woocommerce'),
					'type'        => 'text',
					'description' => __('Introduce your API KEY from your Tonder dashboard', 'woocommerce'),
					'default'     => __('XXXXXXXXXXXXXXXX', 'woocommerce'),
					'desc_tip'    => true,
				)
			);
		}

		/**
		 * Check If The Gateway Is Available For Use.
		 *
		 * @return bool
		 */
		public function is_available()
		{
			$order          = null;
			$needs_shipping = false;

			// Test if shipping is needed first.
			if (WC()->cart && WC()->cart->needs_shipping()) {
				$needs_shipping = true;
			} elseif (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay')) {
				$order_id = absint(get_query_var('order-pay'));
				$order    = wc_get_order($order_id);

				// Test if order needs shipping.
				if ($order && 0 < count($order->get_items())) {
					foreach ($order->get_items() as $item) {
						$_product = $item->get_product();
						if ($_product && $_product->needs_shipping()) {
							$needs_shipping = true;
							break;
						}
					}
				}
			}

			$needs_shipping = apply_filters('woocommerce_cart_needs_shipping', $needs_shipping);

			// Virtual order, with virtual disabled.
			// if (!$this->enable_for_virtual && !$needs_shipping) {
			// 	return false;
			// }

			// Only apply if all packages are being shipped via chosen method, or order is virtual.
			if (!empty($this->enable_for_methods) && $needs_shipping) {
				$order_shipping_items            = is_object($order) ? $order->get_shipping_methods() : false;
				$chosen_shipping_methods_session = WC()->session->get('chosen_shipping_methods');

				if ($order_shipping_items) {
					$canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids($order_shipping_items);
				} else {
					$canonical_rate_ids = $this->get_canonical_package_rate_ids($chosen_shipping_methods_session);
				}

				if (!count($this->get_matching_rates($canonical_rate_ids))) {
					return false;
				}
			}

			return parent::is_available();
		}

		public function payment_scripts() {

			if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
				return;
			}

			if (!is_admin()) {
				wp_enqueue_script('jquery');
			}

			wp_register_script(
				'openpay',
				plugins_url( '/assets/js/openpay.v1.min.js', __FILE__ ),
				array('jquery')
			);

			wp_register_script(
				'openpay-data',
				plugins_url( '/assets/js/openpay-data.v1.min.js', __FILE__ ),
				array('jquery')
			);

			wp_enqueue_script('skyflow-js', 'https://js.skyflow.com/v1/index.js');
			wp_enqueue_script( 'openpay');
			wp_enqueue_script( 'openpay-data');
		}

		public function payment_fields() {
			include_once('includes/tokenization-process.php');
		}

		/**
		 * Checks to see whether or not the admin settings are being accessed by the current request.
		 *
		 * @return bool
		 */
		private function is_accessing_settings()
		{
			if (is_admin()) {
				// phpcs:disable WordPress.Security.NonceVerification
				if (!isset($_REQUEST['page']) || 'wc-settings' !== $_REQUEST['page']) {
					return false;
				}
				if (!isset($_REQUEST['tab']) || 'checkout' !== $_REQUEST['tab']) {
					return false;
				}
				if (!isset($_REQUEST['section']) || 'zplit' !== $_REQUEST['section']) {
					return false;
				}
				// phpcs:enable WordPress.Security.NonceVerification

				return true;
			}

			// if ( Constants::is_true( 'REST_REQUEST' ) ) {
			// 	global $wp;
			// 	if ( isset( $wp->query_vars['rest_route'] ) && false !== strpos( $wp->query_vars['rest_route'], '/payment_gateways' ) ) {
			// 		return true;
			// 	}
			// }

			return false;
		}

		/**
		 * Loads all of the shipping method options for the enable_for_methods field.
		 *
		 * @return array
		 */
		private function load_shipping_method_options()
		{
			// Since this is expensive, we only want to do it if we're actually on the settings page.
			if (!$this->is_accessing_settings()) {
				return array();
			}

			$data_store = WC_Data_Store::load('shipping-zone');
			$raw_zones  = $data_store->get_zones();

			foreach ($raw_zones as $raw_zone) {
				$zones[] = new WC_Shipping_Zone($raw_zone);
			}

			$zones[] = new WC_Shipping_Zone(0);

			$options = array();
			foreach (WC()->shipping()->load_shipping_methods() as $method) {

				$options[$method->get_method_title()] = array();

				// Translators: %1$s shipping method name.
				$options[$method->get_method_title()][$method->id] = sprintf(__('Any &quot;%1$s&quot; method', 'woocommerce'), $method->get_method_title());

				foreach ($zones as $zone) {

					$shipping_method_instances = $zone->get_shipping_methods();

					foreach ($shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance) {

						if ($shipping_method_instance->id !== $method->id) {
							continue;
						}

						$option_id = $shipping_method_instance->get_rate_id();

						// Translators: %1$s shipping method title, %2$s shipping method id.
						$option_instance_title = sprintf(__('%1$s (#%2$s)', 'woocommerce'), $shipping_method_instance->get_title(), $shipping_method_instance_id);

						// Translators: %1$s zone name, %2$s shipping method instance name.
						$option_title = sprintf(__('%1$s &ndash; %2$s', 'woocommerce'), $zone->get_id() ? $zone->get_zone_name() : __('Other locations', 'woocommerce'), $option_instance_title);

						$options[$method->get_method_title()][$option_id] = $option_title;
					}
				}
			}

			return $options;
		}

		/**
		 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
		 *
		 * @since  3.4.0
		 *
		 * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
		 * @return array $canonical_rate_ids    Rate IDs in a canonical format.
		 */
		private function get_canonical_order_shipping_item_rate_ids($order_shipping_items)
		{

			$canonical_rate_ids = array();

			foreach ($order_shipping_items as $order_shipping_item) {
				$canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
			}

			return $canonical_rate_ids;
		}

		/**
		 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
		 *
		 * @since  3.4.0
		 *
		 * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
		 * @return array $canonical_rate_ids  Rate IDs in a canonical format.
		 */
		private function get_canonical_package_rate_ids($chosen_package_rate_ids)
		{

			$shipping_packages  = WC()->shipping()->get_packages();
			$canonical_rate_ids = array();

			if (!empty($chosen_package_rate_ids) && is_array($chosen_package_rate_ids)) {
				foreach ($chosen_package_rate_ids as $package_key => $chosen_package_rate_id) {
					if (!empty($shipping_packages[$package_key]['rates'][$chosen_package_rate_id])) {
						$chosen_rate          = $shipping_packages[$package_key]['rates'][$chosen_package_rate_id];
						$canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
					}
				}
			}

			return $canonical_rate_ids;
		}

		/**
		 * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
		 *
		 * @since  3.4.0
		 *
		 * @param array $rate_ids Rate ids to check.
		 * @return boolean
		 */
		private function get_matching_rates($rate_ids)
		{
			// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
			return array_unique(array_merge(array_intersect($this->enable_for_methods, $rate_ids), array_intersect($this->enable_for_methods, array_unique(array_map('wc_get_string_before_colon', $rate_ids)))));
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id Order ID.
		 * @return array
		 */
		public function process_payment($order_id)
		{
			$order = wc_get_order( $order_id );

			$order->payment_complete();

			$order->reduce_order_stock();

			$order->add_order_note( 'Order by Tonder', true );

			// Remove cart.
			WC()->cart->empty_cart();

			// Return thankyou redirect.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url($order),
			);
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page(){

		}
		/**
		 * Change payment complete order status to completed for Zplit orders.
		 *
		 * @since  3.1.0
		 * @param  string         $status Current order status.
		 * @param  int            $order_id Order ID.
		 * @param  WC_Order|false $order Order object.
		 * @return string
		 */
		public function change_payment_complete_order_status($status, $order_id = 0, $order = false)
		{
			if ($order && 'zplit' === $order->get_payment_method()) {
				$status = 'processing';
			}
			return $status;
		}

		/**
		 * Add content to the WC emails.
		 *
		 * @param WC_Order $order Order object.
		 * @param bool     $sent_to_admin  Sent to admin.
		 * @param bool     $plain_text Email format: plain text or HTML.
		 */
		public function email_instructions($order, $sent_to_admin, $plain_text = false)
		{
			if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method()) {
				echo wp_kses_post(wpautop(wptexturize($this->instructions)) . PHP_EOL);
			}
		}

	}
}