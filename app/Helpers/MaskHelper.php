<?php

namespace App\Helpers;

class MaskHelper
{
    /**
     * Mask a string by showing only first and last few characters
     *
     * @param string|null $value The string to mask
     * @param int $showFirst Number of characters to show at beginning
     * @param int $showLast Number of characters to show at end
     * @param string $maskChar Character to use for masking
     * @return string
     */
    public static function maskString(?string $value, int $showFirst = 2, int $showLast = 2, string $maskChar = '*'): string
    {
        if (empty($value)) {
            return '';
        }

        $length = strlen($value);

        // If string is too short, just mask all except first character
        if ($length <= $showFirst + $showLast) {
            return substr($value, 0, 1) . str_repeat($maskChar, $length - 1);
        }

        $firstPart = substr($value, 0, $showFirst);
        $lastPart = substr($value, -$showLast);
        $maskLength = $length - ($showFirst + $showLast);

        return $firstPart . str_repeat($maskChar, $maskLength) . $lastPart;
    }

    /**
     * Mask an email address
     *
     * @param string|null $email
     * @return string
     */
    public static function maskEmail(?string $email): string
    {
        if (empty($email)) {
            return '';
        }

        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return self::maskString($email);
        }

        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = self::maskString($username, 1, 1);
        $domainParts = explode('.', $domain);
        $tld = array_pop($domainParts);
        $domainName = implode('.', $domainParts);
        $maskedDomain = self::maskString($domainName, 1, 1) . '.' . $tld;

        return $maskedUsername . '@' . $maskedDomain;
    }

    /**
     * Mask a phone number
     *
     * @param string|null $phone
     * @return string
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Keep only digits for consistent masking
        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($digitsOnly) <= 4) {
            return str_repeat('*', strlen($digitsOnly));
        }

        return self::maskString($digitsOnly, 2, 2);
    }
}
