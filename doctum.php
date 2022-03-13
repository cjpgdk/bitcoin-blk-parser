<?php

use Doctum\Doctum;
use Doctum\RemoteRepository\GitHubRemoteRepository;
use Doctum\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;


$dir = __DIR__.'/src';
$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir);

$versions = GitVersionCollection::create($dir)
    ->addFromTags('*')
    ->add('master', 'Latest')
    ->add('develop', 'Develop');

return new Doctum($iterator, [
    'title'                => 'Bitcoin BLK block file parser/reader API',
    'versions'             => $versions,
    'language'             => 'en',
    'source_dir'           => dirname($dir) . '/',
    'build_dir'            => __DIR__ . '/../docs/bitcoin-blk-parser/%version%',
    'cache_dir'            => __DIR__ . '/../docs/cache/bitcoin-blk-parser/%version%',
    'default_opened_level' => 2,
    'remote_repository'    => new GitHubRemoteRepository('cjpgdk/bitcoin-blk-parser', __DIR__),
    'base_url'             => 'https://cjpgdk.github.io/bitcoin-blk-parser/%version%/',
]);
