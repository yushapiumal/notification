<?php
function getTodayFlights()
{
    date_default_timezone_set('Asia/Colombo');
    $api = "https://cinnamon.go.digitable.io/avidi/api/avidi/v1/radar?";
    $date = date('Y-m-d');
    $url = $api . "date=" . $date . "&type=todayFlight";

    $headers = [
        "Accept: application/json",
        "Content-Type: application/x-www-form-urlencoded",
        "Build: 11",
        "Header-from: web"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $errorMsg = 'Curl error: ' . curl_error($ch);
        file_put_contents('error_log.log', $errorMsg . PHP_EOL, FILE_APPEND);
        return json_encode(['error' => $errorMsg]);
    }

    curl_close($ch);
    $jsonData = json_decode($response, true);

    $filteredFlights = [];

    if (isset($jsonData['data']) && is_array($jsonData['data']) && count($jsonData['data']) > 0) {
        // Filter flights
        foreach ($jsonData['data'] as $flight) {
            if (
                (isset($flight['departure']) && $flight['departure'] === 'NUF') ||
                (isset($flight['destination']) && $flight['destination'] === 'NUF') ||
                (isset($flight['route']) && strpos($flight['route'], 'NUF') !== false)
            ) {
                $filteredFlights[] = $flight;
            }
        }
    }

    if (count($filteredFlights) > 0) {
        return json_encode(['flights' => $filteredFlights]);
    }

    return json_encode(['flights' => [], 'message' => 'No flights available for today with NUF.']);
}

header('Content-Type: application/json');
echo getTodayFlights();
