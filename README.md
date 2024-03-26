# Mantle API Library for PHP

A very basic PHP library for accessing the Mantle API. Never give your Mantle API key to anyone, and never use it on the frontend or store it in source control!

### Example usage

```php
<?php

require_once 'MantleClient.php';

try {
    // Initialize the MantleClient with necessary parameters
    $mantleClient = new MantleClient(
        $appId = 'your_mantle_app_id',
        $apiKey = 'your_mantle_api_key', // Use null if calling from the client-side
        $customerApiToken = null, // Use the customer's API token if calling from the client-side
        $apiUrl = 'https://appapi.heymantle.com/v1'
    );

    // Example usage: Identify a customer
    $identifyResponse = $mantleClient->identify(
        $platformId = 'customer_platform_id',
        $myshopifyDomain = 'customer_shop.myshopify.com',
        $platform = 'shopify',
        $accessToken = 'platform_access_token',
        $name = 'Customer Name',
        $email = 'customer@example.com'
    );
    echo "Customer identified; API Token: " . $identifyResponse['apiToken'] . PHP_EOL;

    // Example usage: Get the customer associated with the current API token
    $customer = $mantleClient->getCustomer();
    echo "Current Customer: " . print_r($customer, true) . PHP_EOL;

    // Example usage: Subscribe a customer to a plan
    $subscribeResponse = $mantleClient->subscribe(
        $planId = 'plan_identifier',
        $planIds = null,
        $discountId = 'discount_identifier',
        $returnUrl = 'https://yourapp.com/return_url_after_subscription',
        $billingProvider = 'stripe' // or any other billing provider you support
    );
    echo "Subscription created: " . print_r($subscribeResponse, true) . PHP_EOL;

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . PHP_EOL;
}
```
