<?php

namespace App\Support;

use Illuminate\Http\Request;

class DomainVerificationHelper
{
    /**
     * Determine if the incoming request matches an authorized domain for the site environment.
     */
    public function isAuthorized(Request $request, array $domains): bool
    {
        // Allow loopback requests (local testing & development)
        if ($this->isLoopback($request) || app()->isLocal()) {
            return true;
        }

        $allowedDomains = [];

        // Collect all authorized domains from environment and site
        foreach ($domains as $domain) {
            if ($cleanDomain = $this->normalize($domain)) {
                $allowedDomains[] = $cleanDomain;
            }
        }

        if (empty($allowedDomains)) {
            return true;
        }

        // Collect candidate incoming hosts from request headers
        $requestHosts = [];

        if ($normalizedHost = $this->normalize($request->getHost())) {
            $requestHosts[] = $normalizedHost;
        }

        if ($normalizedHttpHost = $this->normalize($request->getHttpHost())) {
            $requestHosts[] = $normalizedHttpHost;
        }

        if ($forwardedHost = $request->header('X-Forwarded-Host')) {
            if ($normalizedForwarded = $this->normalize($forwardedHost)) {
                $requestHosts[] = $normalizedForwarded;
            }
        }

        if ($origin = $request->header('Origin')) {
            if ($normalizedOrigin = $this->normalize($origin)) {
                $requestHosts[] = $normalizedOrigin;
            }
        }

        if ($referer = $request->header('Referer')) {
            if ($normalizedReferer = $this->normalize($referer)) {
                $requestHosts[] = $normalizedReferer;
            }
        }

        if ($siteDomainHeader = $request->header('X-Site-Domain')) {
            if ($normalizedSiteDomain = $this->normalize($siteDomainHeader)) {
                $requestHosts[] = $normalizedSiteDomain;
            }
        }

        $requestHosts = array_unique(array_filter($requestHosts));

        foreach ($requestHosts as $candidate) {
            if (in_array($candidate, $allowedDomains, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize a host or URL string to a clean domain without port, protocol, or path.
     */
    public function normalize(?string $domain): ?string
    {
        if (! $domain) {
            return null;
        }

        $domain = trim($domain);

        if (! str_contains($domain, '://')) {
            $domain = 'http://'.$domain;
        }

        $host = parse_url($domain, PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        $host = explode(':', $host)[0];

        return strtolower(trim($host, " /\t\n\r\0\x0B"));
    }

    /**
     * Check if the incoming request is from a local loopback interface.
     */
    public function isLoopback(Request $request): bool
    {
        $host = strtolower(explode(':', $request->getHttpHost())[0]);
        $clientHost = strtolower($request->getHost());

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || in_array($clientHost, ['localhost', '127.0.0.1', '::1'], true);
    }
}
