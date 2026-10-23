<?php

declare(strict_types=1);

namespace RefactorCircus\Standards\Pest;

/**
 * Registers the shared `refactorCircus` arch preset. Call it from a package's
 * tests/Pest.php, never from an autoloaded file: Composer may load a `files`
 * entry before Pest's own functions exist.
 *
 *     \RefactorCircus\Standards\Pest\ArchPreset::register();
 *     arch()->preset()->refactorCircus();
 *
 * By default the rules apply to the root package's own `autoload.psr-4`
 * namespaces (not autoload-dev, not workbench). Pass prefixes to choose them.
 */
final class ArchPreset
{
    public const string NAME = 'refactorCircus';

    /**
     * @param  list<string>|null  $prefixes  namespace prefixes the rules apply to; null = the root package's autoload
     */
    public static function register(?array $prefixes = null, ?string $root = null): void
    {
        $prefixes ??= self::autoloadNamespaces($root ?? (string) getcwd());

        pest()->presets()->custom(self::NAME, static fn (array $namespaces): array => self::expectations(
            self::packageNamespaces($namespaces, $prefixes),
        ));
    }

    /**
     * Pest hands a preset every non-test PSR-4 namespace, workbench included;
     * the shared rules apply to the package's own code only.
     *
     * @param  array<int, string>  $namespaces
     * @param  list<string>  $prefixes
     * @return array<int, string>
     */
    public static function packageNamespaces(array $namespaces, array $prefixes): array
    {
        $prefixes = array_map(static fn (string $prefix): string => rtrim($prefix, '\\'), $prefixes);

        return array_values(array_filter(
            $namespaces,
            static function (string $namespace) use ($prefixes): bool {
                foreach ($prefixes as $prefix) {
                    if ($namespace === $prefix || str_starts_with($namespace, $prefix.'\\')) {
                        return true;
                    }
                }

                return false;
            },
        ));
    }

    /**
     * The root package's `autoload.psr-4` namespaces, without trailing backslashes.
     *
     * @return list<string>
     */
    public static function autoloadNamespaces(string $root): array
    {
        $manifest = is_file($root.'/composer.json') ? json_decode((string) file_get_contents($root.'/composer.json'), true) : null;
        $psr4 = is_array($manifest) ? ($manifest['autoload']['psr-4'] ?? []) : [];

        return array_values(array_map(
            static fn (int|string $namespace): string => rtrim((string) $namespace, '\\'),
            array_keys(is_array($psr4) ? $psr4 : []),
        ));
    }

    /**
     * @param  array<int, string>  $namespaces
     * @return array<int, mixed>
     */
    private static function expectations(array $namespaces): array
    {
        if ($namespaces === []) {
            return [];
        }

        return [
            expect($namespaces)->toUseStrictTypes(),
            expect('env')->not->toBeUsedIn($namespaces),
        ];
    }
}
