# Email and future gateway integrations

## Email delivery

The plugin uses WordPress `wp_mail`. Configure a reputable authenticated SMTP/API transport on your host, or a maintained SMTP plugin selected by the organization. No paid plugin is required or bundled. Enter credentials through host secrets or secure plugin settings, never source code or chat. Use a verified sending domain and configure the provider’s SPF/DKIM and the organization’s reviewed DMARC policy.

Set WordPress admin email and the optional notification-email setting. Volunteer/contact submissions are always stored first; staff notification contains only an admin link, not the private message. Each stored submission records mail handoff accepted/failed, so an unavailable mail transport does not lose the enquiry. Staff can view saved submissions independently of mail.

Confirmed-donation acknowledgments use the donor’s submission language. Mail handoff failure is recorded on the donation and staff can retry. Test with real controlled inboxes on staging: English/Urdu, approval, failure/retry and inbox/spam delivery. `wp_mail` returning true is not a delivery receipt. This build was tested with forced mail failure; real deliverability remains unverified because no production mail provider is supplied.

## Meezan transfer vs online payments

This implementation supports **manual bank transfer** with staff verification. It does not call Meezan APIs, invent merchant URLs, process card details or offer automated recurring donations. Making a bank transfer in an external bank app is separate from a real online merchant gateway.

For a future provider supply all of:

- Organization-approved merchant onboarding and account approval, supported PKR settlement and official legal entity details.
- The provider’s official, current API/SDK/checkout/webhook documentation and permitted use for charitable donations.
- Sandbox account and credentials, test scenarios, server-side signature/public-key verification specification, allowed network endpoints and retry rules.
- Production merchant identifier/secrets, webhook URL registration, origin/return URL configuration, refund/chargeback procedures and written launch approval.

No credentials or undocumented endpoint should be guessed. No gateway options are shown until a reviewed integration is installed and configured.

## Extension interface

`awsw_payment_gateways` is a registration filter returning an array. The core returns `[]` by default. It is an integration boundary, **not** a ready-made payment processor. A separate provider plugin can register capabilities such as key, label, official docs URL and configured/available state. It must implement its own server-side checkout and notification handlers under an agreed, reviewed schema. Do not expose keys to browser scripts.

`awsw_donation_status_changed($id, $old_status, $new_status)` fires after a committed verification change, useful for cache purging and audited operational notifications. It does not authenticate gateway events.

Before enabling a provider, implement verified signatures, amount/currency/reference matching, unique provider event/transaction keys and idempotent callbacks in a separate ledger or reviewed migration. Callbacks must handle duplicate, reordered and delayed events without double counting. Never use a browser return/redirect as evidence of payment. Authenticate provider refunds independently. Keep existing manual-verification records and audit history intact. Test successful, failed, cancelled, spoofed, altered-amount and duplicate notifications before launch. Automated recurring donations must stay unadvertised until successfully supported and tested by the real provider.
