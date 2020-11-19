<?php

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Reset a subscription's billing schedule.
 *
 *
 * @class Action_Subscription_Revert_Renewal_Date
 * @since 1.0.0
 */
class Action_Subscription_Reset_Billing_Schedule extends Abstract_Action_Subscription {


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
	public function run() {
		$subscription 		= $this->get_subscription_to_edit();
		$old_renewal_date 	= $subscription->get_meta( '_old_schedule_next_payment' );
		$billing_interval 	= $subscription->get_billing_interval();
		$billing_period 	= $subscription->get_billing_period();
		$new_payment_date	= date( 'Y-m-d H:i:s', wcs_add_time( $billing_interval, $billing_period, wcs_date_to_time( $old_renewal_date ) ) );
		$subscription->update_dates(
			array(
				'next_payment' => $new_payment_date,
			)
		);
		$subscription->delete_meta_data( '_old_schedule_next_payment' );
	}
}
