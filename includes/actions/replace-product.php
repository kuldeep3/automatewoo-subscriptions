<?php
/**
 * Class Action_Subscription_Replace_Product
 *
 * @package AutomateWoo Subscriptions
 */

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Action to update a chosen product line item to a subscription with a chosen quantity.
 *
 * @class Action_Subscription_Replace_Product
 * @since 4.4
 */
class Action_Subscription_Replace_Product extends Abstract_Action_Subscription {

	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Replace Product', 'automatewoo-subscriptions' );
		$this->description = __( 'Replace an existing product line item on a subscription with a new product line item. Keeps the quantity of the new line item same as that of the old line item.', 'automatewoo-subscriptions' );
	}

	/**
	 * A subscription is needed so that it can be edited by instances of this action.
	 *
	 * @var array
	 */
	public $required_data_items = array( 'subscription' );

	/**
	 * Flag to define whether the quantity input field should be marked as required.
	 *
	 * @var bool
	 */
	protected $require_quantity_field = false;

	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @param WC_Subscription $subscription The subscription object.
	 * @throws \Exception When there is an error.
	 */
	public function edit_subscription( $subscription ) {
	}
}
