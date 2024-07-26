<?php
session_start();

include('../../database/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$base_dir = "C:/xampp/htdocs/guardarDocs/uploads/";
$locations = [
    $base_dir . "location1/",
    $base_dir . "location2/",
    $base_dir . "location3/"
];

// Crear las carpetas si no existen
foreach ($locations as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Conexión a la base de datos

// if ($conn->connect_error) {
//     die("Conexión fallida: " . $conn->connect_error);
// }

$uploadOk = 1;
$imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

// Obtener el nombre del cliente
$cliente_id = $_POST['cliente_id'];
$stmt = $conn->prepare("SELECT nombre FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$clienteNombre = preg_replace("/[^a-zA-Z0-9]/", "", $cliente['nombre']); // Eliminar caracteres especiales
$stmt->close();

// Generar un código único para el documento
$codDoc = uniqid();

// Crear el nuevo nombre del archivo
$newFileName = $codDoc . "_" . $clienteNombre . ".pdf";

// Verificar el tamaño del archivo
if ($_FILES["fileToUpload"]["size"] > 5*1024*1024) {
    $_SESSION['message'] = "Lo siento, tu archivo es demasiado grande.";
    $_SESSION['alert_type'] = "danger";
    $uploadOk = 0;
}

// Permitir solo archivos PDF
if($imageFileType != "pdf") {
    $_SESSION['message'] = "Lo siento, solo se permiten archivos PDF.";
    $_SESSION['alert_type'] = "warning";
    $uploadOk = 0;
}

//ANTESSS
//$_SESSION['show_loading'] = true;


// Subir y encriptar el archivo si todo está bien
if ($uploadOk == 1) {
    $temp_file = $_FILES["fileToUpload"]["tmp_name"];
    
    // Leer el contenido del archivo
    $fileContent = file_get_contents($temp_file);
    
    // Generar una clave de encriptación única para este archivo
    $encryption_key = openssl_random_pseudo_bytes(32);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    
    // Encriptar el contenido
    $encrypted = openssl_encrypt($fileContent, 'aes-256-cbc', $encryption_key, 0, $iv);
    
    $upload_success = true;
    
    // Guardar el archivo encriptado en todas las ubicaciones
    foreach ($locations as $location) {
        $target_file = $location . $newFileName;
        if (file_put_contents($target_file, $encrypted) === false) {
            $upload_success = false;
            break;
        }
    }
    
    //ANTESSSSSSS
    if ($upload_success) {
        // Guardar información del archivo en la base de datos
        $user_id = $_SESSION['user_id'];

        $sql = "INSERT INTO docs (titulo, usuario_id, cliente_id, encryption_key, iv) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiss", $newFileName, $user_id, $cliente_id, $encryption_key, $iv);
                
        if ($stmt->execute()) {
            $_SESSION['message'] = "El archivo ha sido subido, encriptado y guardado en múltiples ubicaciones como: " . $newFileName;
            $_SESSION['alert_type'] = "success";
        } else {
            $_SESSION['message'] = "Error al guardar en la base de datos: " . $stmt->error;
            $_SESSION['alert_type'] = "danger";
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "Lo siento, hubo un error al subir y encriptar tu archivo en todas las ubicaciones.";
        $_SESSION['alert_type'] = "danger";
    }
} else {
    $_SESSION['message'] = "No se pudo procesar el archivo debido a errores previos.";
    $_SESSION['alert_type'] = "danger";
}

$conn->close();

// Redirigir a upload.php
//$_SESSION['show_loading'] = true;
header("Location: ../upload.php");
exit();
?>