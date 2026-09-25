# Live Implementation Adjustment Guide

This guide records the adjustments made in this project after the original baseline (`1dd33b3 INIT`). Use it when preparing another live implementation of this legacy CodeIgniter application. It is a change inventory and porting checklist, not permission to overwrite a live site wholesale.

## Agent handoff prompt

> Read `LIVE_IMPLEMENTATION_ADJUSTMENT_GUIDE.md` and compare each listed adjustment with this live implementation. Apply only changes that match its code, PHP version, database driver, configuration, and approved behavior. Preserve local configuration and data. Treat the password-hash change, PDF decommissioning, session/logout behavior, and database result handling as sensitive compatibility changes: verify their current behavior and dependencies before porting. Keep AJAX responses free of PHP notices/warnings. Review the diff and report what was applied, skipped, and how each affected flow was verified.

## Porting rules

1. Compare the live implementation to the original application structure and to this repository's current code before editing. This project is an old CodeIgniter-style application with custom system and PDO driver code; do not assume a modern CodeIgniter upgrade.
2. Preserve live-only configuration, credentials, institution branding, routes, database schemas, stored procedures, and user data. Do not copy local configuration files or generated cache contents from this checkout.
3. Make focused changes in the matching controller, model, view, shared library, or driver. Keep endpoint response shapes and stored procedure contracts intact unless the live implementation is proven to use the same contract.
4. Treat warning-free JSON/AJAX output as a functional requirement. A PHP notice before JSON breaks clients even when the operation itself succeeded.
5. Confirm PHP version and extensions used on the target host. The recorded compatibility target is PHP 8.5; changes that remove old PHP support should only be carried over when the target allows it.

## Change inventory

### PHP 8.5 compatibility

Commits: `988d3b9`, `a334b38`

- Updated the bundled legacy framework and application code for PHP 8 behavior: optional parameters now have valid defaults/order; removed `each()` use is replaced or supported compatibly; removed `/e` regular-expression replacements use callbacks; reference-to-function-call patterns and removed constants were addressed; string values passed to HTML escaping are normalized; expected legacy dynamic properties are allowed on framework classes.
- Updated PHP 8-incompatible library and helper behavior, including login/session access and request-server values that may be absent.
- Initialized legacy model/controller state explicitly where PHP 8 no longer supplies the old behavior.
- Replaced the old TCPDF-backed PDF library with a clear retired-feature response in this application.

**For the live site:** scan the entire deployed application and bundled framework, not just application controllers. Do not decommission PDF generation unless that feature is intentionally retired there. If PDF is still required, port or replace the PDF library with a PHP 8.5-compatible supported version and verify its fonts, cache paths, and output.

### Authentication and credential compatibility

Commits: `6158ff0`, `3c87d25`, `9a64ee3`

- Normalized login result codes returned from PDO before making success/failure decisions.
- Changed the application password digest from `sha1(userID:password:coreware)` to `sha256(userID:password:coreware)`.
- Made password-reset handling tolerate incomplete login/session context.

**Important:** the SHA-256 change changes the stored digest format. Do not copy it to a live site without confirming how existing hashes are stored, how users authenticate, and whether a migration or dual-verification path is needed. Test successful login, failed login, password change/reset, and existing accounts using the live database.

### PDO, stored procedures, and result lifecycle

Commits: `b8f5ec7`, `8f5c326`, `91e4cf0`, `4723410`, `4533395`

- Drain every PDO rowset returned by MySQL stored procedures and close the cursor before issuing another query on the same connection. `closeCursor()` alone was not reliable for these procedures.
- Release query results explicitly in the settings save flow and in the shared driver path.
- Preserve PDO browse result rows and use a valid row-count query for the transaction monitor.
- Fixed the General Settings save path where unconsumed PDO results prevented subsequent queries.

**For the live site:** reproduce against its actual MySQL/MariaDB/PDO versions and stored procedures. Check every result set expected by callers before changing shared result cleanup; callers may depend on additional result sets. Exercise consecutive procedure calls on one connection, settings save, browse/pagination, and transaction monitoring.

### Session expiration, logout, and missing context

Commits: `f165f01`, `9a64ee3`, `f93ead7`

- Made logout safe when the session has expired or the user was never fully authenticated. User/audit/cache cleanup that requires an authenticated identity only runs when that identity is present; branding is retained with safe defaults so the login page can still render.
- Cleared session state and handled headers before rendering the login page.
- Centralized guarded reads for optional user and institution session values in `application/libraries/core.php`; boolean capability checks default safely when context is missing.
- Avoided treating a missing user context as authenticated.

