<?php

namespace TautId\Tracker\Factories\PixelTrackerDrivers;

use Illuminate\Support\Facades\Http;
use TautId\Tracker\Abstracts\PixelTrackerAbstract;

class MetaDriver extends PixelTrackerAbstract
{
    private string $raw_url = 'https://graph.facebook.com/v24.0/{PIXEL_ID}/events';

    private function baseUrl(): string
    {
        $base_url = $this->raw_url;

        $base_url = str_replace('{PIXEL_ID}', $this->pixel->pixel_id, $base_url);

        return $base_url;
    }

    public function getEvents(): array
    {
        $events = [
            'AddPaymentInfo',
            'AddToCart',
            'AddToWishlist',
            'CompleteRegistration',
            'Contact',
            'CustomizeProduct',
            'Donate',
            'FindLocation',
            'InitiateCheckout',
            'Lead',
            'Purchase',
            'Schedule',
            'Search',
            'StartTrial',
            'SubmitApplication',
            'Subscribe',
            'ViewContent',
        ];

        return array_combine($events, $events);
    }

    public function validateData(): void
    {
        $this->validateRequiredData();

        switch ($this->pixel->event) {
            case 'Purchase':
                $this->validatePurchaseData();
                break;
            case 'StartTrial':
                $this->validateStartTrialData();
                break;
            case 'Subscribe':
                $this->validateSubscribeData();
                break;
            default:
                break;
        }
    }

    private function validatePurchaseData(): void
    {
        if (data_get($this->data, 'currency')) {
            throw new \InvalidArgumentException('currency is required for puchase event');
        }

        if (data_get($this->data, 'value')) {
            throw new \InvalidArgumentException('value is required for purchase event');
        }

        if (! is_float($this->data, 'value')) {
            throw new \InvalidArgumentException('value type must be float');
        }
    }

    private function validateStartTrialData(): void
    {
        if (data_get($this->data, 'currency')) {
            throw new \InvalidArgumentException('currency is required for start trial event');
        }

        if (data_get($this->data, 'value')) {
            throw new \InvalidArgumentException('value is required for start trial event');
        }

        if (data_get($this->data, 'predicted_ltv')) {
            throw new \InvalidArgumentException('predicted_ltv is required for start trial event');
        }

        if (! is_float($this->data, 'value')) {
            throw new \InvalidArgumentException('value type must be float');
        }

        if (! is_float($this->data, 'predicted_ltv')) {
            throw new \InvalidArgumentException('predicted_ltv type must be float');
        }
    }

    private function validateSubscribeData(): void
    {
        if (data_get($this->data, 'currency')) {
            throw new \InvalidArgumentException('currency is required for subscribe event');
        }

        if (data_get($this->data, 'value')) {
            throw new \InvalidArgumentException('value is required for subscribe event');
        }

        if (data_get($this->data, 'predicted_ltv')) {
            throw new \InvalidArgumentException('predicted_ltv is required for subscribe event');
        }

        if (! is_float($this->data, 'value')) {
            throw new \InvalidArgumentException('value type must be float');
        }

        if (! is_float($this->data, 'predicted_ltv')) {
            throw new \InvalidArgumentException('predicted_ltv type must be float');
        }
    }

    public function fetch(): void
    {
        $this->validateData();

        try {
            $payload = [
                'data' => [
                    'event_name' => $this->pixel->event,
                    'event_time' => $this->conversion->created_at->timestamp,
                    'user_data' => [
                        'fbc' => data_get($this->conversion->meta, 'fbc'),
                        'fbp' => data_get($this->conversion->meta, 'fbp'),
                        'client_ip_address' => $this->conversion->client_ip,
                        'client_user_agent' => $this->conversion->user_agent,
                    ],
                    'custom_data' => $this->data,
                    'event_source_url' => $this->conversion->source_url,
                    'action_source' => 'website',
                ],
                'access_token' => $this->pixel->token,
            ];

            $response = Http::acceptJson()
                ->asForm()
                ->post($this->baseUrl(), $payload);

            if (! $response->successful()) {
                throw new \Exception('Fetch Failed');
            }
        } catch (\Exception $e) {

        }
    }
}
