<?php
include('../database/db_connect.php');
if (!isset($_SESSION['user_id'])) {
    exit();
}
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT d.id, d.titulo, d.fecha_creacion, c.nombre as cliente_nombre 
        FROM docs d 
        JOIN clientes c ON d.cliente_id = c.id 
        ORDER BY d.fecha_creacion DESC";
$stmt = $conn->prepare($sql);
//$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["titulo"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["cliente_nombre"]) . "</td>";
        echo "<td>" . $row["fecha_creacion"] . "</td>";
        echo "<td>
                <a href='../views/actions/view_pdf.php?id=" . $row["id"] . "' target='_blank' class='btn btn-sm btn-primary'>Ver</a> 
                <button class='btn btn-sm btn-info summarize-btn' data-doc-id='" . $row["id"] . "'>Resumir</button>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No hay archivos subidos.</td></tr>";
}

$stmt->close();
$conn->close();
?>