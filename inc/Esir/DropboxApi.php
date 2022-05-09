<?php

namespace GF\DropBox;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

class DropboxApi
{
    private $refreshToken;
    private $appKey = '37egq9md6b0v31c';
    private $appSecret = 'm8mgibxijcfrhbu';
    /**
     * @var DropboxApp
     */
    private $app;

    private $client;
    /**
     * @var Filesystem
     */
    private $fileSystem;
    /**
     * @var Dropbox
     */
    private $dropbox;

    private $authUrl;


    /**
     * @throws \Kunnu\Dropbox\Exceptions\DropboxClientException
     */
    public function __construct()
    {
        $this->app = new DropboxApp($this->appKey, $this->appSecret);
        $this->dropbox = new Dropbox($this->app);
        $this->authUrl = get_home_url() . '/back-ajax/?action=dropboxAuthComplete';
    }

    /**
     * @throws \JsonException
     */
    public function setupFileSystem(): void
    {
        $this->client = $this->dropbox->getClient();
        $this->getAccessToken();
        $this->fileSystem = new Filesystem(new DropboxAdapter(new Client($this->dropbox->getAccessToken())),
            ['case_sensitive' => false]);

    }

    /**
     * @throws FilesystemException
     */
    public function getFolderContents(): array
    {
        $files = [];
        foreach ($this->fileSystem->listContents('/') as $item) {
            if ($item['type'] === 'file') {
                $files[] = $item['path'];
                $fileData = $this->fileSystem->read($item['path']);
                $files[$item['path']] = $fileData;
            }
        }
        return $files;
    }

    /**
     * @throws FilesystemException
     */
    public function getOrderFileContents(int $orderId): string
    {
        return ($this->fileSystem->read($orderId . '.json'));
    }

    /**
     * @throws \JsonException
     */
    private function getAccessToken(): void
    {
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->app->getClientId(),
            'client_secret' => $this->app->getClientSecret(),
            'refresh_token' => $this->getRefreshToken()
        ];
        $params = http_build_query($params, '', '&');
        $apiUrl = 'https://api.dropboxapi.com/oauth2/token';
        $uri = $apiUrl . "?" . $params;
        $response = $this->client
            ->getHttpClient()
            ->send($uri, "POST", null);
        $response = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->dropbox->setAccessToken($response['access_token'] ?? '');
    }

    /**
     * Redirects user to Dropbox authentication page, after auth is complete
     * redirects user to the specified URL. @return void
     *
     * @see $authUrl
     */
    public function dropBoxAuthConsent(): void
    {
        wp_redirect($this->dropbox->getAuthHelper()
            ->getAuthUrl($this->authUrl, ['token_access_type' => 'offline']));
    }

    /**
     * @throws \Kunnu\Dropbox\Exceptions\DropboxClientException
     */
    public function saveRefreshToken(): void
    {
        global $wpdb;
        $accessTokenInfo = $this->dropbox->getAuthHelper()
            ->getAccessToken($_GET['code'], null, $this->authUrl);
        $refreshToken = $accessTokenInfo->getDataProperty('refresh_token');
        $accountId = $accessTokenInfo->getUid();
        if ($refreshToken) {
            $sql = "SELECT * FROM `dropboxRefreshToken` WHERE accountId = '{$accountId}'";
            $result = $wpdb->get_results($sql);
            if (count($result) === 0) {
                if (!$wpdb->insert('dropboxRefreshToken',
                    ['accountId' => $accountId, 'refreshToken' => $refreshToken])) {
                    throw new \RuntimeException('Failed to insert refresh token');
                }
                return;
            }
            if (!$wpdb->update('dropboxRefreshToken', ['refreshToken' => $refreshToken],
                ['accountId' => $accountId])) {
                throw new \RuntimeException('Failed to update refresh token');
            }
        }
    }

    public function getRefreshToken(): string
    {
        global $wpdb;
        $sql = "SELECT * FROM `dropboxRefreshToken`";
        return $wpdb->get_results($sql)[0]->refreshToken;
    }
}