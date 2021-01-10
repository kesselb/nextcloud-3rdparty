<?php

$branchToVersion = [
    'master'   => 'dev-master',
    'stable20' => '20',
    'stable19' => '19',
    'stable18' => '18',
];

$branch = $argv[1] ?? null;

if ($branch === null || ! isset($branchToVersion[$branch])) {
    throw new \RuntimeException('Invalid branch "' . $branch . '"');
}

$contents = file_get_contents('https://raw.githubusercontent.com/nextcloud/3rdparty/' . $branch . '/composer.lock');

try {
    $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    throw new \RuntimeException($e->getMessage());
}

$packages = $data['packages'];
$replace  = [];

foreach ($packages as $package) {
    $replace[$package['name']] = $package['version'];
}

$composer         = [];
$composer['name'] = 'kesselb/nextcloud-3rdparty';
$composer['type'] = 'metapackage';
if (isset($data['platform']['php'])) {
    $composer['require'] = ['php' => $data['platform']['php']];
}
$composer['replace'] = $replace;

try {
    file_put_contents('composer.json', json_encode($composer, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
} catch (JsonException $e) {
    throw new \RuntimeException($e->getMessage());
}


