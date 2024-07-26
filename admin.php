<?php
include('database/db_connect.php');
// Conexión a la base de datos
//$db = new mysqli('localhost', 'root', 'password', 'documentos');

// Verificar conexión a la base de datos
if ($conn->connect_error) {
    die('Error de conexión: ' . $db->connect_error);
}

// Si se envía el formulario de registro
if (isset($_POST['registrar'])) {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // Generar hash de contraseña usando password_hash()
    $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

    // Preparar consulta SQL para insertar usuario
    $sql = "INSERT INTO usuarios (nombre, apellido, email, contrasena, fecha_creacion) VALUES (?, ?, ?, ?, NOW())";

    // Preparar declaración
    $stmt = $conn->prepare($sql);

    // Vincular parámetros
    $stmt->bind_param('ssss', $nombre, $apellido, $email, $hash_contrasena);


    // Ejecutar consulta
    if ($stmt->execute()) {
        echo "Usuario registrado correctamente.";
    } else {
        echo "Error al registrar usuario: " . $$conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de usuarios</title>
</head>
<body>
    <h1>Registro de usuarios</h1>

    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required><br>

        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required><br>

        <br>
        <input type="submit" name="registrar" value="Registrar">
    </form>
</body>
</html>
