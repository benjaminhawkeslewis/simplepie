#! /usr/bin/env php
<?php

/**
 * Generate a package.xml version 2 file.
 *
 * You will want to specify a channel with --channel (no official channel
 * exists at present.
 *
 * @author Benjamin Hawkes-Lewis
 */

require_once('PEAR/PackageFileManager2.php');

// Change to source root
chdir(__DIR__ . '/..');

PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packageFileManager = new PEAR_PackageFileManager2();

// Set channel and stability from options
// Default to pear.simplepie.org (NB this does not exist), snapshot,
// and base version 1.2.1.

$channel   = NULL;
$stability = NULL;
$version   = NULL;
$releaseVersion = NULL;

$shortOptions = array('c:', 's:', 'v:', 'r:');
$longOptions  = array('channel:', 'stability:', 'version:', 'release-version:');

foreach(getopt(join($shortOptions), $longOptions) as $option => $value)
{
    switch ($option)
    {
        case 'v:':
        case 'version:':
            $version = $value;
            break;
        case 'c':
        case 'channel':
            $channel = $value;
            break;
        case 's':
        case 'stability':
            $stability = $value;
            break;
        case 'r':
        case 'releaseVersion':
            $releaseVersion = $value;
            break;
    }
}

if (NULL === $channel)
{
    $channel = 'pear.simplepie.org';
    fwrite(STDERR, "Defaulting to channel $channel (specify --channel to override)." . PHP_EOL);
}

if (NULL === $stability)
{
    $stability = 'snapshot';
    fwrite(STDERR, "Defaulting to stability $stability (specify --stability to override)." . PHP_EOL);
}

if (NULL === $version)
{
    $version = '1.2.1';
    fwrite(STDERR, "Defaulting to version $version (specify --version to override)." . PHP_EOL);
}

// API stability field does not support "snapshot".
$apiStability = ($stability === 'snapshot') ? 'devel' : $stability;

// Calculate release version
if (NULL === $releaseVersion)
{
    if ('stable' !== $stability)
    {
        $releaseVersion = "$version$stability";
    }

    if ('snapshot' === $stability)
    {
        $releaseVersion .= date('YmdHis');
    }

    fwrite(STDERR, "Defaulting to release version $releaseVersion (specify --release-version to override)." . PHP_EOL);
}


$packageFileManager->setOptions(array(
    'baseinstalldir'    => '/',
    'simpleoutput'      => true,
    'packagedirectory'  => './',
    'filelistgenerator' => 'file',
    'ignore'            => array(
        'build/',
        'compatibility_test/',
        'db.sql',
        'demo/',
        'generatePackage.php',
        'LICENCE.txt',
        'README.markdown'
    ),
    'exceptions' => array(
        'README.markdown' => 'doc',
        'LICENCE.txt'     => 'doc'
    ),
    'dir_roles' => array(
        'tests'     => 'test',
        'SimplePie' => 'php'
    ),
));

$packageFileManager->setPackage('SimplePie');
$packageFileManager->setNotes('Simple Atom/RSS parsing library');
$packageFileManager->setSummary('Simple Atom/RSS parsing library');
$packageFileManager->setDescription('A simple Atom/RSS parsing library for PHP.');
$packageFileManager->setChannel($channel);
$packageFileManager->setAPIVersion($version);
$packageFileManager->setReleaseVersion($releaseVersion);

$packageFileManager->setReleaseStability('snapshot');

$packageFileManager->setAPIStability('devel');

$packageFileManager->setPackageType('php');

$packageFileManager->addRelease();

$packageFileManager->detectDependencies();

$packageFileManager->addMaintainer(
    'lead',
   'rmccue',
   'Ryan McCue',
   'r@rotorised.com'
);

$packageFileManager->setLicense('BSD');

$packageFileManager->setPhpDep('5.3.0');
$packageFileManager->setPearinstallerDep('1.4.0');

$packageFileManager->generateContents();

$packageFileManager->writePackageFile();
