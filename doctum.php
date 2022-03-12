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
    'build_dir'            => __DIR__ . '/build/%version%',
    'cache_dir'            => __DIR__ . '/cache/%version%',
    'default_opened_level' => 2,
    'remote_repository'    => new GitHubRemoteRepository('cjpgdk/bitcoin-blk-parser', dirname(__DIR__)),
    'base_url'             => 'http://localhost/api/%version%/',
]);
