<?php
/**
 * Identity Formatting Utilities
 * Normalizes resident names and usernames into clean, government-standard
 * formatting before database insertion or update.
 *
 * Usage:
 *   require_once "path/to/includes/format.php";
 *   $full_name = formatName($full_name);   // Title Case (Unicode-safe)
 *   $username  = formatUsername($username); // lowercase
 */

/**
 * Convert a name to proper title case.
 * Uses Unicode-aware conversion so Filipino characters (ñ, é, etc.)
 * are handled correctly. Strips leading/trailing whitespace first.
 *
 * Examples:
 *   "juan dela cruz"    → "Juan Dela Cruz"
 *   "MARK TRIXIAN DELEÑA" → "Mark Trixian Deleña"
 *   "mArIa sAntOs"      → "Maria Santos"
 *
 * @param  string $name  Raw user input
 * @return string        Title-cased name
 */
function formatName(string $name): string
{
    return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Normalize a username to lowercase for consistent storage and login.
 *
 * Example:
 *   "Mark.TD"  → "mark.td"
 *
 * @param  string $username  Raw user input
 * @return string            Lowercase username
 */
function formatUsername(string $username): string
{
    return strtolower(trim($username));
}
?>
