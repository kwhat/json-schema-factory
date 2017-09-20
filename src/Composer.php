<?php

namespace JsonSchema;

use Composer\Installer;
use Composer\Package;
use Composer\Repository;
use Composer\Script;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Composer
{
    public static function generateSchema(Script\Event $event)
    {
        /** @var \Composer\Composer $composer */
        $composer = $event->getComposer();

        /** @var Installer\InstallationManager $installationManager */
        $installationManager = $composer->getInstallationManager();

        /** @var \Composer\Autoload\AutoloadGenerator $autoloadGenerator */
        $autoloadGenerator = $composer->getAutoloadGenerator();
        $autoloadGenerator->setDevMode(false);

        /** @var Package\PackageInterface $mainPackage */
        $mainPackage = $composer->getPackage();

        $autoloadMap = $mainPackage->getAutoload();

        /** @var string $installDir */
//        $installDir = realpath($installationManager->getInstallPath($mainPackage));

        /** @var Repository\InstalledRepositoryInterface $repoManager */
//        $repoManager = $composer->getRepositoryManager()->getLocalRepository();

        /** @var Package\CompletePackageInterface[] $packageMap */
        //$packageMap = $autoloadGenerator->buildPackageMap($installationManager, $mainPackage, $repoManager->getCanonicalPackages());
//        $packageMap = $autoloadGenerator->buildPackageMap($installationManager, $mainPackage, array());
        /** @var array $autoloadMap */
//        $autoloadMap = $autoloadGenerator->parseAutoloads($packageMap, $mainPackage);
        //var_dump($autoloadMap);
        ///die($installDir);



        /** @var Installer\InstallationManager $installationManager */
//        $installationManager = $composer->getInstallationManager();

        /** @var Package\PackageInterface $mainPackage */
//        $mainPackage = $composer->getPackage();

        /** @var string $installDir */
//        $installDir = realpath($installationManager->getInstallPath($mainPackage));

//        $autoloadMap = $mainPackage->getAutoload();


        foreach ($autoloadMap as $std => $lookup) {
            switch ($std) {
                case "psr-0":
                case "psr-4":
                    foreach ($lookup as $namespace => $paths) {
                        if (substr($namespace, -1) != "\\") {
                            $namespace .= "\\";
                            // TODO Maybe Throw Warning?
                        }

                        if (! is_array($paths)) {
                            $paths = array($paths);
                        }

                        foreach ($paths as $path) {
                            if (substr($path, 0, -1) != DIRECTORY_SEPARATOR) {
                                $path .= DIRECTORY_SEPARATOR;
                            }

                            if (is_dir($path)) {
                                $directory = new RecursiveDirectoryIterator($path);
                                $iterator = new RecursiveIteratorIterator($directory);
                                $regex = new RegexIterator($iterator, '/^' . preg_quote($path, DIRECTORY_SEPARATOR) . '(.+)\.php$/i', RegexIterator::REPLACE);
                                $regex->replacement = '$1';

                                foreach ($regex as $file => $class) {
                                    $pathInfo = pathinfo($class);
                                    $class = "{$namespace}" . str_replace(DIRECTORY_SEPARATOR, "\\", $class);

                                    if (is_subclass_of($class, AbstractSchema::class, true)) {
                                        $args = $event->getArguments();
                                        $prefix = "schema" . DIRECTORY_SEPARATOR;

                                        if (count($args) > 0) {
                                            $prefix = $args[0];
                                            if (substr($prefix, -1) != DIRECTORY_SEPARATOR) {
                                                $prefix .= DIRECTORY_SEPARATOR;
                                            }
                                        }

                                        $schemaPath = $prefix . str_replace("\\", DIRECTORY_SEPARATOR, $namespace) . "{$pathInfo["dirname"]}";
                                        if (! file_exists($schemaPath)) {
                                            mkdir($schemaPath, 0755, true);
                                        }

                                        /** @var AbstractSchema $class */
                                        $schema = $class::schemaSerialize();
                                        file_put_contents($schemaPath . DIRECTORY_SEPARATOR . "{$pathInfo["filename"]}.json",
                                            json_encode($schema, JSON_PRETTY_PRINT));
                                    }
                                }
                            }
                        }
                    }
                    break;

                case "classmap":
                case "files":
                    foreach ($lookup as $i => $path) {
                        $autoload[$std][$i] = $path;
                    }
                    break;

                default:
                    unset($autoloadMap[$std]);
            }
        }
    }
}
