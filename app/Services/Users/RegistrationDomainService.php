<?php

namespace Pterodactyl\Services\Users;

class RegistrationDomainService
{
    public function isAllowed(string $email, array $allowedDomains): bool
    {
        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

        foreach ($allowedDomains as $allowedDomain) {
            $allowedDomain = strtolower(trim((string) $allowedDomain));

            if ($allowedDomain === '') {
                continue;
            }

            if ($domain === $allowedDomain || str_ends_with($domain, '.' . $allowedDomain)) {
                return true;
            }
        }

        return false;
    }
}
