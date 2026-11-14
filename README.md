
# WordPress External Link Gateway

A small, self-hosted interstitial page for external links. No WordPress plugin, database, tracking service, or admin panel required.

It rewrites eligible external links to `/go/<encoded-url>`, shows the destination on a separate page, and lets visitors choose whether to continue.

**This is not a security boundary or a way to hide the final destination.** Base64 is encoding, not encryption. The destination is visible on the interstitial page. Referrer and Fetch Metadata headers can be missing or spoofed. Use a real authorization mechanism for sensitive destinations.

## Requirements

- WordPress site on Apache with `mod_rewrite` and working `.htaccess` overrides
- PHP 8.1+
- Ability to upload `go.php` to the site root and add JavaScript to the site

## Setup

1. Edit `go.php`: replace `devarts.pro` and `www.devarts.pro` in `$allowedHosts` with your domains, and set `$siteName`.
2. Upload `go.php` to the web root, next to WordPress's `index.php`.
3. Copy the rules from `examples/htaccess-rules.txt` **above** the standard WordPress rewrite rules. Back up `.htaccess` first.
4. Add `assets/external-links.js` to your theme or snippet manager, ideally loaded after the content. Check the host exclusions and opt-out flags.
5. Open a page with an external link and verify that the new tab shows the destination page. Test internal links, excluded links, invalid inputs, and direct access.

To opt out a link, add `data-gateway-skip="true"` to the anchor. To opt out an entire section, put `data-gateway-skip="true"` on a parent element.

## How it works

```text
WordPress page → JavaScript rewrites an external anchor
               → /go/<base64url-encoded-destination>
               → Apache rewrite → go.php
               → visitor reviews the destination and clicks Continue
```

The JavaScript runs in the browser, so visitors without JavaScript retain the original external links. The PHP page does **not** perform an automatic HTTP redirect.

## Limitations and notes

- The same-origin header check is only a basic friction measure. It may reject legitimate visitors if browsers or privacy tools omit headers; it is not access control.
- Some sites have caching, CSP, CDN, Nginx, or nonstandard WordPress rewrite setups. The included `.htaccess` rules only apply to Apache.
- Rewriting links in the browser does not guarantee how crawlers or SEO tools interpret them. Do not rely on this to manipulate ranking signals.
- **Affiliate and paid links:** disclose relationships as required, and apply `rel="sponsored"` or `rel="nofollow"` to the *original* links as appropriate. The script preserves existing `rel` tokens.
- The page is marked `noindex, nofollow` and should not be used as an indexed landing page.
- A destination URL is user-supplied input. This project shows it to the visitor; do not change it into a server-side redirect without adding strict destination validation and abuse controls.
- Test changes on a staging site before production.

## Detailed article

A Russian-language walkthrough and background discussion: [How external link gateway pages work on WordPress](https://devarts.pro/4442-wordpress-js-hide-external-links.html).

## License

MIT. See [LICENSE](LICENSE).

