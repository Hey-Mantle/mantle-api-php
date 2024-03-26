<?php

class MantleClient {
    private $appId;
    private $apiKey;
    private $customerApiToken;
    private $apiUrl;

    public function __construct($appId, $apiKey = null, $customerApiToken = null, $apiUrl = 'https://appapi.heymantle.com/v1') {
        if (!$appId) {
            throw new Exception('[MantleClient] appId is required');
        }
        if (!empty($_SERVER['HTTP_USER_AGENT']) && $apiKey) {
            throw new Exception('[MantleClient] apiKey should never be used in the browser');
        }
        if (!$apiKey && !$customerApiToken) {
            throw new Exception('[MantleClient] One of apiKey or customerApiToken is required');
        }

        $this->appId = $appId;
        $this->apiKey = $apiKey;
        $this->customerApiToken = $customerApiToken;
        $this->apiUrl = $apiUrl;
    }

    private function mantleRequest($path, $method = 'GET', $body = null) {
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($path, '/');
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            "X-Mantle-App-Id: {$this->appId}"
        ];
        if ($this->apiKey) {
            $headers[] = "X-Mantle-App-Api-Key: {$this->apiKey}";
        }
        if ($this->customerApiToken) {
            $headers[] = "X-Mantle-Customer-Api-Token: {$this->customerApiToken}";
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $errorMessage = curl_error($curl);
            curl_close($curl);
            throw new Exception("[mantleRequest] {$path} error: {$errorMessage}");
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            curl_close($curl);
            throw new Exception("[mantleRequest] {$path} error: HTTP status code {$httpCode}");
        }

        curl_close($curl);
        return json_decode($response, true);
    }

    public function identify($platformId, $myshopifyDomain, $platform = 'shopify', $accessToken, $name, $email, $customFields = null) {
        $body = [
            'platformId' => $platformId,
            'myshopifyDomain' => $myshopifyDomain,
            'platform' => $platform,
            'accessToken' => $accessToken,
            'name' => $name,
            'email' => $email,
            'customFields' => $customFields
        ];
        return $this->mantleRequest('identify', 'POST', $body);
    }

    public function getCustomer() {
        $response = $this->mantleRequest('customer');
        return $response['customer'] ?? null;
    }

    public function subscribe($planId, $planIds, $discountId, $returnUrl, $billingProvider = null) {
        $body = [
            'planId' => $planId,
            'planIds' => $planIds,
            'discountId' => $discountId,
            'returnUrl' => $returnUrl,
            'billingProvider' => $billingProvider
        ];
        return $this->mantleRequest('subscriptions', 'POST', $body);
    }

    public function cancelSubscription($cancelReason = null) {
        $body = $cancelReason ? ['cancelReason' => $cancelReason] : [];
        return $this->mantleRequest('subscriptions', 'DELETE', $body);
    }

    public function updateSubscription($id, $cappedAmount) {
        $body = [
            'id' => $id,
            'cappedAmount' => $cappedAmount
        ];
        return $this->mantleRequest('subscriptions', 'PUT', $body);
    }

    public function sendUsageEvent($eventId, $eventName, $customerId, $properties = []) {
        $body = [
            'eventId' => $eventId,
            'eventName' => $eventName,
            'customerId' => $customerId,
            'properties' => $properties
        ];
        return $this->mantleRequest('usage_events', 'POST', $body);
    }

    public function sendUsageEvents($events) {
        $body = [
            'events' => $events
        ];
        return $this->mantleRequest('usage_events', 'POST', $body);
    }
}
