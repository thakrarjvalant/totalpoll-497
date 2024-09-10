<?php
! defined( 'ABSPATH' ) && exit();
 return array(
    'root' => array(
        'name' => 'totalsuite/totalpoll',
        'pretty_version' => '4.9.7',
        'version' => '4.9.7.0',
        'reference' => '30e9c5edbc9427942de69925c750154c5df929f2',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'container-interop/container-interop' => array(
            'pretty_version' => '1.2.0',
            'version' => '1.2.0.0',
            'reference' => '79cbf1341c22ec75643d841642dd5d6acd83bdb8',
            'type' => 'library',
            'install_path' => __DIR__ . '/../container-interop/container-interop',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'container-interop/container-interop-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '^1.1',
            ),
        ),
        'league/container' => array(
            'pretty_version' => '2.2.0',
            'version' => '2.2.0.0',
            'reference' => 'c0e7d947b690891f700dc4967ead7bdb3d6708c1',
            'type' => 'library',
            'install_path' => __DIR__ . '/../league/container',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'misqtech/totalsuite-totalcore' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '380f09500fc316f27d44189d3809d64f4a0304e2',
            'type' => 'library',
            'install_path' => __DIR__ . '/../misqtech/totalsuite-totalcore',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'orno/di' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '~2.0',
            ),
        ),
        'psr/container' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'reference' => 'b7ce3b176482dbbc1245ebf52b181af44c2cf55f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/container',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'symfony/polyfill-mbstring' => array(
            'pretty_version' => 'v1.7.0',
            'version' => '1.7.0.0',
            'reference' => '78be803ce01e55d3491c1397cf1c64beb9c1b63b',
            'type' => 'library',
            'install_path' => __DIR__ . '/../symfony/polyfill-mbstring',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'totalsuite/totalpoll' => array(
            'pretty_version' => '4.9.7',
            'version' => '4.9.7.0',
            'reference' => '30e9c5edbc9427942de69925c750154c5df929f2',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'woocommerce/action-scheduler' => array(
            'pretty_version' => '3.6.2',
            'version' => '3.6.2.0',
            'reference' => '4eb2fa9737a53e4d284dafcf3e0bf428b5f941bc',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../woocommerce/action-scheduler',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
