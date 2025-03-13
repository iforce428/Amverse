<?php

// Step 1: Define the OpenAI API key
$apiKey = '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'; 

// Step 2: Fetch precomputed embeddings from the database
function getPrecomputedEmbeddings() {
    $conn = new mysqli('localhost', 'root', '', 'chat_bot_db');
    if ($conn->connect_error) {
        die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
    }

    // Fetch the embedding for id = 60 for testing
    $query = "SELECT id, response, embedding FROM response_list limit 5";
    $result = $conn->query($query);

    if (!$result) {
        $conn->close();
        die(json_encode(["error" => "Failed to fetch data: " . $conn->error]));
    }

    $baseKnowledge = [];
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['embedding'])) {
            $embedding = json_decode($row['embedding'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $baseKnowledge[] = [
                    'id' => $row['id'],
                    'response' => $row['response'],
                    'embedding' => $embedding,
                ];
            }
        } else {
            $newEmbedding = createEmbedding($row['response']);
            storeEmbedding($row['id'], $newEmbedding);
            $baseKnowledge[] = [
                'id' => $row['id'],
                'response' => $row['response'],
                'embedding' => $newEmbedding,
            ];
        }
    }

    $conn->close();
    if (empty($baseKnowledge)) {
        die(json_encode(["error" => "No data found for response ID = 62."]));
    }

    return $baseKnowledge;
}

// Step 3: Fetch new embeddings
function createEmbedding($text) {
    global $apiKey;

    $data = [
        'model' => 'text-embedding-ada-002',
        'input' => $text,
    ];

    $ch = curl_init('https://api.openai.com/v1/embeddings');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die(json_encode(["error" => "Curl error: " . curl_error($ch)]));
    }
    curl_close($ch);

    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($responseData['data'][0]['embedding'])) {
        die(json_encode(["error" => "Failed to parse OpenAI embedding response."]));
    }

    return $responseData['data'][0]['embedding'];
}

// Step 4: Store embeddings in the database
function storeEmbedding($responseId, $embedding) {
    $conn = new mysqli('localhost', 'root', '', 'chat_bot_db');
    if ($conn->connect_error) {
        die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
    }

    $embeddingJson = json_encode($embedding);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die(json_encode(["error" => "Failed to encode embedding as JSON."]));
    }

    $stmt = $conn->prepare("UPDATE response_list SET embedding = ? WHERE id = ?");
    $stmt->bind_param("si", $embeddingJson, $responseId);

    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        die(json_encode(["error" => "Failed to update embedding: " . $stmt->error]));
    }

    $stmt->close();
    $conn->close();
}

// Step 5: Calculate cosine similarity
function cosineSimilarity($vecA, $vecB) {
    $dotProduct = 0;
    $magnitudeA = 0;
    $magnitudeB = 0;

    for ($i = 0; $i < count($vecA); $i++) {
        $dotProduct += $vecA[$i] * $vecB[$i];
        $magnitudeA += pow($vecA[$i], 2);
        $magnitudeB += pow($vecB[$i], 2);
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    if ($magnitudeA == 0 || $magnitudeB == 0) {
        return 0;
    }

    return $dotProduct / ($magnitudeA * $magnitudeB);
}

// Step 6: Retrieve most relevant responses
function getMostRelevantResponses($userQuery, $baseKnowledge) {
    $queryEmbedding = createEmbedding($userQuery);

    $similarities = [];
    foreach ($baseKnowledge as $entry) {
        $similarity = cosineSimilarity($queryEmbedding, $entry['embedding']);
        $similarities[] = [
            'id' => $entry['id'],
            'response' => $entry['response'],
            'similarity' => $similarity,
        ];
    }

    usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

    return $similarities[0];
}

// Step 7: Call OpenAI API
function callOpenAI($prompt) {
    global $apiKey;

    $data = [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'Provide a helpful response to the user query.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'max_tokens' => 500,
        'temperature' => 0.7,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die(json_encode(["error" => "Curl error: " . curl_error($ch)]));
    }
    curl_close($ch);

    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($responseData['choices'][0]['message']['content'])) {
        return "Error: Could not retrieve response.";
    }

    return $responseData['choices'][0]['message']['content'];
}

// Step 8: Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userQuery = $_POST['user_query'] ?? '';

    if (empty($userQuery)) {
        echo json_encode(["error" => "User query is required."]);
        exit;
    }

    $baseKnowledge = getPrecomputedEmbeddings();
    $relevantResponse = getMostRelevantResponses($userQuery, $baseKnowledge);

    $prompt = "User query: " . $userQuery . "\nRelevant Response: " . $relevantResponse['response'];

    $openAIResponse = callOpenAI($prompt);

    echo json_encode(["content" => $openAIResponse]);
} else {
    echo json_encode(["error" => "Please send a POST request."]);
}
?>