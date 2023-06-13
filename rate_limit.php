<?php
require 'vendor/autoload.php';

use Predis\Client;

// Create a Redis connection
$redis = new Client();

// IP-based rate limiting using Redis
function rateLimit($maxRequests, $timeWindow) {
    global $redis;

    // Get the client's IP address
    $clientIP = $_SERVER['REMOTE_ADDR'];

    // Set a unique key for the client's IP address
    $key = 'rate_limit_' . $clientIP;

    // Get the current request count for the client's IP
    $requestCount = $redis->get($key);

    // If the request count doesn't exist or has expired, initialize it
    if ($requestCount === null) {
        $requestCount = 1;
        $redis->set($key, $requestCount, 'ex', $timeWindow);
    } else {
        // If the request count exists, increment it
        $requestCount++;
        $redis->incr($key);
    }

    // Check if the request count exceeds the maximum allowed requests
    if ($requestCount > $maxRequests) {
        // Perform actions for rate limit exceeded, such as returning an error response or delaying the request
        http_response_code(429); // 429 Too Many Requests
        echo "Rate limit exceeded. Please try again later.";
        exit;
    }

    // Proceed with normal execution if rate limit is not exceeded
    echo "Request processed successfully.";
}

// Endpoint-based rate limiting
function endpointRateLimit($maxRequests, $timeWindow)
{
    global $redis;

    // Get the endpoint or route path
    $endpoint = $_SERVER['REQUEST_URI'];

    // Create a unique key for the endpoint
    $key = 'rate_limit_' . $endpoint;

    // Get the current request count for the endpoint
    $requestCount = (int)$redis->get($key);

    // If the request count doesn't exist or has expired, initialize it
    if ($requestCount === 0) {
        $redis->set($key, 1, 'ex', $timeWindow * 60); // Convert minutes to seconds
        $redis->expire($key, $timeWindow);
    } else {
        // If the request count exists, increment it
        $redis->incr($key);
    }

    // Check if the request count exceeds the maximum allowed requests
    if ($requestCount >= $maxRequests) {
        http_response_code(429); // 429 Too Many Requests
        echo 'Endpoint limit exceeded. Please try again later.';
        exit;
    }
}

// Global rate limiting using Redis
function globalRateLimit($maxRequests, $timeWindow) {
    global $redis;

    // Set a unique key for the global rate limiting
    $key = 'rate_limit_global';

    // Get the current request count for the global rate limiting
    $requestCount = $redis->get($key);

    // If the request count doesn't exist or has expired, initialize it
    if ($requestCount === null) {
        $requestCount = 1;
        $redis->set($key, $requestCount, 'ex', $timeWindow);
    } else {
        // If the request count exists, increment it
        $requestCount++;
        $redis->incr($key);
    }

    // Check if the request count exceeds the maximum allowed requests
    if ($requestCount > $maxRequests) {
        // Perform actions for rate limit exceeded, such as returning an error response or delaying the request
        http_response_code(429); // 429 Too Many Requests
        echo "Rate limit exceeded. Please try again later.";
        exit;
    }

    // Proceed with normal execution if rate limit is not exceeded
    echo "Request processed successfully.";
}

// Usage example: Global rate limit to 1000 requests per hour
globalRateLimit(1000, 3600);

// Usage example: Rate limit to 100 requests per minute
rateLimit(100, 60);

// Usage example with endpoint "/api/endpoint1"
endpointRateLimit(10, 60);
// Perform actions for the endpoint if rate limit is not exceeded
echo 'Endpoint 1 response';
?>
