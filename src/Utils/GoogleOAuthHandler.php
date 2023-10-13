<?php

namespace ybc\octavia\Utils;


use Google\Client as Google_Client;
use Google\Service\Gmail as Google_Service_Gmail;

class GoogleOAuthData {
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
    private string $tokens_path = OCTAVIA_GOOGLE_OAUTH_DATA_PATH;
    private string $oauth2_config_path = OCTAVIA_GOOGLE_OAUTH_CONFIG_PATH;
    private string $session_key = 'google_oauth_data';
    private $logger;

    public function __construct()
    {
        $this->logger = new Log("GoogleOAuth");

        if (!session_id()) {
            session_start();
        }
    }

    public function get_tokens(): GoogleOAuthData
    {
        if (isset($_SESSION[$this->session_key])) {
            $data = $_SESSION[$this->session_key] as GoogleOAuthData;
            if ($this->_is_access_token_expired($data->expires_at)) {
                $data = $this->_refresh_access_token($data->refresh_token);
            }
            return $data;
        }

        $data = $this->_read_from_file();
        if ($this->_is_access_token_expired($data->expires_at)) {
            $data = $this->_refresh_access_token($data->refresh_token);
        }

        $_SESSION[$this->session_key] = $data;
        return $data;
    }

    private function _is_access_token_expired(int $expires_at): bool
    {
        return time() > $expires_at;
    }

    private function _refresh_access_token(string $refresh_token): GoogleOAuthData
    {
        $this->logger->info("Refreshing Google OAuth access token");
        $client = new Google_Client();
        $client->setAuthConfig($this->oauth2_config_path);
        $client->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->setAccessType('offline');
        $client->fetchAccessTokenWithRefreshToken($refresh_token);

        $new_data = $this->_build_bean_from_google_client($client);

        $this->_write_to_file($new_data);
        $_SESSION[$this->session_key] = $new_data;

        return $new_data;
    }

    private function _read_from_file(): GoogleOAuthData
    {
        $content = file_get_contents($this->tokens_path);

        if (!$content) {
            throw new \Exception("Could not read tokens file");
        }

        $data = json_decode($content, true);

        if (!isset($data['access_token']) || !isset($data['refresh_token']) || !isset($data['expires_in']) || !isset($data['expires_at'])) {
            throw new \Exception("Invalid tokens file");
        }

        return new GoogleOAuthData($data['access_token'], $data['refresh_token'], $data['expires_in'], $data['expires_at']);
    }

    private function _write_to_file(GoogleOAuthData $data): void
    {
        file_put_contents($this->tokens_path, json_encode($data));
    }

    private function _build_bean_from_google_client(Google_Client $client): GoogleOAuthData
    {
        $new_token_config = $client->getAccessToken();
        $access_token = $new_token_config['access_token'];
        $refresh_token = $client->getRefreshToken();
        $expires_in = $access_token['expires_in'];
        $expires_at = time() + $expires_in;

        return new GoogleOAuthData($access_token, $refresh_token, $expires_in, $expires_at);
    }

    public function handle_initial_prompt(string $redirect_uri): bool
    {
        $this->logger->info("Handling initial Google OAuth prompt");
        $client = new Google_Client();
        $client->setAuthConfig($this->oauth2_config_path);
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
        $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Check for errors
        if (isset($token['error']) || !isset($token['access_token'])) {
            return false;
        }

        $data = $this->_build_bean_from_google_client($client);

        // Save the token information for later usage
        $this->_write_to_file($token);
        $_SESSION[$this->session_key] = $data;

        return true;
    }
}
