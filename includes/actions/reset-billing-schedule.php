<?php

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Reset a subscription's billing schedule.
 *
 *
 * @class Action_Subscription_Reset_Billing_Schedule
 * @since 1.2.2
 */
class Action_Subscription_Reset_Billing_Schedule extends Abstract_Action_Subscription {

	public $old_payment_date = '';


	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Reset Billing Schedule', 'automatewoo-subscriptions' );
		$this->description = __( 'Revert a subscription\'s renewal date to its original schedule .', 'automatewoo-subscriptions' );
	}

	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @throws \Exception When there is an error.
	 */
	public function edit_subscription( $subscription ) {
		$old_renewal_date 		= $subscription->get_meta( '_old_schedule_next_payment' );
		$billing_interval 		= $subscription->get_billing_interval();
		$billing_period 		= $subscription->get_billing_period();
		$new_payment_date		= date( 'Y-m-d H:i:s', wcs_add_time( $billing_interval, $billing_period, wcs_date_to_time( $old_renewal_date ) ) );
		$this->old_payment_date = $subscription->get_date( 'next_payment' );
		$subscription->update_dates(
			array(
				'next_payment' => $new_payment_date,
			)
		);
		$subscription->delete_meta_data( '_old_schedule_next_payment' );
	}


	/**
	 * Get a message to add to the subscription to record the shipping being added by this action.
	 *
	 * Helpful for tracing the history of this action by viewing the subscription's notes.
	 *
	 * @param \WC_Product $shipping Product being added to the subscription. Required so its name can be added to the order note.
	 * @return string
	 */
	protected function get_note( $subscription ) {
		return sprintf( __( '%1$s workflow run: updated next payment date on subscription from %2$s to %3$s', 'automatewoo-subscriptions' ), $this->workflow->get_title(), $this->old_payment_date, $subscription->get_date( 'next_payment' ) );
	}
}
