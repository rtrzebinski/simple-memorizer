<?php

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Gets user data array from oauth service
     * than runs loginOauthUser
     * @param string $strProviderName
     */
    public function oauth($strOauthProviderName) {
        $this->load->spark('oauth2/0.4.0');

        // create oauth provider object
        $arrOauthProvidersData = $this->config->item('oauthProvidersData');
        $oProvider = $this->oauth2->provider($strOauthProviderName, $arrOauthProvidersData[$strOauthProviderName]);

        // get user data array
        if ($this->input->get('code')) {
            $strToken = $oProvider->access($this->input->get('code'));
            $arrOauthUserData = $oProvider->get_user_info($strToken);
            $this->loginOauthUser($strOauthProviderName, $arrOauthUserData);
        } else {
            $strAuthUrl = $oProvider->authorize();
            // redirects this page which is needed to exchange data between servers
            redirect($strAuthUrl);
        }

        // user canceled permissions request - go to home page
        if ($this->input->get('error')) {
            redirect(base_url());
        }
    }

    /**
     * Logs user in, if it exists in database
     * Or creates new one if doesn't
     * Restores session and redirects to home page after that
     * @param string $strOauthProvider
     * @param array $arrUserData
     */
    private function loginOauthUser($strOauthProviderName, $arrOauthUserData) {
        $oUser = new User();

        // try to authorize with oauth
        try {
            // throws an exception on faliture, loads object on success
            $oUser->authorizeOauth($arrOauthUserData['uid'], $strOauthProviderName); 
        } catch (Exception $exc) {
            // if log in incorrect -> user doesn't exist -> create new one
            $oUser->create($arrOauthUserData['name'], $arrOauthUserData['email'], $arrOauthUserData['uid'], $strOauthProviderName);
        }

        // write user data to session and go to main page
        $this->sessionmanager->setUser($oUser);
        redirect(base_url());
    }

}