<?php

namespace AutomateWoo_Subscriptions;

defined( 'ABSPATH' ) || exit;

/**
 * Change a subscription's renewal date.
 *
 * This class extends Abstract_Action_Subscription
 * as it provides many useful methods for editing a subscription's renewal date.
 *
 * @class Action_Subscription_Update_Next_Payment
 * @since 1.0.0
 */
class Action_Subscription_Revert_Renewal_Date extends Abstract_Action_Subscription_Edit_Renewal {


	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       	= __( 'Revert Post Renewal Date', 'automatewoo-subscriptions' );
		$this->description 	= __( 'Revert a subscription\'s renewal date to its original schedule .', 'automatewoo-subscriptions' );
	}

	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @throws \Exception When there is an error.
	 */
	public function run() {
		$subscription 		= $this->get_subscription_to_edit();
		$subscription_id 	= $subscription->get_id();
		$old_renewal_date 	= strtotime( implode( " ", get_post_meta( $subscription_id, '_old_renewal_date' ) ) );
		$billing_interval 	= get_post_meta( $subscription_id, '_billing_interval' );
		$billing_period 	= get_post_meta( $subscription_id, '_billing_period' );
		$new_renewal_date 	= date( 'Y-m-d H:i:s', wcs_add_time( implode( " ", $billing_interval ), implode( " ", $billing_period ), $old_renewal_date ) );
		$new_renewal_date_string = wcs_get_datetime_from( $new_renewal_date );
		$subscription->update_dates(
			array(
				'next_payment' => wcs_get_datetime_utc_string( $new_renewal_date_string ),
			)
		);
		delete_post_meta( $subscription_id, '_old_renewal_date' );
	}
}
