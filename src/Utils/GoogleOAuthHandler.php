<?php

namespace ybc\octavia\Utils;


use Google\Client as Google_Client;
use Google\Service\Gmail as Google_Service_Gmail;

class GoogleOAuthData
{
    public string $access_token;
    public string $refresh_token;
    public int $expires_in;
    public int $expires_at;

    public function __construct(string $access_token, string $refresh_token, int $expires_in, int $expires_at)
    {
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
        $this->expires_in = $expires_in;
        $this->expires_at = $expires_at;
    }

    public function to_array(): array
    {
        return [
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'expires_in' => $this->expires_in,
            'expires_at' => $this->expires_at
        ];
    }
}

class GoogleOAuthHandler
{
    private string $data_path = OCTAVIA_GOOGLE_OAUTH_DATA_PATH;
    private string $config_path = OCTAVIA_GOOGLE_OAUTH_CONFIG_PATH;
    private string $session_key = 'google_oauth_data';
    private $logger;

    public function __construct()
    {
        $this->logger = new Log("GoogleOAuth");

        if (!session_id()) {
            session_start();
        }
    }

    /**
     * Gets the Google OAuth data from the cache.
     * 
     * @return GoogleOAuthData|null The data, or null if not found
     */
    private function get_cached_data(): ?GoogleOAuthData
    {
        if (isset($_SESSION[$this->session_key])) {
            return $_SESSION[$this->session_key];
        }

        return null;
    }

    /**
     * Gets the Google OAuth data from the cache or the tokens file.
     * If the access token is expired, it will refresh it and save it to the cache and tokens file.
     * 
     * @return GoogleOAuthData The data
     */
    public function get_data(): GoogleOAuthData
    {
        $cached_data = $this->get_cached_data();

        if (isset($cached_data)) {
            if ($this->_is_access_token_expired($cached_data->expires_at)) {
                return $this->_refresh_access_token($cached_data->refresh_token);
            }

            return $cached_data;
        }

        $file_data = $this->_read_from_file();
        if ($this->_is_access_token_expired($file_data->expires_at)) {
            return $this->_refresh_access_token($file_data->refresh_token);
        }

        return $file_data;
    }

    /** 
     * Checks if the access token is expired
     * 
     * @param int $expires_at The timestamp of when the access token expires
     * @return bool True if the access token is expired, false otherwise
     */
    private function _is_access_token_expired(int $expires_at): bool
    {
        return time() > $expires_at;
    }

    private function _save_data(GoogleOAuthData $data): void
    {
        $this->_write_to_file($data);
        $_SESSION[$this->session_key] = $data;
    }

    /**
     * Gets a new access token from Google OAuth using the refresh token, 
     * and saves it to the tokens file and session (cache)
     * 
     * @param string $refresh_token
     * @return GoogleOAuthData The new data
     */
    private function _refresh_access_token(string $refresh_token): GoogleOAuthData
    {
        $this->logger->info("Refreshing Google OAuth access token");

        // Handle the refreshing of the access token
        $client = new Google_Client();
        $client->setAuthConfig($this->config_path);
        $client->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAccessType('offline');
        $client->fetchAccessTokenWithRefreshToken($refresh_token);

        // Build the data structure
        $new_data = $this->_build_bean_from_google_client($client);

        // Save the token information in file and session
        $this->_save_data($new_data);

        return $new_data;
    }

    /**
     * Reads the tokens file and returns the data
     * 
     * @return GoogleOAuthData The data
     * @throws \Exception If the file could not be read or the file is invalid
     */
    private function _read_from_file(): GoogleOAuthData
    {
        $content = file_get_contents($this->data_path);

        if ($content === FALSE) {
            throw new \Exception("Could not read tokens file");
        }

        $data = json_decode($content, true);

        if (!isset($data['access_token']) || !isset($data['refresh_token']) || !isset($data['expires_in']) || !isset($data['expires_at'])) {
            throw new \Exception("Invalid tokens file");
        }

        return new GoogleOAuthData($data['access_token'], $data['refresh_token'], $data['expires_in'], $data['expires_at']);
    }

    /**
     *  Writes the data to the tokens file
     * 
     * @param GoogleOAuthData $data The data to write
     */
    private function _write_to_file(GoogleOAuthData $data): void
    {
        $result = file_put_contents($this->data_path, json_encode($data->to_array()));

        if ($result === FALSE) {
            throw new \Exception("Could not write to tokens file");
        }
    }

    /**
     * Builds a GoogleOAuthData bean from a Google_Client
     *  
     * @param Google_Client $client The Google_Client to build from
     * @return GoogleOAuthData The bean
     */
    private function _build_bean_from_google_client(Google_Client $client): GoogleOAuthData
    {
        $new_token_config = $client->getAccessToken();
        $refresh_token = $client->getRefreshToken();
        $access_token = $new_token_config['access_token'];
        $expires_in = $new_token_config['expires_in'];
        $expires_at = time() + $expires_in;

        return new GoogleOAuthData($access_token, $refresh_token, $expires_in, $expires_at);
    }

    /**
     * Handles the initial Google OAuth prompt
     * 
     * @param string $redirect_uri The redirect URI to use
     * @return bool True if the prompt was handled successfully, false otherwise
     */
    public function handle_initial_prompt(string $redirect_uri): bool
    {
        $this->logger->info("Handling initial Google OAuth prompt");
        $client = new Google_Client();
        $client->setAuthConfig($this->config_path);
        $client->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setRedirectUri($redirect_uri); // Ensure this matches what you set in Google Cloud Console
        $client->setAccessType('offline'); // To get a refresh token
        $client->setPrompt("consent"); // To force receive a refresh token


        if (!isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
            exit;
        }

        // Authenticate and fetch the access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Check for errors
        if (isset($token['error']) || !isset($token['access_token'])) {
            return false;
        }

        $new_data = $this->_build_bean_from_google_client($client);

        // Save the token information for later usage
        $this->_save_data($new_data);

        return true;
    }
}
