<?php

namespace Tinder;

/**
 * Wrapper for Tinder API
 */
class TinderAPI
{
    protected $serviceUrl = 'https://api.gotinder.com/';
    protected $fbUserId;
    protected $fbToken;
    protected $authToken;
    protected $user;

    /**
     * Constructor of TinderAPI Class Wrapper
     * @param string $facebookUserId facebook tinder id for OAuth
     * @param string $facebookToken  facebook token for OAuth
     * @param string $token_tinder   tinder token
     */
    function __construct(string $facebookUserId, string $facebookToken, string $token_tinder = null)
    {
        if (function_exists('curl_init') === false)
        {
            throw new \ErrorException('This service requires the CURL PHP extension.');
        }

        $this->fbUserId     = $facebookUserId;
        $this->fbToken      = $facebookToken;
        $this->authToken    = $token_tinder;

        if(empty($token_tinder))
        {
            $auth               = $this->authenticate();

            if(!$auth)
            {
                throw new \ErrorException('Invalid Facebook id or token.');
            }
        }
    }

    /**
     * Function to send datas on get and post to an url
     * @param string $url    url
     * @param  string $method POST or GET
     * @param  array  $data   an array of data to send
     * @return string         json of data callback
     */
    private function api (string $url = null, string $method = "POST", array $data = []) : array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:8888"); // debug only

        if($method == "POST")
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Auth-Token: ' . $this->authToken,
            'Content-type: application/json',
            'app_version: 3',
            'platform: ios',
            'User-Agent: Tinder/3.0.4 (iPhone; iOS 7.1; Scale/2.00)',
            'os_version: 700001',
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Tinder/3.0.4 (iPhone; iOS 7.1; Scale/2.00)');

        return json_decode(curl_exec($ch), true);
    }

    /**
     * Authentificate tinder on OAuth and get the tinder token
     * @return bool if the OAuth informations on facebook are validated
     */
    private function authenticate () : bool
    {
        $response = $this->api($this->serviceUrl . "auth", "POST", ['facebook_token' => $this->fbToken, 'facebook_id' => $this->fbUserId]);

        var_dump($response);

        if ($response && isset($response['user']['api_token']))
        {
            $this->authToken = $response['user']['api_token'];
            $this->user = $response['user'];

            return true;
        }

        return false;
    }

    /**
     * Get the message randomly into list
     * @param  array  $list table of sentences to send
     * @return array       the sentence randomly generated from the array
     */
    public function random_message (array $list) : string
    {
        $random_key = 0;
        $random_key = array_rand($list);

        return $list[$random_key];
    }

    /**
     * Send message to an tinder user
     * @param  string $user_id tinde id
     * @param  string $message the message
     * @return array          json callback
     */
    public function sendMessage (string $user_id, string $message) : array
    {
        return $this->api($this->serviceUrl . "user/matches/" . $user_id, "POST", ['message' => $message]);
    }

    /**
     * Like tinder user
     * @param  string $user_id tinder user id
     * @return array          json callback
     */
    public function like (string $user_id) : array
    {
        return $this->api($this->serviceUrl . "like/" . $user_id, "GET");
    }

    /**
     * Dislike tinder user
     * @param  string $user_id tinder user id
     * @return array          json callback
     */
    public function pass (string $user_id) : array
    {
        return $this->api($this->serviceUrl . "pass/" . $user_id, "GET");
    }

    /**
     * Get the last update informations
     * @param  string $lastActivityTime int of the last activity time
     * @return array                   json callback
     */
    public function update (string $lastActivityTime = "") : array
    {
        return $this->api($this->serviceUrl . "updates", "POST", ['last_activity_date' => $lastActivityTime]);
    }

    /**
     * Get all of recommandtions arround the location of the gps tinder
     * @return array json callback
     */
    public function recommendations () : array
    {
        return $this->api($this->serviceUrl . "user/recs", "GET");
    }

    /**
     * View the profile of an user
     * @param  string $user_id tinder user id
     * @return array          json callback
     */
    public function getProfile (string $user_id) : array
    {
        return $this->api($this->serviceUrl . "user/" . $user_id, "GET");
    }

    public function getTinderToken () : string
    {
        return $this->authToken;
    }
}

?>
