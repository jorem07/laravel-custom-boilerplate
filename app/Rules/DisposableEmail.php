<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\SecondLevelDomain;

class DisposableEmail implements ValidationRule
{
    protected array $allowedTlds = ['com', 'net', 'org', 'gov', 'edu'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedDomainsQuery = SecondLevelDomain::pluck('name');

        if ($allowedDomainsQuery->isEmpty()) {  
            $fail('Domain validation failed. No Data');
            return;
        }

        $allowedDomains = $allowedDomainsQuery
            ->map(fn($d) => strtolower($d))
            ->toArray();

        // Extract domain from email
        $fullDomain = strtolower(substr(strrchr($value, "@"), 1));

        $parts = explode('.', $fullDomain);
        if (count($parts) < 2) {
            $fail('Invalid email domain.');
            return;
        }

        $tld = $parts[count($parts) - 1];
        $secondLevelDomain = $parts[count($parts) - 2];

        if (!in_array($secondLevelDomain, $allowedDomains)) {
            $fail('Invalid email domain.');
            return;
        }

        if (!in_array($tld, $this->allowedTlds)) {
            $fail('Invalid TLD.');
        }
    }
}
