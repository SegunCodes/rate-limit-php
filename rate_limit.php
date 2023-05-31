<?php
require 'vendor/autoload.php';

use Predis\Client;

// IP-based rate limiting using Redis
function rateLimit($maxRequests, $timeWindow) {
    // Create a Redis connection
    $redis = new Client();

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

// Usage example: Rate limit to 100 requests per minute
rateLimit(100, 60);
?>
