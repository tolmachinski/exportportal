<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Library Zoho_Desk
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring code style
 *
 */
class TinyMVC_Library_Zoho_Desk
{
    /**
     * The access token used in each API call
     */
    private $accessToken = null;

    public function __construct()
    {
        if (null === $this->accessToken) {
            $this->generateAccessTokenFromRefreshToken();
        }
    }

    /**
     * Function to getting the ticket by ticket id
     *
     * @param int $ticketId
     *
     * @return array|null
     */
    public function getTicketById(int $ticketId): ?array
    {
        try {
            $res = (new Client())->get('https://desk.zoho.com/api/v1/tickets/' . $ticketId, [
                'headers' => [
                    'orgId' => config('env.ZOHO_ORGANIZATION_ID'),
                    'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
                ]
            ]);

            if (200 !== $res->getStatusCode()) {
                return null;
            }

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $e) {
            return null;
        }
    }

    /**
     * Function to create a ticket
     *
     * @param array $ticketData
     */
    public function createTicket(array $ticketData)
    {
        $json = [
            'departmentId'  => $ticketData['departmentId'],
            'subject'       => $ticketData['subject'],
            'channel'       => 'Web',
        ];

        if (!empty($ticketData['classification'])) {
            $json['classification'] = $ticketData['classification'];
        }

        if (!empty($ticketData['category'])) {
            $json['category'] = $ticketData['category'];
        }

        if (!empty($ticketData['email'])) {
            $json['email'] = $ticketData['email'];
        }

        if (!empty($ticketData['description'])) {
            $json['description'] = $ticketData['description'];

            if ($ticketData['prepareDescription'] ?? false) {
                $descriptionRaw = [];

                foreach ((array) $ticketData['description'] as $key => $value) {
                    $descriptionRaw[] = '<strong>' . $key . ': </strong>' . $value;
                }

                $json['description'] = implode('<br>', $descriptionRaw);
            }
        }

        if (!empty($ticketData['contactId'])) {
            $json['contactId'] = $ticketData['contactId'];
        }

        if (!empty($ticketData['contact'])) {
            $json['contact'] = $ticketData['contact'];
        }

        try {
            $res = (new Client())->post('https://desk.zoho.com/api/v1/tickets', [
                'headers' => [
                    'orgId' => config('env.ZOHO_ORGANIZATION_ID'),
                    'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
                ],
                'json'  => $json,
            ]);

            if (200 !== $res->getStatusCode()) {
                return null;
            }

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $e) {
            return null;
        }
    }

    /**
     * Function to update a ticket
     *
     * @param int $ticketId
     * @param array $ticketData
     */
    public function updateTicket(int $ticketId, array $ticketData)
    {
        $json = [
            'departmentId'  => $ticketData['departmentId'],
            'subject'       => $ticketData['subject'],
            'channel'       => 'Web',
        ];

        if (!empty($ticketData['classification'])) {
            $json['classification'] = $ticketData['classification'];
        }

        if (!empty($ticketData['category'])) {
            $json['category'] = $ticketData['category'];
        }

        if (!empty($ticketData['email'])) {
            $json['email'] = $ticketData['email'];
        }

        if (!empty($ticketData['description'])) {
            $json['description'] = $ticketData['description'];

            if ($ticketData['prepareDescription'] ?? false) {
                $descriptionRaw = [];

                foreach ((array) $ticketData['description'] as $key => $value) {
                    $descriptionRaw[] = '<strong>' . $key . ': </strong>' . $value;
                }

                $json['description'] = implode('<br>', $descriptionRaw);
            }
        }

        if (!empty($ticketData['contactId'])) {
            $json['contactId'] = $ticketData['contactId'];
        }

        if (!empty($ticketData['contact'])) {
            $json['contact'] = $ticketData['contact'];
        }

        try {
            $res = (new Client())->patch('https://desk.zoho.com/api/v1/tickets/' . $ticketId, [
                'headers' => [
                    'orgId' => config('env.ZOHO_ORGANIZATION_ID'),
                    'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
                ],
                'json'  => $json,
            ]);

            if (200 !== $res->getStatusCode()) {
                return null;
            }

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $e) {
            return null;
        }
    }

    /**
     *  Function to create a ticket comment
     *
     * @param int $ticketId
     * @param array $ticketData
     *
     * @return array|null
     */
    public function createTicketComment(int $ticketId, array $ticketData)
    {
        try {
            $res = (new Client())->post('https://desk.zoho.com/api/v1/tickets/' . $ticketId . '/comments', [
                'headers' => [
                    'orgId' => config('env.ZOHO_ORGANIZATION_ID'),
                    'Authorization' => 'Zoho-oauthtoken ' . $this->accessToken
                ],
                'json'  => $ticketData,
            ]);

            if (200 !== $res->getStatusCode()) {
                return null;
            }

            return json_decode($res->getBody()->getContents(), true);
        } catch (ClientException $e) {
            return null;
        }
    }

    /**
     * Function to generate Access Token from the Refresh Token
     *
     * @param array $params
     * @param string $params['refreshToken']
     * @param string $params['scope']
     *
     * @return void
     */
    private function generateAccessTokenFromRefreshToken(array $params = []): void
    {
        try {
            $res = (new Client())->post('https://accounts.zoho.com/oauth/v2/token', [
                'query' => [
                    'refresh_token' => $params['refreshToken'] ?? config('env.ZOHO_DESK_REFRESH_TOKEN'),
                    'client_secret' => config('env.ZOHO_CLIENT_SECRET'),
                    'redirect_uri'  => config('env.ZOHO_REDIRECT_URI'),
                    'access_type'   => 'offline',
                    'grant_type'    => 'refresh_token',
                    'client_id'     => config('env.ZOHO_CLIENT_ID'),
                    'scope'         => $params['scope'] ?? 'Desk.tickets.ALL,Desk.contacts.ALL',
                ]
            ]);

            $resultArray = json_decode($res->getBody()->getContents(), true);
            if (!empty($resultArray['access_token'])) {
                $this->accessToken = $resultArray['access_token'];
            }
        } catch (ClientException $e) {
            //$e->getMessage();
        } catch (ServerException $e) {
            //$e->getMessage();
        }
    }
}

// End of file tinymvc_library_zoho_desk.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_zoho_desk.php
