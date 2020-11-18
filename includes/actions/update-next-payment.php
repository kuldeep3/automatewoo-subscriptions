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
		$this->title       	= __( 'Update Next Payment', 'automatewoo-subscriptions' );
		$this->description 	= __( 'Change a subscription\'s next payment date.', 'automatewoo-subscriptions' );
		$this->group 		= __( 'Subscription', 'automatewoo' );
	}

	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @throws \Exception When there is an error.
	 */
	public function run() {
		$subscription 		= $this->get_subscription_to_edit();
		$subscription_id 	= $subscription->get_id();
		$old_payment_date 	= get_post_meta( $subscription_id, '_schedule_next_payment' );
		update_post_meta( $subscription_id, '_old_renewal_date', implode( " ", $old_payment_date ) );
		$date_string = sprintf( '%1$s %2$s', $this->get_option( 'next_payment_date' ), implode(":", $this->get_option( 'next_payment_time' ) ).":00" );
		$new_payment_date_string = wcs_get_datetime_from( $date_string );
		$subscription->update_dates(
			array(
				'next_payment' => wcs_get_datetime_utc_string( $new_payment_date_string ),
			)
		);
	}

	/**
	 * Get the subscription passed in by the workflow's trigger.
	 *
	 * @return \WC_Subscription|false
	 */
	protected function get_subscription_to_edit() {
		return $this->workflow->data_layer()->get_subscription();
	}

	function load_fields() {
		$this->add_payment_fields();
	}

	protected function add_payment_fields() {
		$date = new \AutomateWoo\Fields\Date();
		$date->set_required();
		$date->set_name( 'next_payment_date' );
		$date->set_title( __( 'Next Payment Date', 'automatewoo-subscriptions' ) );
		$this->add_field( $date );

		$time = new \AutomateWoo\Fields\Time();
		$time->set_required();
		$time->set_name( 'next_payment_time' );
		$time->set_title( __( 'Next Payment Time', 'automatewoo-subscriptions' ) );
		$this->add_field( $time );
	}
}
