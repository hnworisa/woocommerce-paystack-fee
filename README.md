# Paystack Transaction Fee for WooCommerce

**Contributors:** Chinemerem Nworisa  
**Tags:** WooCommerce, Paystack, Payment Gateway, Fees  
**Requires at least:** 5.0  
**Tested up to:** 6.1  
**Stable tag:** 1.0.0  
**License:** GPL-3.0+  
**License URI:** https://opensource.org/licenses/GPL-3.0

## Description

The **Paystack Transaction Fee for WooCommerce** plugin adds Paystack transaction fees to the WooCommerce cart total. This allows you to pass Paystack's transaction fees on to the customer during checkout. It provides options for including taxes, shipping, and setting fee caps to control how much is charged.

## Installation

1. **Download the Plugin**: Download the [latest release](https://github.com/hnworisa/woocommerce-paystack-fee/releases/latest) from GitHub (use the asset named `woocommerce-paystack-fee-{version}.zip`).

2. **Upload the Plugin**:
   - Go to `Plugins > Add New` in your WordPress admin panel.
   - Click the "Upload Plugin" button at the top.
   - Upload the zipped archive you downloaded.

3. **Activate the Plugin**: After the upload completes, click the "Activate Plugin" link to enable the plugin.

4. **Configure the Settings**: Go to `WooCommerce > Settings > Paystack Transaction Fee` to configure the plugin settings.

## Usage

1. **Configure Fees**: Navigate to `WooCommerce > Settings > Paystack Transaction Fee` to set your desired fee percentage, flat fee, and cap. You can also choose whether to include taxes and shipping in the fee calculation.
2. **Set Fee Label**: Customize the label that will be displayed for the transaction fee in the cart and checkout pages.

## Changelog

### 1.0.0
* Initial release of the Paystack Transaction Fee for WooCommerce plugin.

## Frequently Asked Questions

### How do I change the transaction fee percentage?

1. Go to `WooCommerce > Settings > Paystack Transaction Fee`.
2. Adjust the "Fee Percentage" field to your desired value.

### Can I exclude certain products from the transaction fee?

Currently, the plugin applies fees to the entire cart. Future versions may include this feature based on user feedback.


## Development

If you’d like to contribute to this plugin, please fork the repository on [GitHub](https://github.com/hnworisa/woocommerce-paystack-fee) and submit a pull request.

## License

This plugin is licensed under the GPL-3.0+ license. See the [LICENSE](LICENSE) file for more details.

## Acknowledgements

Special thanks to the developers of WooCommerce and Paystack for their excellent platforms.
