<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Acceso denegado');
}

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

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

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
        // Leer el contenido encriptado
        $encrypted_content = file_get_contents($file_path);
        
        // Desencriptar el contenido
        $decrypted_content = openssl_decrypt($encrypted_content, 'aes-256-cbc', $encryption_key, 0, $iv);
        
        // Enviar el PDF desencriptado al navegador
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=\"$titulo\"");
        echo $decrypted_content;
        exit;
    } else {
        echo "El archivo no se encuentra en ninguna ubicación del servidor.";
    }
} else {
    echo "Archivo no encontrado o no tienes permiso para verlo.";
}

$stmt->close();
$conn->close();
?>