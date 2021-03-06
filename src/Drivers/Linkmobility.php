<?php

namespace Tzsk\Sms\Drivers;

use GuzzleHttp\Client;
use Tzsk\Sms\Abstracts\Driver;

class Linkmobility extends Driver
{
    /**
     * Linkmobility Settings.
     *
     * @var object|null
     */
    protected $settings;

    /**
     * Http Client.
     *
     * @var Client|null
     */
    protected $client;

    /**
     * Construct the class with the relevant settings.
     *
     * SendSmsInterface constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->settings = (object) $settings;
        $this->client = new Client();
    }

    /**
     * Send text message and return response.
     *
     * @return mixed
     */
    public function send()
    {
        $numbers = implode(',', $this->recipients);

        $response = $this->client->request('POST', $this->settings->url, [
            'form_params' => [
                'USER' => $this->settings->username,
                'PW' => $this->settings->password,
                'RCV' => $numbers,
                'SND' => urlencode($this->settings->sender),
                'TXT' => $this->body,
            ],
        ]);

        $data = $this->getResponseData($response);

        return (object) array_merge($data, ['status' => true]);
    }

    /**
     * @param mixed $response
     *
     * @return mixed|object
     */
    protected function getResponseData($response)
    {
        if ($response->getStatusCode() != 200) {
            return ['status' => false, 'message' => 'Request Error. '.$response->getReasonPhrase()];
        }

        $data = json_decode((string) $response->getBody(), true);

        if ($data['status'] != 'success') {
            return ['status' => false, 'message' => 'Something went wrong.', 'data' => $data];
        }

        return $data;
    }
}
