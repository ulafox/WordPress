# Woo Pay Alipay Native (Plugin)

This plugin provides an Alipay (PC/H5/APP) gateway implementation and REST endpoints (notify/return) used by the Woo Pay core plugins.

Important:
- Do NOT commit real private keys or Alipay credentials to the repository.
- Use the plugin settings page (WooCommerce → Settings → Payments → Alipay or Plugins → Woo Pay Core settings) to paste private_key and Alipay public key.

Installation
1. Copy this folder to `wp-content/plugins/woo-pay-alipay-native`.
2. Activate `woo-pay-core` plugin first, then activate this plugin.
3. In WooCommerce payment settings enable "Alipay (Native)" and fill in App ID, Private Key (RSA2), Alipay Public Key and toggle sandbox mode as needed.

REST endpoints (examples)
- notify (server-to-server): POST https://your-domain.com/wp-json/woo-pay/alipay/notify
- return (sync redirect): GET https://your-domain.com/wp-json/woo-pay/alipay/return

Sandbox testing without credentials
- The repository includes sample scripts to simulate notify payloads. Use those scripts or the Woo Pay Core simulate endpoint documented in the repo README to emulate Alipay notify for testing.

Security
- Keep private key secret. Consider using server-side secret management or constants in wp-config.php instead of storing long-term in the database.

Notes
- This implementation provides a secure-signed form submission for the alipay.trade.page.pay scenario and a sample app-order string generator for app pay usage. After you fill in sandbox credentials in plugin settings, try a sandbox flow and monitor the REST notify endpoint logs for verification.
