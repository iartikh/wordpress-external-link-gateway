
/**
 * External Link Gateway
 * Rewrites external HTTP(S) links to /go/<encoded-url>.
 */
(() => {
  'use strict';

  const excludedHosts = new Set([
    // 'example.com',
  ]);

  const anchors = document.querySelectorAll('a[href]');

  for (const link of anchors) {
    if (link.dataset.gatewaySkip === 'true') continue;
    if (link.closest('[data-gateway-skip="true"]')) continue;

    let url;

    try {
      url = new URL(link.href, window.location.href);
    } catch {
      continue;
    }

    if (!['http:', 'https:'].includes(url.protocol)) continue;
    if (url.origin === window.location.origin) continue;
    if (excludedHosts.has(url.hostname.toLowerCase())) continue;
    if (link.relList.contains('external-gateway-skip')) continue;

    // Encode UTF-8 URLs as URL-safe Base64.
    const bytes = new TextEncoder().encode(url.href);
    let binary = '';

    for (const byte of bytes) {
      binary += String.fromCharCode(byte);
    }

    const encoded = btoa(binary)
      .replace(/\+/g, '-')
      .replace(/\//g, '_')
      .replace(/=+$/g, '');

    link.href = '/go/' + encoded;
    link.target = '_blank';

    link.rel = [
      ...new Set([
        ...link.rel.split(/\s+/).filter(Boolean),
        'noopener'
      ])
    ].join(' ');
  }
})();
