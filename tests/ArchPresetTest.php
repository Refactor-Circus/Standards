<?php

declare(strict_types=1);

use RefactorCircus\Standards\Pest\ArchPreset;

it('keeps only namespaces under the given prefixes', function () {
    expect(ArchPreset::packageNamespaces(['Acme\\Shop', 'Workbench\\App', 'Acme\\Shop\\Database', 'Acme\\Shopping'], ['Acme\\Shop\\']))
        ->toBe(['Acme\\Shop', 'Acme\\Shop\\Database']);
});

it('reads the root package autoload namespaces, not autoload-dev', function () {
    expect(ArchPreset::autoloadNamespaces(dirname(__DIR__)))->toBe(['RefactorCircus\\Standards']);
});

it('reads nothing when there is no composer.json', function () {
    expect(ArchPreset::autoloadNamespaces(sys_get_temp_dir().'/no-such-package'))->toBe([]);
});
