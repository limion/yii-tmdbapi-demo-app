<?php

/*
 * WebUser component
 */
class TmdbWebUser extends \CWebUser {
    
    const CACHE_KEY = 'Yii.TmdbWebUser.tmdbConfig';
    
    /**  
     * @var string the ID of the application component ID that is used 
     * to cache the Tmdb Configuration data.
     */
    public $cacheID = 'cache';
    
    /**
     * @var int the number of seconds in which the cached Configuration will expire. 0 means never expire.
     * Default is 172800 (2 days)
     */
    public $cacheExpire = 172800;
    
    protected function beforeLogin($id, $states, $fromCookie)
    {
        // get tmdb configuration
        $tmdbConfig = null;
        if(false !== $this->cacheID && null !== $cache = Yii::app()->getComponent($this->cacheID)) {
            $tmdbConfig = $cache->get(self::CACHE_KEY);
        }
        if (empty($tmdbConfig)) {
            try {
                $config = Yii::app()->tmdb
                    ->setApiKey($id)
                    ->getConfiguration();

            } catch (TmdbDemo\ApiException $e) {
                $this->setFlash('error','Something went wrong. Can\'t get TMDb configuration. Please, notify the website administrator.');
                Yii::log(LogHelper::buildApiMessage($e),CLogger::LEVEL_ERROR);
                return false;
            }
            $tmdbConfig = $config;
        }
        if ($tmdbConfig) {
            // save configuration to the session
            $this->setState('tmdbConfig', $tmdbConfig);
            if(isset($cache)) {
		$cache->set(self::CACHE_KEY,$tmdbConfig, $this->cacheExpire);
            }
        }
        else {
            $this->setFlash('error','Something went wrong. TMDb configuration is empty. Please, notify the website administrator.');
            Yii::log(sprintf('Got empty TMDb configuration for API Key: %s',$id),CLogger::LEVEL_ERROR);
            return false;
        }
        
        if ($fromCookie) {
            // is apiKey ok?
            if (!isset($config)) {
                try {
                    $config = Yii::app()->tmdb
                        ->setApiKey($id)
                        ->getConfiguration();

                } catch (TmdbDemo\ApiException $e) {
                    // apiKey is not ok, back to login page
                    return false;
                }
            }
        }
        return parent::beforeLogin($id, $states, $fromCookie);
    }
            
}