**For the live site:** review the target's session library and audit requirements. Verify normal logout, auto-logout after expiry, direct logout with no session, login-page branding, and that missing identity does not trigger audit actions with fabricated user IDs or delete unrelated temporary files.

### Override and state-changing response flows

Commits: `69b4e31`, `c2590a6`, `93ba173`, `cad49b8`, `11f8c4f`, `7bb7877`, `dce0e9b`, `e7ed177`

- Renamed the `Override` controller to `Useroverride` to avoid collision with the PHP built-in `Override` attribute name; retained legacy URLs through explicit routes and updated modal/form actions.
- Preserved the pending account update form action through the override interaction and continued the approved update after a valid override response.
- Normalized stored-procedure `errno` checks to accept integer or string zero while checking that the returned field exists.
- Normalized card verification and card issuance response states so browser clients receive the expected success/failure values.
- Applied the same stored-procedure success check across the affected account, card, and terminal/POS maintenance flows.

**For the live site:** map the full request sequence (initial update, override validation, approval, final update) and preserve authorization and audit behavior. Verify both valid and invalid overrides, canceled overrides, duplicate submissions, and failures after approval. Confirm clients receive JSON with the keys and types they expect, and that an approval response is not mistaken for completion of the underlying update.

### JSON/AJAX, list browsing, and PHP 8 notices

Commits: `a8b8ec5`, `4d118ea`, `6287913`, `be99c28`, `635ce18`, `26f424e`, `cb43c86`, `98dabf8`

- Fixed user-list view data and browse counts.
- Corrected malformed/escaped toolbar scripts in browse and list views. Nested HTML embedded in JavaScript responses must remain valid JavaScript and JSON; server notices must not be emitted into the response body.
- Replaced PHP 8 reference notices in the service-code, allows-setup, terminal, and POS monitoring endpoints with local variables before reference-sensitive operations.
- Supplied missing branch names for service-charge data.

**For the live site:** inspect browser Network response bodies and console errors for affected endpoints. Check that empty, populated, and paginated lists render correctly; filters/search/counts agree with returned rows; and successful and failed mutations return valid JSON with no PHP warning markup preceding it.

### Customer and card interface corrections

Commits: `a465eaa`, `f67bfdb`, `02408bc`, `ac57fa5`, `c0d3f0b`, `61ce726`

- Reworked customer forms/details so the photo sits beside the personal information, identity fields use a two-column layout, and contact numbers sit beside the address. Added responsive stacking for narrow screens.
- Kept card-list status, card-type, and search controls on one toolbar row.
- Kept area and department list footer controls visible after navigating back from create/edit views.
- Applied maintenance wrapper sizing based on each view's intrinsic content, capped to the viewport with horizontal overflow available. The earlier blanket 500px minimum was replaced because it widened narrow views unnecessarily.

**For the live site:** compare the actual live templates and shared styles before porting. Keep layout rules scoped to the relevant module; verify the smallest supported viewport and the widest table/form in each affected view. Do not impose a shared fixed minimum width across unrelated maintenance screens.

### Cache repository hygiene

Commit: `b5f0728`

- Ignored generated files under `application/cache/` while retaining `application/cache/.gitkeep` so the directory exists in a clean checkout.

**For the live site:** keep the runtime cache directory writable by the web process, but do not commit generated session/cache files or live cache contents. Preserve any host-specific deployment rules for cache cleanup.

## Deployment verification checklist

Before releasing changes to a live implementation:

- Record PHP version, PDO driver/client version, extensions, database server version, and relevant framework/application revision.
- Back up code and database; stage and review the exact diff. Never overwrite live configuration or data with this repository's local values.
- Check PHP syntax on changed PHP files and inspect server logs. Search the affected AJAX responses for warning/notice HTML before JSON.
- Verify login, failed login, password reset/change, standard logout, expired-session auto-logout, and access control when session data is absent.
- Verify representative stored procedures both alone and consecutively on the same PDO connection; confirm all expected result sets and row counts.
- Verify account override, card verification/issuance, terminal/POS edits, and maintenance saves with success and failure cases.
- Verify customer layouts, card list filters, area/department list actions, and maintenance forms at expected screen sizes.
- Confirm cache directory permissions and that no generated cache/session files entered the release.
- Report each guide item as **applied**, **not applicable**, or **blocked pending environment/behavior confirmation**. Include the changed files and verification evidence.

## History reference

The adjustments above are represented by commits from `988d3b9` through `61ce726`, after the original baseline commit `1dd33b3 INIT`. Refer to `git log --oneline 1dd33b3..HEAD` and individual commit diffs when exact implementation details are needed. Commit hashes are references for this repository; a separate live copy may not share its Git history.
