<?php

declare(strict_types=1);

use RefactorCircus\Standards\Pest\ArchPreset;

it('keeps only suite namespaces', function () {
    expect(ArchPreset::packageNamespaces(['RefactorCircus\\Keystone', 'Workbench\\App', 'RefactorCircus\\Keystone\\Database']))
        ->toBe(['RefactorCircus\\Keystone', 'RefactorCircus\\Keystone\\Database']);
});
