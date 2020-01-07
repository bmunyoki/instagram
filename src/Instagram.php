<?php

namespace bmunyoki\Instagram;

class Instagram{
    /*
     * Facebook App Id
     */
    private $app_id;

    /*
     * Facebook App Secret
     */
    private $app_secret;

    /*
     * Instagram Redirect URL
     */
    private $redirect_url;

    /*
     * Facebook Object
     */
    private $fb;
    
    /*
     * Facebook helper
     */
    private $fbHelper;
    
    /*
     * Facebook Permissions
     */
    private $permissions = ['instagram_basic', 'manage_pages'];

    /**
     * Instantiates a new Facebook class object, Facebook Helper 
     *
     */
    public function __construct() {
    	$this->app_id = config('instagram.app_id');
    	$this->app_secret = config('instagram.app_secret');
    	$this->redirect_url = config('instagram.redirect_url');

		$this->fb = new \Facebook\Facebook([
		    'app_id' => $this->app_id,
		    'app_secret' => $this->app_secret,
		    'default_graph_version' => 'v3.3',
		]);
		$this->fbHelper = $this->fb->getRedirectLoginHelper();
    }

    /**
     * Get redirect URL
     *
     */
    public function getLoginUrl() {
		return $this->fbHelper->getLoginUrl($this->redirect_url, $this->permissions);
    }

    /**
     * returns an AccessToken.
     *
     *
     * @return AccessToken|null|false
     *
     * @throws FacebookSDKException
     */
    public function getAccessToken() {
		try {
		    $accessToken = $this->fbHelper->getAccessToken();

		} catch (Facebook\Exceptions\FacebookResponseException $e) {
		    // Graph API returned an error
		    Log::error('Graph returned an error: ' . $e->getMessage());
		    return false;

		} catch (Facebook\Exceptions\FacebookSDKException $e) {
		    // Validation fail or other local issues
		    Log::error('Facebook SDK returned an error: ' . $e->getMessage());
		    return false;
		}

		if (!isset($accessToken)) {
		    if ($this->fbHelper->getError()) {
				$error = "Error: " . $this->fbHelper->getError() . "\n";
				$error .= "Error Code: " . $this->fbHelper->getErrorCode() . "\n";
				$error .= "Error Reason: " . $this->fbHelper->getErrorReason() . "\n";
				$error .= "Error Description: " . $this->fbHelper->getErrorDescription() . "\n";
				Log::error($error);
				return false;

		    } else {
				Log::error('Bad request');
				return false;
		    }
		    return false;
		}

		// Logged in
		$accessTokenValue = $accessToken->getValue();

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $this->fb->getOAuth2Client();

		// Get the access token metadata from /debug_token
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId(config('instagram.app_id'));

		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');

		$tokenMetadata->validateExpiration();
		if (!$accessToken->isLongLived()) {
		    // Exchanges a short-lived access token for a long-lived one
		    try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		    } catch (Facebook\Exceptions\FacebookSDKException $e) {
				Log::error("<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n");
		    }
		    $accessTokenValue = ($accessToken->getValue());
		}
		return $accessTokenValue;
    }

    /**
     * returns Connected Instagram IDs
     *
     *
     * @return array|false
     *
     * @throws FacebookSDKException
     */
    public function getConnectedInstagramID($accessToken) {
		try {
		    $response = $this->fb->get('/me/accounts', $accessToken);

		} catch (\Facebook\Exceptions\FacebookResponseException $e) {
		    // Graph API returned an error
		    Log::error('Graph returned an error: ' . $e->getMessage());
		    return false;

		} catch (\Facebook\Exceptions\FacebookSDKException $e) {
		    // Validation failed or other local issues
		    Log::error('Facebook SDK returned an error: ' . $e->getMessage());
		    return false;
		}

		$decodedData = json_decode($response->getBody());
		$connectedAccountIDs = [];
		foreach ($decodedData->data as $key => $item) {
		    $pageID = $item->id;
		    try {
				$connectedAccountResponse = $this->fb->get('/' . $pageID . '?fields=connected_instagram_account,instagram_business_account', $accessToken);
				$decodedConnectedAccountData = json_decode($connectedAccountResponse->getBody());

				if (!empty($decodedConnectedAccountData->instagram_business_account)) {
				    $connectedAccountIDs[] = $decodedConnectedAccountData->instagram_business_account->id;
				} else if (!empty($decodedConnectedAccountData->connected_instagram_account)) {
				    $connectedAccountIDs[] = $decodedConnectedAccountData->connected_instagram_account->id;
				}
		    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
				// Graph API returned an error
				Log::error('Graph returned an error: ' . $e->getMessage());
				return false;
		    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
				// Validation failed or other local issues
				Log::error('Facebook SDK returned an error: ' . $e->getMessage());
				return false;
		    }
		}

		return $connectedAccountIDs;
    }

    /**
     * returns User Info for Connected Instagram IDs
     *
     *
     * @return array|false
     *
     * @throws FacebookSDKException
     */
    public function getUserInfo() {
		$accessToken = $this->getAccessToken();
		$connectedInstagramIDs = $this->getConnectedInstagramID($accessToken);
		$instagramuserInfo = [];
		
		if (!empty($connectedInstagramIDs)) {
		    foreach ($connectedInstagramIDs as $key => $value) {
				try {
				    // Get the \Facebook\GraphNodes\GraphUser object for the current user.
				    // If you provided a 'default_access_token', the '{access-token}' is optional.
				    $response = $this->fb->get('/'.$value.'?fields=name,biography,username,ig_id,followers_count,follows_count,media_count,profile_picture_url,website', $accessToken);
				    $decodedData = json_decode($response->getBody());
				    $instagramuserInfo[] = $decodedData;

				} catch (\Facebook\Exceptions\FacebookResponseException $e) {
				    // Graph API returned an error
				    Log::error('Graph returned an error: ' . $e->getMessage());
				    return false;
				} catch (\Facebook\Exceptions\FacebookSDKException $e) {
				    // Validation fails or other local issues
				    Log::error('Facebook SDK returned an error: ' . $e->getMessage());
				    return false;
				}
		    }
		}
		
		return ["userInfo" => $instagramuserInfo, "accessToken" => $accessToken];

    }
}