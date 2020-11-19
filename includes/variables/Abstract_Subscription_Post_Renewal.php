<?php

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Class Variable_Abstract_Subscription_Post_Renewal
 *
 * @package AutomateWoo
 */
class Variable_Abstract_Subscription_Post_Renewal extends AutomateWoo\includes\Variable {
/**
	 * Load admin details.
	 */
	public function load_admin_details() {
		$this->add_parameter_text_field( 'key', __( 'The meta_key of the field you would like to display.', 'automatewoo' ), true );
	}
}
