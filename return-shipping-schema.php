<?php
/**
 * Plugin Name:      Return and Shipping Schema for WooCommerce
 * Description:      Adds hasMerchantReturnPolicy and per-country shippingDetails to the Offer in WooCommerce's product structured data.
 * Version:          1.0.0
 * Author:           Robert Harris
 * License:          GPL-2.0-or-later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * All values in one place. They end up in markup that search engines read,
 * so change them to match your store's real policy before you activate the plugin.
 *
 * destinations: country code => array( min transit days, max transit days, shipping rate or null )
 */
function rss_wc_settings() {
	return apply_filters(
		'rss_wc_settings',
		array(
			'return_days'      => 30,
			'return_countries' => array( 'US' ),
			'handling_days'    => array( 1, 5 ),
			'currency'         => 'USD',
			'destinations'     => array(
				'US' => array( 8, 15, null ),
				'CA' => array( 8, 15, null ),
				'GB' => array( 8, 15, null ),
				'AU' => array( 10, 20, null ),
			),
		)
	);
}

function rss_wc_return_policy( array $s ) {
	$countries = array_values( $s['return_countries'] );

	return array(
		'@type'                => 'MerchantReturnPolicy',
		'applicableCountry'    => 1 === count( $countries ) ? $countries[0] : $countries,
		'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
		'merchantReturnDays'   => (int) $s['return_days'],
		'returnMethod'         => 'https://schema.org/ReturnByMail',
	);
}

function rss_wc_shipping_details( array $s ) {
	$details = array();

	foreach ( $s['destinations'] as $country => $d ) {
		list( $min, $max, $rate ) = array_pad( $d, 3, null );

		$entry = array(
			'@type'               => 'OfferShippingDetails',
			'shippingDestination' => array(
				'@type'          => 'DefinedRegion',
				'addressCountry' => $country,
			),
			'deliveryTime'        => array(
				'@type'        => 'ShippingDeliveryTime',
				'handlingTime' => array(
					'@type'    => 'QuantitativeValue',
					'minValue' => (int) $s['handling_days'][0],
					'maxValue' => (int) $s['handling_days'][1],
					'unitCode' => 'DAY',
				),
				'transitTime'  => array(
					'@type'    => 'QuantitativeValue',
					'minValue' => (int) $min,
					'maxValue' => (int) $max,
					'unitCode' => 'DAY',
				),
			),
		);

		if ( null !== $rate ) {
			$entry['shippingRate'] = array(
				'@type'    => 'MonetaryAmount',
				'value'    => (string) $rate,
				'currency' => $s['currency'],
			);
		}

		$details[] = $entry;
	}

	return $details;
}

add_filter(
	'woocommerce_structured_data_product_offer',
	function ( $offer, $product ) {
		if ( ! $product instanceof WC_Product || ! $product->is_purchasable() ) {
			return $offer;
		}

		$s = rss_wc_settings();

		$offer['hasMerchantReturnPolicy'] = rss_wc_return_policy( $s );
		$offer['shippingDetails']         = rss_wc_shipping_details( $s );

		return $offer;
	},
	10,
	2
);
