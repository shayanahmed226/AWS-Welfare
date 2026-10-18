# Volunteer, assistance, partnership and contact operations

The four kinds share persistent database storage with separate kinds, statuses, signed per-form submission keys and a unique database index. Replaying the same form cannot duplicate a request. All successful responses follow a database insert or an existing matching submission; no simulated success. Public fields use nonces, validation, sanitization, consent, honeypot and rate limits.

## Forms and review

- Volunteer: name, email, phone, city, preferred program, skills, days/hours, on-site/remote/either, motivation, consent. This is general interest unless real opportunities are published. No CNIC/identity scans.
- Assistance: name, contact method, general area, assistance type, short description and consent. No uploads, medical records or identity scans. Submission does not guarantee help and is not an emergency service.
- Partnership: organization, name/email, optional phone, sponsorship/in-kind/employee-volunteering interest, brief message and consent. No invented partner endorsements.
- Contact: name/email, optional phone, inquiry category, message and consent.

Default status is `new`; authorized reviewers can record `reviewing`, `accepted`, `declined` with mandatory staff notes. These outcomes are review states, not promises of delivered support. Staff identity, time and old/new states are recorded separately from financial audits. Do not enter unnecessary sensitive details in notes.

## Least privilege

- Administrator: all operations.
- Donation Manager: existing financial review/export; no assistance/general applications/settings.
- Application Manager: volunteer, partnership and contact requests only; no assistance, donations or bank settings.
- Assistance Manager: private assistance requests only; no ordinary donor/payment settings or public content privileges.
- Content Editor: public editorial content, no private requests/donations.

New roles are created on plugin activation/migration. Administrators assign individual accounts. “Applications” and “Assistance requests” have separate top-level admin menus, filters, pagination, empty states and review history. Early capability checks reject attempts to access assistance via ordinary application URLs. Each destructive deletion requires a nonce and explicit confirmation under the approved retention policy. A verified WordPress privacy request can erase matched records/review notes; phone-only requests need an organization-approved verification process rather than email matching.

## Errors, duplicates and notifications

Enhanced forms validate visibly, offer an accessible error summary, show real upload/request progress and preserve entries in the current page after server/network failures. No form personal data is stored in localStorage/sessionStorage. An expired nonce is refreshed in the error response so retry need not clear entered details. Language switching warns before resetting an unsent form. Without JavaScript all fields and normal browser/server validation remain available; the browser Back action returns to the previous form after a server validation error. Never promise browser-restored file selection; select the proof again if your browser clears it.

Staff mail notifications contain only an admin link; saved requests remain available if mail fails. Accepted mail means transport handoff, not inbox delivery. Admin records show notification status. Reconcile the stored inbox regularly rather than relying solely on email. Configure the sending provider before launch.
