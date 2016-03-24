<?php
return array(
    'aliases' => array(
        'vendor' => 'application.vendor',
    ),
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR .'..',
    'name' => 'My Awesome Yii Site',
    'components' => array(
        'db' => array(
            // Sqlite
            'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
        ),
    ),
    'params' => array(
        'composer.callbacks' => array(
            // args for Yii command runner
            //'yiisoft/yii-install' => array('yiic', 'webapp', dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..','git'),
            'post-update' => array('yiic', 'migrate'),
            'post-install' => array('yiic', 'migrate'),
        ),
    ),
);