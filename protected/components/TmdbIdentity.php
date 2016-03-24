<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class TmdbIdentity extends CBaseUserIdentity
{
    const ERROR_APIKEY_INVALID = 3;
    const ERROR_APIEXCEPTION = 4;
    
    /**
     * Tmdb API key
     * @var string
     */
    public $username;
    
    /**
    * Constructor.
    * @param string $username Tmdb API key
    */
    public function __construct($username)
    {
        $this->username=$username;
    }
    
    /**
     * Authenticates a user.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        $this->errorCode=self::ERROR_NONE;
        try {
            $data = Yii::app()->tmdb
                ->setApiKey($this->username)
                ->getAuthenticationGuestSessionNew();
            
        } catch (TmdbDemo\ApiException $e) {
            if (7 == $e->getCode()) {
                $this->errorCode=self::ERROR_APIKEY_INVALID;
            }
            else {
                $this->errorCode=self::ERROR_APIEXCEPTION;
            }
        }
        if ($this->errorCode === self::ERROR_NONE) {
            $this->setState('guest_session_id', $data['guest_session_id']);
            $this->setState('expires_at', $data['expires_at']);
        }
        return !$this->errorCode;
    }
    
    /**
    * Returns the unique identifier for the identity.
    * The default implementation simply returns {@link username}.
    * This method is required by {@link IUserIdentity}.
    * @return string the unique identifier for the identity.
    */
    public function getId()
    {
        return $this->username;
    }
   /**
    * Returns the display name for the identity.
    * The default implementation simply returns {@link username}.
    * This method is required by {@link IUserIdentity}.
    * @return string the display name for the identity.
    */
    public function getName()
    {
        return 'GuestSessionId: '.$this->getState('guest_session_id');
    }

}