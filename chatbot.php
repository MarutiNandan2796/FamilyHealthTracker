<?php
header('Content-Type: application/json');

const GEMINI_API_KEY = "AIzaSyDsucGMRRmBAsr1eXIu2Lz-WKMM6IGN1QI";
const GEMINI_API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent?key=" . GEMINI_API_KEY;

function getChatbotResponse($message) {
    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => "You are a helpful health assistant. Please provide accurate and helpful information about health-related questions. Here's the user's question: " . $message
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "topK" => 40,
            "topP" => 0.95,
            "maxOutputTokens" => 1024,
        ]
    ];

    $ch = curl_init(GEMINI_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "I apologize, but I'm having trouble processing your request right now. Please try again later.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    
    if (!empty($message)) {
        $response = getChatbotResponse($message);
        echo json_encode(['response' => $response]);
    } else {
        echo json_encode(['error' => 'No message provided']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?> 