<?php

namespace ybc\octavia\Utils;


use Google\Client as Google_Client;
use Google\Service\Gmail as Google_Service_Gmail;
use ybc\octavia\Utils\Log;

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

class GoogleOAuthConfig
{
    public string $client_id;
    public string $project_id;
    public string $auth_uri;
    public string $token_uri;
    public string $auth_provider_x509_cert_url;
    public string $client_secret;
    public array $redirect_uris;
    public array $javascript_origins;

    public function __construct(array $config)
    {
        $this->client_id = $config['client_id'];
        $this->project_id = $config['project_id'];
        $this->auth_uri = $config['auth_uri'];
        $this->token_uri = $config['token_uri'];
        $this->auth_provider_x509_cert_url = $config['auth_provider_x509_cert_url'];
        $this->client_secret = $config['client_secret'];
        $this->redirect_uris = $config['redirect_uris'];
        $this->javascript_origins = $config['javascript_origins'];
    }

    public function to_array(): array
    {
        return [
            'client_id' => $this->client_id,
            'project_id' => $this->project_id,
            'auth_uri' => $this->auth_uri,
            'token_uri' => $this->token_uri,
            'auth_provider_x509_cert_url' => $this->auth_provider_x509_cert_url,
            'client_secret' => $this->client_secret,
            'redirect_uris' => $this->redirect_uris,
            'javascript_origins' => $this->javascript_origins
        ];
    }
}

class GoogleOAuthHandler
{
    private string $data_path = OCTAVIA_GOOGLE_OAUTH_DATA_PATH;
    private string $config_path = OCTAVIA_GOOGLE_OAUTH_CONFIG_PATH;
    private string $session_key = 'google_oauth_data';

    public function __construct()
    {
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
        if (isset($_SESSION[$this->session_key]) && $_SESSION[$this->session_key] instanceof GoogleOAuthData) {
            return $_SESSION[$this->session_key];
        }

        return null;
    }

    /**
     * Gets the Google OAuth data from the cache or the data file.
     * If the access token is expired, it will refresh it and save it to the cache and data file.
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
     * Gets the Google OAuth config from the config file
     * 
     * @return GoogleOAuthConfig The config
     */
    public function get_config(): GoogleOAuthConfig
    {
        // check if file exists
        if (!file_exists($this->config_path)) {
            throw new \Exception("Config file does not exist");
        }

        $file_content = file_get_contents($this->config_path);

        if ($file_content === FALSE) {
            throw new \Exception("Could not read config file");
        }

        // decode and get the web config
        $config = json_decode($file_content, true);

        if (!isset($config['web'])) {
            throw new \Exception("Invalid config file");
        }

        $config = $config['web'];

        // make sure all the required fields are present dynamically by getting the class variables
        $class_vars = get_class_vars(GoogleOAuthConfig::class);

        foreach ($class_vars as $key => $value) {
            if (!isset($config[$key])) {
                throw new \Exception("Invalid config file");
            }
        }

        return new GoogleOAuthConfig($config);
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
     * and saves it to the data file and session (cache)
     * 
     * @param string $refresh_token
     * @return GoogleOAuthData The new data
     */
    private function _refresh_access_token(string $refresh_token): GoogleOAuthData
    {
        Log::info("Refreshing Google OAuth access token");

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
     * Reads the data file and returns the data
     * 
     * @return GoogleOAuthData The data
     * @throws \Exception If the file could not be read or the file is invalid
     */
    private function _read_from_file(): GoogleOAuthData
    {

        // check if file exists
        if (!file_exists($this->data_path)) {
            throw new \Exception("Data file does not exist");
        }

        $content = file_get_contents($this->data_path);

        if ($content === FALSE) {
            throw new \Exception("Could not read data file");
        }

        $data = json_decode($content, true);

        if (!isset($data['access_token']) || !isset($data['refresh_token']) || !isset($data['expires_in']) || !isset($data['expires_at'])) {
            throw new \Exception("Invalid data file");
        }

        return new GoogleOAuthData($data['access_token'], $data['refresh_token'], $data['expires_in'], $data['expires_at']);
    }

    /**
     *  Writes the data to the data file
     * 
     * @param GoogleOAuthData $data The data to write
     */
    private function _write_to_file(GoogleOAuthData $data): void
    {
        $result = file_put_contents($this->data_path, json_encode($data->to_array()));

        if ($result === FALSE) {
            throw new \Exception("Could not write to data file");
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
        Log::info("Handling initial Google OAuth prompt");
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
            Log::error("Error while fetching access token");
            return false;
        }

        $new_data = $this->_build_bean_from_google_client($client);

        // Save the token information for later usage
        $this->_save_data($new_data);

        return true;
    }
}
