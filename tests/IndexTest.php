<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

function standardsIndex(): array
{
    return Yaml::parseFile(dirname(__DIR__).'/index.yml');
}

function standardFiles(): array
{
    $root = dirname(__DIR__).'/standards/';
    $files = [];

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() === 'md') {
            $files[] = substr($file->getPathname(), strlen($root), -3);
        }
    }

    sort($files);

    return $files;
}

it('indexes every standard file and nothing else', function () {
    $keys = array_keys(standardsIndex());
    sort($keys);

    expect($keys)->toBe(standardFiles());
});

it('gives every standard a description, tags and enforced_by', function () {
    foreach (standardsIndex() as $key => $entry) {
        expect($entry)->toHaveKeys(['description', 'tags', 'enforced_by'])
            ->and($entry['description'])->toBeString()->not->toBeEmpty()
            ->and($entry['tags'])->toBeArray()
            ->and($entry['enforced_by'])->toBeArray();
    }
});

it('keeps every description within the skill description budget', function () {
    foreach (standardsIndex() as $entry) {
        expect(strlen($entry['description']))->toBeLessThanOrEqual(400);
    }
});
