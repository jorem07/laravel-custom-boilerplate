<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedEmailProviders implements ValidationRule
{
    protected array $alloweddomains = [
        "gmail.com", 
        "yahoo.com",
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        
        $domain = strtolower(substr(strrchr($value, "@"), 1));

        if (!in_array($domain, $this->alloweddomains)) {
            $fail('Only Valid email addresses are allowed.');
        }
    }
}
