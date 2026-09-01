<?php

namespace App\Support;

use Illuminate\Support\Number;

/**
 * Reconciles the app's intended upload limits with what PHP will actually
 * accept.
 *
 * Validating against a larger limit than `upload_max_filesize` is worse than
 * useless: PHP rejects the file before Laravel sees it, and the user gets the
 * unhelpful "failed to upload" message instead of being told the real size cap.
 * Everything here derives the effective limit from php.ini at runtime, so the
 * rules and the on-screen guidance stay truthful if those settings change.
 */
class UploadLimits
{
    /**
     * The largest single file PHP will accept, in kilobytes.
     */
    public static function phpMaxKilobytes(): int
    {
        $upload = self::iniKilobytes('upload_max_filesize');
        $post = self::iniKilobytes('post_max_size');

        $limits = array_filter([$upload, $post]);

        return $limits === [] ? 2048 : (int) min($limits);
    }

    /**
     * The whole request body PHP will accept, in kilobytes. Exceeding this
     * wipes $_POST entirely, which is what turns a big upload into a confusing
     * "419 Page Expired" rather than a validation error.
     */
    public static function postMaxKilobytes(): int
    {
        return self::iniKilobytes('post_max_size') ?: 8192;
    }

    /**
     * The limit to actually validate against: what we want, capped by what PHP
     * can physically accept.
     */
    public static function forFile(int $preferredKilobytes): int
    {
        return (int) min($preferredKilobytes, self::phpMaxKilobytes());
    }

    /**
     * A human-readable version of a kilobyte limit, e.g. "2 MB".
     */
    public static function label(int $kilobytes): string
    {
        return Number::fileSize($kilobytes * 1024, precision: 0);
    }

    /**
     * Convenience: the effective limit for a preferred value, already formatted.
     */
    public static function labelFor(int $preferredKilobytes): string
    {
        return self::label(self::forFile($preferredKilobytes));
    }

    /**
     * Parse a php.ini shorthand byte value ("2M", "8192K", "1G") to kilobytes.
     * Returns null when the directive is unset or unlimited.
     */
    protected static function iniKilobytes(string $directive): ?int
    {
        $raw = trim((string) ini_get($directive));

        if ($raw === '' || $raw === '-1' || $raw === '0') {
            return null;
        }

        $unit = strtolower(substr($raw, -1));
        $value = (float) $raw;

        $bytes = match ($unit) {
            'g' => $value * 1024 ** 3,
            'm' => $value * 1024 ** 2,
            'k' => $value * 1024,
            default => $value,
        };

        return (int) floor($bytes / 1024);
    }
}
