<?php

/*
 * Reverse proxies allowed to set X-Forwarded-* headers (scheme, host, port and client IP).
 * Default: only a proxy on this machine. Behind Cloudflare or a host load balancer, list their
 * IP ranges in TRUSTED_PROXIES (comma separated). Never use "*" unless the origin accepts
 * traffic only from that proxy; otherwise clients could spoof their IP and scheme.
 */
$proxies = trim((string) env('TRUSTED_PROXIES', '127.0.0.1,::1'));

return [
    'proxies' => $proxies === '*' || $proxies === '**'
        ? $proxies
        : array_values(array_filter(array_map('trim', explode(',', $proxies)))),
];
