<?php
/**
 * Class Action_Subscription_Reapply_Coupons
 *
 * @package AutomateWoo Subscriptions
 */

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Action to update a chosen product line item to a subscription with a chosen quantity.
 *
 * @class Action_Subscription_Reapply_Coupons
 * @since 1.2.3
 */
class Action_Subscription_Reapply_Coupons extends Abstract_Action_Subscription {

	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Reapply Coupons', 'automatewoo-subscriptions' );
		$this->description = __( 'Removes and then apply coupons on a subscription', 'automatewoo-subscriptions' );
	}

	/**
	 * A subscription is needed so that it can be edited by instances of this action.
	 *
	 * @var array
	 */
	public $required_data_items = array( 'subscription' );

	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @param WC_Subscription $subscription The subscription object.
	 * @throws \Exception When there is an error.
	 */
	public function edit_subscription( $subscription ) {
		$coupons = $subscription->get_coupon_codes();
		foreach ( $coupons as $coupon_code ) {
			$subscription->remove_coupon( wc_format_coupon_code( $coupon_code ) );
		}
		foreach ( $coupons as $coupon_code ) {
			$subscription->apply_coupon( wc_format_coupon_code( $coupon_code ) );
		}
		$subscription->calculate_totals( false );
	}

	/**
	 * Create a note recording the subscription and workflow name to add after replacing product.
	 *
	 * Helpful for tracing the history of this action by viewing the subscription's notes.
	 *
	 * @param WC_Subscription $subscription Subscription data. Same data as the return value of @see $this->get_object_for_edit().
	 * @return string
	 */
	protected function get_note( $subscription ) {
		/* translators: %1$s: Workflow title */
		return sprintf( __( '%1$s workflow run: Reapplied coupons', 'automatewoo-subscriptions' ), $this->workflow->get_title() );
	}
}
