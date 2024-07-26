<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../../vendor/autoload.php';
include('../../database/db_connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Acceso no autorizado']));
}

if (!isset($_GET['query']) || empty($_GET['query'])) {
    exit(json_encode(['error' => 'Consulta de búsqueda vacía']));
}

$query = $_GET['query'];
$user_id = $_SESSION['user_id'];

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

try {
    $sql = "SELECT id, titulo, encryption_key, iv FROM docs";
    $stmt = $conn->prepare($sql);
//    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $matches = [];
    $parser = new \Smalot\PdfParser\Parser();

    while ($row = $result->fetch_assoc()) {
        $file_path = getFilePath($row['titulo']);
        if ($file_path !== false) {
            $encrypted_content = file_get_contents($file_path);
            $decrypted_content = openssl_decrypt($encrypted_content, 'aes-256-cbc', $row['encryption_key'], 0, $row['iv']);
            
            // Guardar el contenido desencriptado en un archivo temporal
            $temp_file = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($temp_file, $decrypted_content);

            // Parsear el PDF
            $pdf = $parser->parseFile($temp_file);
            $text = $pdf->getText();

            // Eliminar el archivo temporal
            unlink($temp_file);

            // Realizar la búsqueda
            if (stripos($text, $query) !== false) {
                $matches[] = [
                    'id' => $row['id'],
                    'titulo' => $row['titulo']
                ];
            }
        }
    }

    echo json_encode($matches);
} catch (Exception $e) {
    error_log("Error en search_pdfs.php: " . $e->getMessage());
    exit(json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]));
}