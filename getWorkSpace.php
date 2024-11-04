<?php
require 'vendor/autoload.php';
require_once 'vendor/easysql/dist/easysql.php';

use GuzzleHttp\Client;

// Create a new Guzzle HTTP client
$httpClient = new Client();

// Create a new EasySQL MySql client
$conn = new easysql();

// provide credentials
$conn->set_credentials_via_json_file("vendor/easysql/dist/credentials.json");

try {
    $result = $conn->open_connection();
    $conn->pretty_print($result);
} catch (Exception $e) {
    $conn->pretty_print($e->getMessage());
}

$headers = ['headers' => ['X-Api-Key' => 'Y2EzMmNjYTItYmNlMy00MTZiLWE5OTMtNjE5ZmVjNGM5MzRj']];
  
// Send a GET request to fetch workspaces
$workspaces = array();
$apiUrl = 'https://api.clockify.me/api/v1/workspaces';    
$workspaces = getClokifyAPI_data($httpClient, $apiUrl, $headers);
if ($workspaces != NULL) {
    $workspaceId = $workspaces[0]['id'];

    $utilisateurs = array();
    $apiUrl = 'https://api.clockify.me/api/v1/workspaces/' . $workspaceId . '/users';
    $utilisateurs = getClokifyAPI_data($httpClient, $apiUrl, $headers);
    if ($utilisateurs != NULL) {
        // Sync users with the MySQL database
        foreach ($utilisateurs as $utilisateur) {
            $Name = $utilisateur['name'];
            $userId = $utilisateur['id'];
            // Check if the utilisateur already exists in the database
            $result = $conn->select("Utilisateur", ["id", "nom"], ["id" => $utilisateur['id']]);
            // Convert JSON string to PHP array
            $result = json_decode($result, true); // Pass true to get an associative array
            // display the array
            print_r($result);
            
            if ($result['status'] == 'success' && empty ($result['data'])) {
                // Insert new utilisateur
                $result = $conn->insert("Utilisateur", ["id" => $utilisateur['id'], "nom" => $utilisateur['name'],
                    "courriel" => $utilisateur['email'], "workspace_id" => $workspaceId]);
                // Convert JSON string to PHP array
                $result = json_decode($result, true); // Pass true to get an associative array
                // display the array
                print_r($result);
                echo "Inserted new user: $Name\n";
            } else {
                // Optionally, you could update the existing utilisateur if necessary
                echo "User already exists: $Name\n";
            }
        }
    } else {
        echo "No users fetched\n";
    }

    $clients = array();
    $apiUrl = 'https://api.clockify.me/api/v1/workspaces/' . $workspaceId . '/clients';
    $clients = getClokifyAPI_data($httpClient, $apiUrl, $headers);
    if ($clients != NULL) {
        // Sync clients with the MySQL database
        foreach ($clients as $client) {
            $Name = $client['name'];
            // Check if the client already exists in the database
            $result = $conn->select("Client", ["id", "nom"], ["id" => $client['id']]);
            // Convert JSON string to PHP array
            $result = json_decode($result, true); // Pass true to get an associative array
            // display the array
            print_r($result);
            
            if ($result['status'] == 'success' && empty ($result['data'])) {
                // Insert new client
                $result = $conn->insert("Client", ["id" => $client['id'], "nom" => $client['name'],
                    "courriel" => $client['email'], "adresse" => $client['address'], "note" => $client['note']]);
                // Convert JSON string to PHP array
                $result = json_decode($result, true); // Pass true to get an associative array
                // display the array
                print_r($result);
                echo "Inserted new client: $Name\n";
            } else {
                // Optionally, you could update the existing client if necessary
                echo "Client already exists: $Name\n";
            }
        }
    } else {
        echo "No clients fetched\n";
    }

    $projets = array();
    $apiUrl = 'https://api.clockify.me/api/v1/workspaces/' . $workspaceId . '/projects';
    $projets = getClokifyAPI_data($httpClient, $apiUrl, $headers);
    if ($projets != NULL) {
        // Sync projets with the MySQL database
        foreach ($projets as $projet) {
            $Name = $projet['name'];
            $projetId = $projet['id'];
            // Check if the project already exists in the database
            $result = $conn->select("Projet", ["id", "nom"], ["id" => $projet['id']]);
            // Convert JSON string to PHP array
            $result = json_decode($result, true); // Pass true to get an associative array
            // display the array
            print_r($result);
            
            if ($result['status'] == 'success' && empty ($result['data'])) {
                // Insert new project
                $result = $conn->insert("Projet", ["id" => $projet['id'], "nom" => $projet['name'],
                    "client_id" => $projet['clientId'], ]);
                // Convert JSON string to PHP array
                $result = json_decode($result, true); // Pass true to get an associative array
                // display the array
                print_r($result);
                echo "Inserted new project: $Name\n";
            } else {
                // Optionally, you could update the existing client if necessary
                echo "Project already exists: $Name\n";
            }

            $taches = array();
            $apiUrl = 'https://api.clockify.me/api/v1/workspaces/' . $workspaceId . '/projects/' .
                        $projetId . '/tasks';
            $taches = getClokifyAPI_data($httpClient, $apiUrl, $headers);
            if ($taches != NULL) {
                // Sync tasks with the MySQL database
                foreach ($taches as $tache) {
                    $Name = $tache['name'];
                    // Check if the task already exists in the database
                    $result = $conn->select("Tache", ["id", "nom"], ["id" => $tache['id']]);
                    // Convert JSON string to PHP array
                    $result = json_decode($result, true); // Pass true to get an associative array
                    // display the array
                    print_r($result);
                    
                    if ($result['status'] == 'success' && empty ($result['data'])) {
                        // Insert new task
                        $result = $conn->insert("Tache", ["id" => $tache['id'], "nom" => $tache['name'],
                            "client_id" => $projet['clientId'], "projet_id" => $tache['projectId'], ]);
                        // Convert JSON string to PHP array
                        $result = json_decode($result, true); // Pass true to get an associative array
                        // display the array
                        print_r($result);
                        echo "Inserted new task: $Name\n";
                    } else {
                        // Optionally, you could update the existing task if necessary
                        echo "Task already exists: $Name\n";
                    }
                }
            } else {
                echo "No tasks fetched\n";
            }

        }
    } else {
        echo "No projetcts fetched\n";
    }
    
    $queryParams = ['query' => ['start' => '2022-11-20T00:00:00.000Z',
                                'end' => '2023-01-01T00:00:00.000Z',
                                'page-size' => 5
                               ],
                    'headers' => ['X-Api-Key' => 'Y2EzMmNjYTItYmNlMy00MTZiLWE5OTMtNjE5ZmVjNGM5MzRj',
                                  'Content-Type' => 'application/json']];

    $intervales = array();

    $entrees = array();
    $apiUrl = 'https://api.clockify.me/api/v1/workspaces/' . $workspaceId . '/user/' .
                    $userId . '/time-entries';
    $entrees = getClokifyAPI_data($httpClient, $apiUrl, $headers, $queryParams);
    if ($entrees != NULL) {
        // Sync time-entries with the MySQL database
        foreach ($entrees as $entree) {
            $Name = $entree['description'];
            // Check if the time-entries already exists in the database
            $result = $conn->select("Entree", ["id"], ["id" => $entree['id']]);
            // Convert JSON string to PHP array
            $result = json_decode($result, true); // Pass true to get an associative array
        }
        
    } else {
        echo "No entries fetched\n";
    }    

/*    $temps = array();
    $apiUrl = 'https://api.clockify.me/api/v1/workspaces/' . $workspaceId . '/user/' . $projects;
    $projets = getClokifyAPI_data($httpClient, $apiUrl, $headers);
    if ($projets != NULL) {
*/    
} else {
    echo "No workspaces fetched\n";
}   

use GuzzleHttp\Psr7\Request;

function getClokifyAPI_data($client, $apiUrl, $headers, $queryParams=NULL): array {
    $array = array();
    try {
        // Send a GET request to the specified URL
        if ($queryParams != NULL) {
            $response = $client->get($apiUrl, $queryParams);
        } else {
            $response = $client->get($apiUrl, $headers);
        }
   
        // Get the status code of the response
        $statusCode = $response->getStatusCode();
        // Print the status code
        echo "Status code: " . $statusCode . "\n";
        if ($statusCode==200) {
            // Get the body of the response
            $body = $response->getBody();
            // Convert JSON string to PHP array
            $array = json_decode($body, true); // Pass true to get an associative array
            // Print the resulting array
            print_r($array);
        } else {
            echo "Failed to fetch records. Status code: $statusCode\n";
        }
  
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Handle the exception
        echo "HTTP Request failed\n";
        if ($e->hasResponse()) {
            echo "Error response: " . $e->getResponse()->getBody() . "\n";
        }
    }
    return $array; 
}
