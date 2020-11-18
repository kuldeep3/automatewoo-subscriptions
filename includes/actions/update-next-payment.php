<?php

namespace AutomateWoo_Subscriptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Change a subscription's currency.
 *
 * While the currency is not a line item, this class still extends Action_Subscription_Edit_Item_Abstract
 * as it provides many useful methods for editing a subscription's currency.
 *
 * @class Action_Subscription_Update_Next_Payment
 * @since 1.0.0
 */
class Action_Subscription_Update_Next_Payment extends \AutomateWoo\Action {

	/**
	 * A subscription is needed so that it can be edited by instances of this action.
	 *
	 * @var array
	 */
	public $required_data_items = [ 'subscription' ];


	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Update Next Payment', 'automatewoo-subscriptions' );
		$this->description = __( 'Change a subscription\'s next payment date.', 'automatewoo-subscriptions' );
		$this->group = __( 'Subscription', 'automatewoo' );
	}

	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @throws \Exception When there is an error.
	 */
	public function run() {
		$subscription = $this->get_subscription_to_edit();

		if ( ! $object || ! $subscription ) {
			return;
		}

		$this->edit_subscription( $object, $subscription );
		$this->add_note( $object, $subscription );
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
