<?php

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;


/**
 * Define shared methods to add, remove or update shipping line items on a subscription.
 *
 * @class Abstract_Action_Subscription_Edit_Shipping
 * @since 1.0
 */
abstract class Abstract_Action_Subscription_Edit_Renewal extends \AutomateWoo\Action {


	/**
	 * A subscription is needed so that it can be edited by instances of this action.
	 *
	 * @var array
	 */
	public $required_data_items = [ 'subscription' ];

	public function load_admin_details() {
		parent::load_admin_details();
		$this->group = __( 'Subscription', 'automatewoo' );
	}

	/**
	 * Get the subscription passed in by the workflow's trigger.
	 *
	 * @return \WC_Subscription|false
	 */
	protected function get_subscription_to_edit() {
		return $this->workflow->data_layer()->get_subscription();
	}

}
