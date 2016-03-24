<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class TmdbLoginForm extends CFormModel
{
	public $username;
	public $rememberMe;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
                    // username and password are required
                    array('username', 'required'),
                    // rememberMe needs to be a boolean
                    array('rememberMe', 'boolean'),
                    // password needs to be authenticated
                    array('username', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
            return array(
                'username' => 'Your TMDb API key',
                'rememberMe'=>'Remember me next time',
            );
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
            if(!$this->hasErrors())
            {
                $this->_identity=new TmdbIdentity($this->username);
                $result = $this->_identity->authenticate();
                if(!$result) {
                    $error = $this->_identity->errorCode === TmdbIdentity::ERROR_APIKEY_INVALID
                            ? 'Invalid API key: You must be granted a valid key'
                            : 'Something went wrong, contact the website administrator';
                    $this->addError('username',$error);
                }
            }
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
            if($this->_identity===null)
            {
                $this->_identity=new TmdbIdentity($this->username);
                $this->_identity->authenticate();
            }
            if($this->_identity->errorCode===TmdbIdentity::ERROR_NONE)
            {
                $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
                return Yii::app()->user->login($this->_identity,$duration);
            }
            else {
                return false;
            }
	}
}
