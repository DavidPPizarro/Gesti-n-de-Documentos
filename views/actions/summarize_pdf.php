<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Acceso denegado');
}

require '../../vendor/autoload.php';
use GuzzleHttp\Client;

$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "documentos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function getFilePath($filename) {
    $locations = [
        "C:/xampp/htdocs/guardarDocs/uploads/location1/",
        "C:/xampp/htdocs/guardarDocs/uploads/location2/",
        "C:/xampp/htdocs/guardarDocs/uploads/location3/"
    ];
    
    foreach ($locations as $location) {
        $path = $location . $filename;
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return false;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    exit('ID de documento no válido');
}

$sql = "SELECT titulo, encryption_key, iv FROM docs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $titulo = $row['titulo'];
    $encryption_key = $row['encryption_key'];
    $iv = $row['iv'];
    
    $file_path = getFilePath($titulo);

    if ($file_path !== false) {
        $encrypted_content = file_get_contents($file_path);
        $decrypted_content = openssl_decrypt($encrypted_content, 'aes-256-cbc', $encryption_key, 0, $iv);
        
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseContent($decrypted_content);
        $text = $pdf->getText();

        if (empty(trim($text))) {
            echo "El PDF no contiene texto legible.";
            exit;
        }
        $text = substr(trim($text), 0, 1000); // Limita el texto a los primeros 1000 caracteres

        $client = new Client();
        try {
            $response = $client->post('https://api-inference.huggingface.co/models/facebook/bart-large-cnn', [
                'headers' => [
                    'Authorization' => 'Bearer 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',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => $text,
                    'parameters' => [
                        'max_length' => 100,
                        'min_length' => 30,
                        'do_sample' => false,
                    ],
                ],
            ]);

            $result = json_decode($response->getBody(), true);
            if (isset($result[0]['summary_text'])) {
                $summary = $result[0]['summary_text'];
                echo "<h2>Resumen de: " . htmlspecialchars($titulo) . "</h2>";
                echo "<p>" . htmlspecialchars($summary) . "</p>";
            } else {
                echo "No se pudo generar un resumen. Respuesta de la API: " . print_r($result, true);
            }
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            echo "Error del servidor de la API: " . $responseBodyAsString;
            error_log("Error de la API de Hugging Face: " . $responseBodyAsString);
        } catch (Exception $e) {
            echo "<p class='text-danger'>Error al generar el resumen: " . htmlspecialchars($e->getMessage()) . "</p>";
            //error_log("Error al generar el resumen: " . $e->getMessage());
        }
    } else {
        echo "El archivo no se encuentra en ninguna ubicación del servidor.";
    }
} else {
    echo "Archivo no encontrado o no tienes permiso para verlo.";
}

$stmt->close();
$conn->close();
?>