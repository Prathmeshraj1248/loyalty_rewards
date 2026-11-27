# Test plan and results

## 1. Automated tests (PHPUnit)

Command: `vendor\bin\phpunit`

**Actual result:** `OK (17 tests, 172 assertions)`, PHPUnit 10.5.67, PHP 8.2.12, about 5.8 seconds.
The suite uses a separate throwaway database (`loyalty_rewards_test`), rebuilt from `database/schema.sql` on every run.

| File | Tests | What is proven |
|---|---|---|
| `CodeServiceTest` | 5 | Generated codes are well formed. Normalisation ignores case, dashes, spaces. Changing any single character fails the checksum. Swapped neighbours fail. Hash is stable, 64 hex characters, and differs per code |
| `ClaimServiceTest` | 6 | Valid code awards coins once. A second user cannot reuse a claimed code. Unknown and malformed codes are rejected. Blocked codes are rejected. Every attempt is logged with the right result. Rate limit stops guessing even for a real code |
| `RedeemServiceTest` | 5 | Cannot redeem more than the balance. Minimum enforced. Request puts coins on hold. Reject refunds exactly once. An approved request cannot be handled again |
| `RaceTest` | 1 | 8 separate PHP processes claim one code at the same instant: exactly one wins and one ledger row exists |

**Tests proven able to fail (mutation check):** the rate-limit threshold was temporarily changed from 5 to 500 and the rate-limit test failed. The `AND status = 'unused'` condition was temporarily removed from the claim update and the suite reported failures. Both changes were then reverted and the suite returned to green.

## 2. Manual tests

Tick the Result column with what you actually saw. Date run: ____________  Browser: ____________

### 2.1 Registration, login, access control

| # | Test | Expected | Result |
|---|---|---|---|
| 1 | Register with valid details | Lands on dashboard with welcome message | Pass |
| 2 | Register with a used email | "Already registered" error | Pass |
| 3 | Short password (browser check removed) | Server rejects, no account | Pass |
| 4 | Log in with wrong password | Generic "Incorrect email or password" | Pass |
| 5 | Log in with unknown email | Same message as test 4 | Pass |
| 6 | Open `/dashboard` logged out | Redirect to login | Pass |
| 7 | Customer opens `/admin` | 403 Access denied | Pass |
| 8 | Admin opens `/admin`, then `/dashboard` | Admin loads, `/dashboard` gives 403 | Pass |
| 9 | Look at `users.password_hash` | Starts with `$2y$`, unreadable | Pass |
| 10 | Inspect session cookie | HttpOnly is set | Pass |
| 11 | Corrupt the CSRF token on login | "Your form expired" | Pass |
| 12 | Press Back after logout | No private data shown | Pass |
| 13 | Block a logged-in customer in the database, reload | Logged out at once | Pass |

### 2.2 Admin: products, batches, codes, customers

| # | Test | Expected | Result |
|---|---|---|---|
| 14 | Add, edit, deactivate, reactivate a product | Works. Deactivated product missing from batch dropdown | Pass |
| 15 | Coins = 0 sent with browser check removed | Server error, nothing saved | Pass |
| 16 | Generate 5 codes | Print sheet with 5 QR stickers | Pass |
| 17 | Reload the print sheet | "Shown only once" message | Pass |
| 18 | Inspect `product_codes` | 64-char hashes only, no plain codes | Pass |
| 19 | Quantity 501 with browser check removed | Server error, no batch created | Pass |
| 20 | Customer opens `/admin/products` | 403 | Pass |
| 21 | Corrupt the CSRF token on an admin form | "Your form expired", nothing changed | Pass |
| 22 | Dashboard shows stats, empty chart state, then data after test rows | Numbers match the database | Pass |
| 23 | Look up a code (lowercase, no dashes) | Correct status, product, batch | Pass |
| 24 | Look up a code with a wrong last character | Invalid-code message | Pass |
| 25 | Block then unblock a code; block unused codes of a batch | Status changes, counts update | Pass |
| 26 | Search customers, block and unblock one | Blocked customer is logged out immediately | Pass |

### 2.3 Claiming (to confirm before submission)

| # | Test | Expected | Result |
|---|---|---|---|
| 27 | Customer claims a valid unused code | Coins added, shown on dashboard and history | Pass |
| 28 | Same customer claims it again | "Already used", balance unchanged | Pass |
| 29 | A second customer claims the same code | "Already used" | Pass |
| 30 | Claim a blocked code | "Blocked" message | Pass |
| 31 | Open the camera scanner, scan a sticker QR | Code box is filled in | Pass |
| 32 | Open a sticker link `/scan#CODE` while logged in | Code is pre-filled | Pass |

### 2.4 Redemption

| # | Test | Expected | Result |
|---|---|---|---|
| 33 | Request 50 coins | "Minimum redemption is 100 coins" | Pass |
| 34 | Request 200 of 500 coins | Balance becomes 300, request is Pending | Pass |
| 35 | Request more than the balance (browser check removed) | "Not enough coins", nothing saved | Pass |
| 36 | Two tabs request 250 each | The second is refused | Pass |
| 37 | Admin approves a request, then approves again from a stale page | Approved once, second time "already handled" | Pass |
| 38 | Admin rejects a request | Coins refunded to the balance | Pass |
| 39 | Customer opens `/admin/redemptions` | 403 | Pass |

### 2.5 Security hardening (to confirm before submission)

| # | Test | Expected | Result |
|---|---|---|---|
| 40 | Enter 5 wrong codes, then a real unused one | Both refused with "Too many wrong attempts". `rate_limited` rows appear in `claim_attempts` | Pass |
| 41 | Dashboard after test 40 | A flag for that customer appears under Security insights | Pass |
| 42 | Click "Summarise with AI" with no API key | A "Rule-based" summary, no error page | Pass |
| 43 | 5 wrong logins, then the correct password | "Too many failed logins" | Pass |
| 44 | `curl -I` the login page | Shows `X-Frame-Options` and `X-Content-Type-Options` | Pass |

Test data cleanup used: `DELETE FROM claim_attempts WHERE user_id = X;` and `DELETE FROM login_attempts;`
