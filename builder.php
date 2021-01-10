<?php

$branches = [
    'master',
    'stable24',
    'stable23',
    'stable22',
    'stable21',
];

$excludePackages = [
    'symfony/event-dispatcher-contracts',
    'symfony/service-contracts',
    'symfony/translation-contracts',
];

$branch = $argv[1] ?? null;

if ($branch === null || !in_array($branch, $branches, true)) {
    throw new \RuntimeException('Invalid branch "'.$branch.'"');
}

$contents = file_get_contents('https://raw.githubusercontent.com/nextcloud/3rdparty/'.$branch.'/composer.lock');

try {
    $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    throw new \RuntimeException($e->getMessage());
}

$packages = $data['packages'];
$replace = [];

foreach ($packages as $package) {
    $packageName = strtolower($package['name']);
    if (in_array($packageName, $excludePackages, true)) {
        continue;
    }
    $replace[$packageName] = $package['version'];
}

$composer = [];
$composer['name'] = 'kesselb/nextcloud-3rdparty';
$composer['type'] = 'metapackage';
$composer['license'] = 'MIT';
if (isset($data['platform']['php'])) {
    $composer['require'] = ['php' => $data['platform']['php']];
}
$composer['replace'] = $replace;
$composer['non-feature-branches'] = ['main', 'stable*'];


try {
    file_put_contents('composer.json', json_encode($composer, JSON_THROW_ON_ERROR  | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
} catch (JsonException $e) {
    throw new \RuntimeException($e->getMessage());
}


