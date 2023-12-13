<?php

namespace App\Console\Commands;

use App\Common\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use zcrmsdk\oauth\ZohoOAuth;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;

class CacheZohoToken extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'zoho:cache-token';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that caches Zoho OAuth token.';

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->ensureHasArgument($input, 'refresh-token');
        $this->ensureHasOption($input, 'user-id');
        $this->ensureHasOption($input, 'client-id');
        $this->ensureHasOption($input, 'client-secret');
        $this->ensureHasOption($input, 'redirect-uri');
        $this->ensureHasOption($input, 'output-dir');
        $this->ensureHasOption($input, 'output-file');
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array('refresh-token', InputArgument::REQUIRED, 'The OAuth refresh token.'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array('user-id', 'u', InputArgument::OPTIONAL, 'The client email value', $_ENV['ZOHO_USER_EMAIL'] ?? null),
            array('client-id', 'i', InputArgument::OPTIONAL, 'The client ID value', $_ENV['ZOHO_CLIENT_ID'] ?? null),
            array('client-secret', 's', InputArgument::OPTIONAL, 'The client secret value', $_ENV['ZOCO_CLIENT_SECRET'] ?? null),
            array('redirect-uri', 'U', InputArgument::OPTIONAL, 'The redirect URI', $_ENV['ZOHO_REDIRECT_URI'] ?? null),
            array('output-dir', 'O', InputArgument::OPTIONAL, 'The path where ouath token cache will be exported.', $_ENV['ZOHO_AUTH_CACHE_PATH'] ?? null),
            array('output-file', 'F', InputArgument::OPTIONAL, 'The name of the file where tokens are exported.', 'zcrm_oauthtokens.txt'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function handle()
    {
        $this->ensureOutputFileExsists("{$this->option('output-dir')}/{$this->option('output-file')}");

        ZCRMRestClient::initialize(array(
            'access_type'            => 'offline',
            'client_id'              => $this->option('client-id'),
            'client_secret'          => $this->option('client-secret'),
            'redirect_uri'           => $this->option('redirect-uri'),
            'token_persistence_path' => $this->option('output-dir'),
        ));
        ZohoOAuth::getClientInstance()->generateAccessTokenFromRefreshToken($this->argument('refresh-token'), $this->option('user-id'));
    }

    /**
     * Ensures that the output file exists before Zoho will try to open it.
     *
     * @param string $filePath
     */
    private function ensureOutputFileExsists(string $filePath): void
    {
        if (!file_exists($filePath)) {
            file_put_contents($filePath, '');
        }
    }
}
