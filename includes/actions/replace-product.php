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
	 * Load input fields required for action in admin.
	 *
	 * @return void
	 */
	public function load_fields() {
		$this->add_old_product_select_field();
		$this->add_new_product_select_field();
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
	 * Flag to define whether variable products should be included in search results for the
	 * product select field.
	 *
	 * @var bool
	 */
	protected $allow_variable_products = true;


	/**
	 * Edit the item managed by this class on the subscription passed in the workflow's trigger
	 *
	 * @param WC_Subscription $subscription The subscription object.
	 * @throws \Exception When there is an error.
	 */
	public function edit_subscription( $subscription ) {

		$old_product_id    = absint( $this->get_data()['old_product_id'] );
		$new_product_id    = absint( $this->get_data()['updated_product_id'] );
		$new_product       = wc_get_product( $new_product_id );
		$old_product_title = wc_get_product( $old_product_id )->get_title();
		$new_product_title = $new_product->get_title();
		$subscription->update_meta_data( '_old_product_title', $old_product_title );
		$subscription->update_meta_data( '_new_product_title', $new_product_title );
		$subscription->save();
		$new_product_price = $new_product->get_price();
		$add_product_args  = array();

		foreach ( $subscription->get_items() as $item ) {
			$item_product_id = $item->get_product_id();
			$item_quantity   = $item->get_quantity();
			if ( $old_product_id === $item_product_id ) {
				$subscription->remove_item( $item->get_id() );
				$add_product_args['subtotal'] = wc_get_price_excluding_tax(
					$new_product,
					array(
						'price' => $new_product_price,
						'qty'   => $item_quantity,
					)
				);
				$add_product_args['total']    = $add_product_args['subtotal'];
				$subscription->add_product( $new_product, $item_quantity, $add_product_args );
				$this->recalculate_subscription_totals( $subscription );
			}
		}
	}

	/**
	 * Add a product selection field for this action
	 */
	protected function add_old_product_select_field() {
		$old_product_select = new \AutomateWoo\Fields\Product();
		$old_product_select->set_required();
		$old_product_select->set_allow_variations( true );
		$old_product_select->set_allow_variable( $this->allow_variable_products );
		$old_product_select->set_title( 'Old Product' );
		$old_product_select->set_name( 'old_product_id' );

		$this->add_field( $old_product_select );
	}

	/**
	 * Add a product selection field for this action
	 */
	protected function add_new_product_select_field() {
		$new_product_select = new \AutomateWoo\Fields\Product();
		$new_product_select->set_required();
		$new_product_select->set_allow_variations( true );
		$new_product_select->set_allow_variable( $this->allow_variable_products );
		$new_product_select->set_title( 'New Product' );
		$new_product_select->set_name( 'updated_product_id' );

		$this->add_field( $new_product_select );
	}

	/**
	 * Store data required to replace the product in a subscription.
	 *
	 * @return array
	 */
	private function get_data() {
		return array(
			'old_product_id'  => $this->get_option( 'old_product_id' ),
			'updated_product' => $this->get_option( 'updated_product' ),
		);
	}

	/**
	 * Recalculate a subscription's totals.
	 *
	 * @param \WC_Subscription $subscription
	 *
	 * @since 4.8.0
	 */
	protected function recalculate_subscription_totals( $subscription ) {
		if ( is_callable( array( $subscription, 'recalculate_coupons' ) ) ) {
			$subscription->recalculate_coupons();
		} else {
			$subscription->calculate_totals();
		}
	}

	/**
	 * Create a note recording the subscription and workflow name to add after replacing product.
	 *
	 * Helpful for tracing the history of this action by viewing the subscription's notes.
	 *
	 * @param object $subscription Subscription data. Same data as the return value of @see $this->get_object_for_edit().
	 * @return string
	 */
	protected function get_note( $subscription ) {
		return sprintf( __( '%1$s workflow run: Replaced %2$s with %3$s', 'automatewoo-subscriptions' ), $this->workflow->get_title(), $subscription->get_meta( '_old_product_title' ), $subscription->get_meta( '_new_product_title' ) );
	}
}
