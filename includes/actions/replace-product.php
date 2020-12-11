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
 * @since 1.2.3
 */
class Action_Subscription_Replace_Product extends Abstract_Action_Subscription {

	/**
	 * Old product ID. The product to be replaced.
	 *
	 * @var int
	 */
	private $old_product_id;

	/**
	 * New product ID. The product to be replaced with.
	 *
	 * @var int
	 */
	private $new_product_id;

	/**
	 * Set action parameters.
	 */
	private function set_parameters() {
		$this->old_product_id = absint( $this->get_option( 'old_product_id' ) );
		$this->new_product_id = absint( $this->get_option( 'new_product_id' ) );
	}

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
		$this->add_field(
			( new \AutomateWoo\Fields\Product() )
				->set_title( __( 'Old Product', 'automatewoo-subscriptions' ) )
				->set_name( 'old_product_id' )
				->set_required()
				->set_allow_variations( true )
				->set_allow_variable( $this->allow_variable_products )
		);

		$this->add_field(
			( new \AutomateWoo\Fields\Product() )
				->set_title( __( 'New Product', 'automatewoo-subscriptions' ) )
				->set_name( 'new_product_id' )
				->set_required()
				->set_allow_variations( true )
				->set_allow_variable( $this->allow_variable_products )
		);
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
		$this->set_parameters();

		if ( $this->old_product_id === $this->new_product_id ) {
			throw new \Exception( __( 'Both the products are same', 'automatewoo-subscriptions' ) );
		}

		$old_product = wc_get_product( $this->old_product_id );
		$new_product = wc_get_product( $this->new_product_id );

		if ( ! $old_product ) {
			throw new \Exception( __( 'Invalid product to be removed', 'automatewoo-subscriptions' ) . ' ' . $old_product );
		}

		if ( ! $new_product ) {
			throw new \Exception( __( 'Invalid product to be added', 'automatewoo-subscriptions' ) . ' ' . $new_product );
		}

		if ( 'variable' === $new_product->get_type() ) {
			/* translators: %s product name */
			throw new \Exception( sprintf( __( '%s is a variable product parent and cannot be added.', 'automatewoo-subscriptions' ), $new_product->get_name() ) );
		}

		$qty = 0;

		foreach ( $subscription->get_items() as $item ) {
			$item_product_id = $item->get_product_id();

			if ( $this->old_product_id !== $item_product_id ) {
				continue;
			}

			$qty = $item->get_quantity();
			$subscription->remove_item( $item->get_id() );
			break;
		}

		if ( $qty > 0 ) {
			$item_id = $subscription->add_product( $new_product, $qty );
			$item    = new \WC_Order_Item_Product( $item_id );

			$item->update_meta_data( '_wcsatt_scheme', $this->get_subscription_scheme( $subscription ) );
			$item->save();
			$subscription->calculate_totals( true );
		} else {
			throw new \Exception( sprintf( __( 'Product to be replaced not found in the subscription', 'automatewoo-subscriptions' ) . ' ' . $old_product->get_name() ) );
		}
	}

	/**
	 * Reapply coupons.
	 *
	 * @param  WC_Subscription $subscription The subscription object.
	 */
	private function reapply_coupons( $subscription ) {
		$coupons = $subscription->get_coupon_codes();
		foreach ( $coupons as $coupon_code ) {
			$subscription->remove_coupon( wc_format_coupon_code( $coupon_code ) );
		}
		foreach ( $coupons as $coupon_code ) {
			$subscription->apply_coupon( wc_format_coupon_code( $coupon_code ) );
		}
	}

	/**
	 * Get subscription scheme.
	 *
	 * @param  WC_Subscription $subscription The susbcription object.
	 * @return string
	 */
	private function get_subscription_scheme( $subscription ) {
		$subscription_id = $subscription->get_id();

		return sprintf(
			'%s_%s',
			get_post_meta( $subscription_id, '_billing_interval', true ),
			get_post_meta( $subscription_id, '_billing_period', true )
		);
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
		return sprintf(
			/* translators: %1$s: Workflow title, %2$s: Old product title, %3$s: New product title */
			__( '%1$s workflow run: Replaced %2$s with %3$s', 'automatewoo-subscriptions' ),
			$this->workflow->get_title(),
			wc_get_product( $this->old_product_id )->get_formatted_name(),
			wc_get_product( $this->new_product_id )->get_formatted_name()
		);
	}
}
