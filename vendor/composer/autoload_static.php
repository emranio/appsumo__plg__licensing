<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9f640a734c1dbf44c622270e882f33cc
{
    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'Curl' => 
            array (
                0 => __DIR__ . '/..' . '/curl/curl/src',
            ),
        ),
    );

    public static $classMap = array (
        'AppSumo__PLG__Licensing\\AppSumoActions' => __DIR__ . '/../..' . '/core/AppSumoActions.php',
        'AppSumo__PLG__Licensing\\AppSumoApi' => __DIR__ . '/../..' . '/core/AppSumoApi.php',
        'AppSumo__PLG__Licensing\\Globals' => __DIR__ . '/../..' . '/core/Globals.php',
        'AppSumo__PLG__Licensing\\UserForm' => __DIR__ . '/../..' . '/core/UserForm.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit9f640a734c1dbf44c622270e882f33cc::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit9f640a734c1dbf44c622270e882f33cc::$classMap;

        }, null, ClassLoader::class);
    }
}