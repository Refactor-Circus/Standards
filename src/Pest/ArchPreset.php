<?php

declare(strict_types=1);

namespace RefactorCircus\Standards\Pest;

/**
 * Registers the suite-wide `refactorCircus` arch preset. Call it from a
 * package's tests/Pest.php, never from an autoloaded file: Composer may load
 * a `files` entry before Pest's own functions exist.
 *
 *     \RefactorCircus\Standards\Pest\ArchPreset::register();
 *     arch()->preset()->refactorCircus();
 */
final class ArchPreset
{
    public const string NAME = 'refactorCircus';

    public static function register(): void
    {
        pest()->presets()->custom(self::NAME, static fn (array $namespaces): array => self::expectations(
            self::packageNamespaces($namespaces),
        ));
    }

    /**
     * Pest hands a preset every non-test PSR-4 namespace, workbench included;
     * the suite rules apply to the package's own code only.
     *
     * @param  array<int, string>  $namespaces
     * @return array<int, string>
     */
    public static function packageNamespaces(array $namespaces): array
    {
        return array_values(array_filter(
            $namespaces,
            static fn (string $namespace): bool => str_starts_with($namespace, 'RefactorCircus\\'),
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
