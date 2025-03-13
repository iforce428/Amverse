<?php

class FinancialLLM
{
    private $analyticsData;

    /**
     * Constructor: Loads the analytics data from a serialized file.
     */
    public function __construct($dataFile)
    {
        if (!file_exists($dataFile)) {
            throw new Exception("Data file not found: $dataFile");
        }
        $this->analyticsData = unserialize(file_get_contents($dataFile));
        if (!is_array($this->analyticsData)) {
            throw new Exception("Invalid data format in the file.");
        }
    }

    public function processQuery($query)
    {
        if (isset($this->analyticsData['responses'][$query])) {
            return $this->analyticsData['responses'][$query];
        }
        return "Query not recognized. Please refine your input.";
    }

    /**
     * Provides a list of supported queries.
     */
    public function getSupportedQueries()
    {
        return array_keys($this->analyticsData['responses']);
    }
}

// Define data file path
$dataFile = __DIR__ . '/FinancialLLMData.dat';

try {
    // Instantiate the FinancialLLM class
    $financialLLM = new FinancialLLM($dataFile);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Fetch the query from POST data
        $query = $_POST['query'] ?? '';
        $response = $financialLLM->processQuery($query);

        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'query' => $query,
            'response' => $response
        ]);
    } else {
        // Display supported queries in HTML
        $queries = $financialLLM->getSupportedQueries();
        echo "<h1>Financial Analytics LLM Simulation</h1>";
        echo "<p>Supported Queries:</p><ul>";
        foreach ($queries as $query) {
            echo "<li>$query</li>";
        }
        echo "</ul>";
        echo "<form method='POST'>";
        echo "Query: <input type='text' name='query' required>";
        echo "<button type='submit'>Submit</button>";
        echo "</form>";
    }
} catch (Exception $e) {
    // Handle errors
    echo "<h1>Error</h1>";
    echo "<p>{$e->getMessage()}</p>";
}
?>
