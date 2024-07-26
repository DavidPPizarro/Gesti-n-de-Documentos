<?php
include('../database/db_connect.php');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT id, nombre, email, telefono, direccion, fecha_registro FROM clientes ORDER BY nombre";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["telefono"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["direccion"]) ."</td>";
        echo "<td>" . $row["fecha_registro"] . "</td>";
        echo "<td>
                <button class='btn btn-primary btn-sm upload-btn' data-cliente-id='" . $row["id"] . "' data-cliente-nombre='" . htmlspecialchars($row["nombre"]) . "'>
                    <i class='fas fa-upload'></i> Subir Documento
                </button>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hay clientes disponibles</td></tr>";
}

$conn->close();
?>