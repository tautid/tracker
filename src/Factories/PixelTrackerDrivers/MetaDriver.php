<?php

namespace TautId\Tracker\Factories\PixelTrackerDrivers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use TautId\Tracker\Services\PixelSummaryService;
use TautId\Tracker\Services\PixelTrackerService;
use TautId\Tracker\Abstracts\PixelTrackerAbstract;
use TautId\Tracker\Data\PixelEvent\PixelEventData;
use TautId\Tracker\Enums\PixelConversionStatusEnums;
use TautId\Tracker\Data\PixelSummary\PixelInformationData;
use TautId\Tracker\Data\PixelSummary\CreatePixelSummaryData;

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
        if (!data_get($this->data, 'currency')) {
            throw new \InvalidArgumentException('currency is required for purchase event');
        }

        if (!data_get($this->data, 'value')) {
            throw new \InvalidArgumentException('value is required for purchase event');
        }

        if (! is_float(data_get($this->data, 'value'))) {
            throw new \InvalidArgumentException('value type must be float');
        }
    }

    private function validateStartTrialData(): void
    {
        if (!data_get($this->data, 'currency')) {
            throw new \InvalidArgumentException('currency is required for start trial event');
        }

        if (!data_get($this->data, 'value')) {
            throw new \InvalidArgumentException('value is required for start trial event');
        }

        if (!data_get($this->data, 'predicted_ltv')) {
            throw new \InvalidArgumentException('predicted_ltv is required for start trial event');
        }

        if (! is_float(data_get($this->data, 'value'))) {
            throw new \InvalidArgumentException('value type must be float');
        }

        if (! is_float(data_get($this->data, 'predicted_ltv'))) {
            throw new \InvalidArgumentException('predicted_ltv type must be float');
        }
    }

    private function validateSubscribeData(): void
    {
        if (!data_get($this->data, 'currency')) {
            throw new \InvalidArgumentException('currency is required for subscribe event');
        }

        if (!data_get($this->data, 'value')) {
            throw new \InvalidArgumentException('value is required for subscribe event');
        }

        if (!data_get($this->data, 'predicted_ltv')) {
            throw new \InvalidArgumentException('predicted_ltv is required for subscribe event');
        }

        if (! is_float(data_get($this->data, 'value'))) {
            throw new \InvalidArgumentException('value type must be float');
        }

        if (! is_float(data_get($this->data, 'predicted_ltv'))) {
            throw new \InvalidArgumentException('predicted_ltv type must be float');
        }
    }

    public function fetch(): void
    {
        if ($this->conversion === null) {
            throw new \InvalidArgumentException('Conversion must be set before fetching.');
        }

        $this->validateData();

        $maxRetries = 3;
        $retryDelay = 100; // ms

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $payload = [
                    'data' => [
                        [
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
                        ]
                    ],
                    'access_token' => $this->pixel->token,
                ];

                $response = Http::acceptJson()
                    ->timeout(30)
                    ->asForm()
                    ->post($this->baseUrl(), $payload);

                if ($response->successful()) {
                    app(PixelTrackerService::class)->changeStatusToSuccess($this->conversion->id);
                    return;
                }

                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? 'Unknown API error';

                throw new \Exception(
                    "Meta API request failed (HTTP {$response->status()}): {$errorMessage}",
                    $response->status()
                );

            } catch (\Exception $e) {
                logger()->warning("Meta pixel tracking attempt {$attempt} failed", [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                    'pixel_id' => $this->pixel->pixel_id,
                    'event' => $this->pixel->event,
                ]);

                if ($attempt === $maxRetries) {
                    app(PixelTrackerService::class)->changeStatusToFailed($this->conversion->id);
                    Log::error("Meta pixel tracking failed", [
                        'error' => $e->getMessage(),
                        'event' => $this->pixel->event
                    ]);

                    break;
                }

                // Wait before retrying (exponential backoff)
                $waitTime = $retryDelay * pow(2, $attempt - 1);
                usleep($waitTime * 1000);
            }
        }
    }

    public function createSummary(?Carbon $date): void
    {
        if ($this->pixel === null) {
            throw new \InvalidArgumentException('Pixel must be set before fetching.');
        }

        $conversion_groups = app(PixelTrackerService::class)
                    ->getUnsavedConversionByPixelEvent($this->pixel->id, $date ?? now());

        foreach($conversion_groups->items() as $date => $group)
        {
            try{
                $meta = match($this->pixel->event){
                    'Purchase' => $group->sum('data.value'),
                    'StartTrial' => $group->sum('data.value'),
                    'Subscribe' => $group->sum('data.value'),
                    default => null
                };

                $data = CreatePixelSummaryData::from([
                    'pixel' => PixelInformationData::from([
                        'id' => $this->pixel->id,
                        'name' => $this->pixel->name
                    ]),
                    'fetch_success' => $group->where('status',PixelConversionStatusEnums::Success->value)->count(),
                    'fetch_failed' => $group->where('status',PixelConversionStatusEnums::Failed->value)->count(),
                    'fetch_duplicated' => $group->where('status',PixelConversionStatusEnums::Duplicate->value)->count(),
                    'date' => Carbon::parse($date),
                    'meta' => $meta
                ]);

                $ids = collect($group)->pluck('id')->toArray();

                app(PixelSummaryService::class)->createPixelSummary($data);
                app(PixelTrackerService::class)->saveConversions($ids);
            }catch(\Exception $e)
            {
                //
            }
        }
    }
}
