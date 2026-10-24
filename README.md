# Return and Shipping Schema for WooCommerce

A small WordPress plugin that adds a merchant return policy and per-country shipping details to the Offer inside WooCommerce's product structured data (JSON-LD).

WooCommerce prints Product markup on its own, but it leaves out these two properties. Google's merchant listings read them to show a return window and delivery times next to a product.

## What it adds

- `hasMerchantReturnPolicy`: a finite return window (30 days by default) with returns by mail
- `shippingDetails`: one `OfferShippingDetails` per destination country, with handling and transit time, plus a shipping rate when you set one

It hooks into `woocommerce_structured_data_product_offer`, so nothing else in the theme or in WooCommerce's output changes.

## Install

1. Put `return-shipping-schema.php` in a folder called `return-shipping-schema` under `wp-content/plugins/`.
2. Set your own values first (see below). The defaults are an example, not your policy.
3. Activate "Return and Shipping Schema for WooCommerce" on the Plugins screen.

## Settings

Every value comes from one array, and the `rss_wc_settings` filter lets you replace any part of it from your theme or a site plugin:

```php
add_filter( 'rss_wc_settings', function ( $s ) {
	$s['return_days']  = 14;
	$s['destinations'] = array(
		'US' => array( 3, 7, 4.99 ),
		'DE' => array( 5, 10, 9.99 ),
	);
	return $s;
} );
```

A destination is `country code => array( min transit days, max transit days, rate )`. A rate of `null` leaves `shippingRate` out, which is better than publishing a number that is not true for every product.

## Checking the output

Open a product page, view its source and search for `application/ld+json`. Then test the product URL with the Schema Markup Validator and with Rich Results Test from Google.

## Credits

Written by Robert Harris for the [Kawaiiki shop](https://kawaiiki.com/), a kawaii clothing store that runs on WooCommerce. The default figures are that store's return window and delivery estimates.
