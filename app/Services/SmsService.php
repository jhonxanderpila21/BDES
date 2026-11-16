<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    /**
     * Send an SMS message to a Philippine mobile number via the configured SMS gateway (Semaphore or Vonage).
     * Never logs the message content or OTP value to application logs.
     */
    public function send(string $phoneNumber, string $message): bool
    {
        $provider = strtolower((string) config('services.sms.provider', 'semaphore'));

        return match ($provider) {
            'vonage'    => $this->sendViaVonage($phoneNumber, $message),
            'semaphore' => $this->sendViaSemaphore($phoneNumber, $message),
            default     => $this->sendViaSemaphore($phoneNumber, $message),
        };
    }

    /**
     * Send SMS using Semaphore API (https://api.semaphore.co/api/v4/messages).
     */
    protected function sendViaSemaphore(string $phoneNumber, string $message): bool
    {
        $apiKey     = config('services.semaphore.api_key');
        $senderName = config('services.semaphore.sender_name', 'SEMAPHORE');
        $endpoint   = config('services.semaphore.endpoint', 'https://api.semaphore.co/api/v4/messages');

        if (empty($apiKey)) {
            Log::warning('SMS gateway (Semaphore) API key is not configured in environment.');
            return false;
        }

        try {
            $payload = [
                'apikey'  => $apiKey,
                'number'  => $phoneNumber,
                'message' => $message,
            ];

            if (! empty($senderName)) {
                $payload['sendername'] = $senderName;
            }

            $response = Http::timeout(10)
                ->asForm()
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Semaphore SMS gateway returned a non-2xx status.', [
                'status' => $response->status(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Semaphore SMS dispatch failed due to a connection exception.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send SMS using Vonage (Nexmo) REST API (https://rest.nexmo.com/sms/json).
     */
    protected function sendViaVonage(string $phoneNumber, string $message): bool
    {
        $apiKey    = config('services.vonage.key');
        $apiSecret = config('services.vonage.secret');
        $from      = config('services.vonage.from', 'BDES');
        $endpoint  = config('services.vonage.endpoint', 'https://rest.nexmo.com/sms/json');

        if (empty($apiKey) || empty($apiSecret)) {
            Log::warning('SMS gateway (Vonage) credentials are not configured in environment.');
            return false;
        }

        try {
            $to = ltrim($phoneNumber, '+');

            $response = Http::timeout(10)
                ->asForm()
                ->post($endpoint, [
                    'api_key'    => $apiKey,
                    'api_secret' => $apiSecret,
                    'to'         => $to,
                    'from'       => $from,
                    'text'       => $message,
                ]);

            if ($response->successful()) {
                $data   = $response->json();
                $status = $data['messages'][0]['status'] ?? '1';

                if ((string) $status === '0') {
                    return true;
                }

                Log::error('Vonage SMS gateway returned an error status code.', [
                    'vonage_status' => $status,
                ]);

                return false;
            }

            Log::error('Vonage SMS gateway returned a non-2xx HTTP status.', [
                'status' => $response->status(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Vonage SMS dispatch failed due to a connection exception.', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

