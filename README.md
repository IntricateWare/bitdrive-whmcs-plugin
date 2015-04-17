# bitdrive-whmcs-plugin
## BitDrive payment method plugin for WHMCS

Accept bitcoin payments on WHMCS using BitDrive Standard Checkout. Includes support for the BitDrive Instant Payment Notification (IPN) messages.

### Minimum Requirements
* PHP 5.2+
* WHMCS 5.1+

### Quick Installation
1. Extract the files in the archive to the WHMCS root path.
2. Log in to the WHMCS admin area and navigate to **Setup > Payments > Payment Gateways**.
3. Select **BitDrive Standard Checkout** from the **Activate Module** dropdown, and click **Activate**.
4. Specify your **Merchant ID** on the **BitDrive Standard Checkout** configuration form.
5. If you have IPN enabled, specify your BitDrive **IPN Secret**.
6. Click the **Save Changes** button.

For documentation on BitDrive merchant services, go to https://www.bitdrive.io/help/merchant-services/6-introduction
