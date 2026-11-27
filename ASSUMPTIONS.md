# Assumptions

The assignment leaves many details open. Each gap, the choice made, and the reason.

| # | Gap | Choice | Why |
|---|---|---|---|
| 1 | Where is the QR placed on the box? | **Inside** the box (under a flap or seal). | A QR on the outside could be scanned in a shop without buying. Inside placement is the proof of purchase. |
| 2 | Code format | 12 random characters plus 1 checksum character, shown as `XXXX-XXXX-XXXX-X`. Alphabet of 31 characters with no `0 O 1 I L`. | About 59 bits of randomness, so guessing is impractical. The checksum catches typos and swapped characters before touching the database. The alphabet avoids misreading when typing. |
| 3 | How are codes stored? | Only an HMAC-SHA256 hash (keyed with `CODE_HASH_SECRET`). | A stolen database gives the attacker no usable codes. |
| 4 | When can the owner see a code? | Only once, on the print sheet right after generating. A lost sheet means that batch's unused codes are blocked. | Follows from assumption 3. |
| 5 | QR content | `APP_URL/scan#CODE`. | Text after `#` is not sent to the server, so codes stay out of server logs. |
| 6 | Coin value | 1 coin = ₹1, stored in the `settings` table. | Easy to explain and demo. Changed in the database (no settings screen). |
| 7 | Coins per product | Set per product by the admin. Changing it affects only future claims. | Flexible, and past claims are never rewritten. |
| 8 | Redemption | Customer requests, owner approves or rejects. Payout is simulated (no real payment gateway). | A real payment gateway is out of scope. |
| 9 | Minimum redemption | 100 coins. | Avoids tiny requests. Stored in `settings`. |
| 10 | When are coins taken for a redemption? | At request time (a negative "hold" row in the ledger). Rejection adds a refund row. | Stops spending the same coins twice. |
| 11 | Customer login | Email and password. No email verification, no password reset. | Keeps scope manageable. Listed as a limitation. |
| 12 | Admin accounts | One owner admin, created by a command-line script. No public admin signup. | Prevents anyone from registering as admin. |
| 13 | Code expiry | Codes do not expire. The owner can block a code or a batch. | Simpler. Blocking covers theft or loss. |
| 14 | Blocking a claimed code | Not allowed. | The customer already received the coins. |
| 15 | Batch size | Maximum 500 codes per batch. | Keeps generation and the page fast. Make several batches for more. |
| 16 | Claim rate limit | 5 failed attempts per user, or 20 per IP, in 15 minutes. | Stops code guessing. Typos count as failures. |
| 17 | Login throttle | 5 failed logins per email in 15 minutes. | Stops password guessing. Trade-off: someone could lock a victim out for 15 minutes. |
| 18 | Session length | 30 minutes of inactivity. | Limits the damage of an unattended device. |
| 19 | Registration messages | Registration says if an email is already used. | Usability over hiding that an email exists. Login uses one generic message. |
| 20 | Scanning flow | Customers log in first. A sticker link opens the claim page with the code pre-filled. The customer presses Claim. | Auto-claiming from a link could be abused and the server must stay the only decision maker. |
| 21 | Client IP | `REMOTE_ADDR` only. | Headers like `X-Forwarded-For` can be faked. Behind a proxy this must be configured. |
| 22 | AI | Optional. It only summarises anonymous counts of suspicious signals and never approves codes, awards coins, or changes data. Falls back to plain rule-based text when no key or when the call fails. | AI is useful for summaries, not for security decisions. |
| 23 | Currency and language | Indian rupees, English interface. | Matches the client. |
| 24 | Front-end tooling | Tailwind and Chart.js through CDN, no build step. | No Node.js needed. A production site would use a build step. |
| 25 | Time | Time windows use the database server clock. | PHP and MySQL time zones can never disagree. |
| 26 | Hosting | A live deployment is optional. Local XAMPP is the primary setup. | The assignment says "if available". |
