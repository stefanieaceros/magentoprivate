<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

try {
   // require __DIR__ . '/app/bootstrap.php';
    require 'app/bootstrap.php';
} catch (\Exception $e) {
    echo $e->getMessage();
}
  
   //$domainName = $_SERVER['SERVER_NAME'];
   $domainName = $_SERVER['HTTP_HOST'];
    switch ($domainName) {
    case 'samplefashion.com':
        $runType = 'store';
        $runCode = 'fashion';
    break;
    case 'samplekidstore.com':
        $runType = 'store';
        $runCode = 'kid';
    break;
    case 'magentotest.com':
        $runType = 'website';
        $runCode = 'base';
    break;
    default:
        $runType = 'website';
        $runCode = 'base';
    break;
    }
 
   // $params = $_SERVER;
    $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = $runCode;
    $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = $runType;
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);    
    $app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
    $bootstrap->run($app);




