<?php
session_start();
$showLoading = isset($_SESSION['show_loading']) && $_SESSION['show_loading'];
unset($_SESSION['show_loading']);
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Documentos</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.7.14/lottie.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .container-fluid { padding: 20px; }
        .card { margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .card-header { background-color: #007bff; color: white; }
        h2, h3 { color: #007bff; }
        .navbar-custom { background-color: #007bff; padding: 10px; }
        .navbar-custom .navbar-brand, .navbar-custom .navbar-nav .nav-link { color: white; }
        .logout-btn { color: white; }
        #uploadFormContainer { display: none; margin-top: 20px; }
        #summaryModal .modal-body { max-height: 400px; overflow-y: auto; }
        
        /* Estilos responsivos */
        @media (max-width: 768px) {
            .container-fluid { padding: 10px; }
            .card { margin-bottom: 15px; }
            .navbar-custom { padding: 5px; }
            .navbar-brand { font-size: 1.2rem; }
            .table-responsive { overflow-x: auto; }
        }
    </style>
</head>
<body>

<?php
if (isset($_SESSION['message']) && isset($_SESSION['alert_type'])) {
    echo '<div class="alert alert-' . $_SESSION['alert_type'] . ' alert-dismissible fade show" role="alert">
            ' . $_SESSION['message'] . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
    unset($_SESSION['message']);
    unset($_SESSION['alert_type']);
}
?>

<div id="loading-screen" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div id="lottie-container" style="width: 200px; height: 200px;"></div>
        <p style="color: white; text-align: center;">Cargando...</p>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-custom">
    <span class="navbar-brand mb-0 h1">Gestión de Documentos</span>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
        <span class="navbar-text ml-auto">
            Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
        </span>
        <a href="logout.php" class="btn btn-secondary ml-3 logout-btn">Cerrar sesión</a>
    </div>
</nav>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Documentos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="search-tab" data-toggle="tab" href="#search" role="tab" aria-controls="search" aria-selected="false">Búsqueda en PDFs</a>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">Clientes</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="clientesTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nombre Completo</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Dirección</th>
                                            <th>Fecha de Registro</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php include 'actions/get_Clientes.php'; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card" id="uploadFormContainer">
                        <div class="card-header">
                            <h3 class="mb-0">Subir Documento</h3>
                        </div>
                        <div class="card-body">
                            <form id="uploadForm" action="actions/upload_file.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="cliente_id" id="selectedClienteId">
                                <div class="form-group">
                                    <label for="clienteNombre">Cliente:</label>
                                    <input type="text" id="clienteNombre" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="fileToUpload">Seleccionar archivo:</label>
                                    <input type="file" name="fileToUpload" id="fileToUpload" class="form-control-file" required accept=".pdf">
                                </div>
                                <button type="submit" class="btn btn-primary">Subir Archivo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="mb-0">Documentos Subidos</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="documentsTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nombre del archivo</th>
                                            <th>Cliente</th>
                                            <th>Fecha de subida</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php include 'actions/list_files.php'; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="search" role="tabpanel" aria-labelledby="search-tab">
        <div class="container mt-4">
            <h2>Búsqueda en PDFs</h2>
            <form id="searchForm">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchQuery" placeholder="Ingrese su búsqueda">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
            <div id="searchResults" class="mt-4"></div>
        </div>
    </div>
</div>

<!-- Modal para mostrar el resumen -->
<div class="modal fade" id="summaryModal" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="summaryModalLabel">Resumen del Documento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="summaryContent">
                <!-- El resumen se cargará aquí -->
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        var spanishLanguage = {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        };

        $('#clientesTable').DataTable({
            language: spanishLanguage,
            responsive: true
        });

        $('#documentsTable').DataTable({
            language: spanishLanguage,
            responsive: true
        });

        $('.upload-btn').click(function() {
            var clienteId = $(this).data('cliente-id');
            var clienteNombre = $(this).data('cliente-nombre');
            $('#selectedClienteId').val(clienteId);
            $('#clienteNombre').val(clienteNombre);
            $('#uploadFormContainer').slideDown();
            $('html, body').animate({
                scrollTop: $("#uploadFormContainer").offset().top
            }, 1000);
        });

        $(document).on('click', '.summarize-btn', function() {
            var docId = $(this).data('doc-id');
            $('#summaryContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generando resumen...</div>');
            $('#summaryModal').modal('show');

            $.ajax({
                url: 'actions/summarize_pdf.php',
                method: 'GET',
                data: { id: docId },
                success: function(response) {
                    $('#summaryContent').html(response);
                },
                error: function() {
                    $('#summaryContent').html('<div class="alert alert-danger">Error al generar el resumen.</div>');
                }
            });
        });

        $('#searchForm').submit(function(e) {
            e.preventDefault();
            var query = $('#searchQuery').val();
            $.ajax({
                url: 'actions/search_pdfs.php',
                method: 'GET',
                data: { query: query },
                dataType: 'json',
                success: function(response) {
                    var resultsHtml = '<h3>Resultados de la búsqueda:</h3>';
                    if (response.error) {
                        resultsHtml += '<p class="text-danger">Error: ' + response.error + '</p>';
                    } else if (response.length > 0) {
                        resultsHtml += '<ul>';
                        response.forEach(function(doc) {
                            resultsHtml += '<li><a href="actions/view_pdf.php?id=' + doc.id + '" target="_blank">' + doc.titulo + '</a></li>';
                        });
                        resultsHtml += '</ul>';
                    } else {
                        resultsHtml += '<p>No se encontraron resultados.</p>';
                    }
                    $('#searchResults').html(resultsHtml);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log("Error status: " + textStatus);
                    console.log("Error thrown: " + errorThrown);
                    console.log("Response Text: " + jqXHR.responseText);
                    $('#searchResults').html('<p class="text-danger">Error al realizar la búsqueda. Detalles: ' + textStatus + ' - ' + errorThrown + '</p>');
                }
            });
        });

        // Cargar la última pestaña activa
        var lastActiveTab = localStorage.getItem('lastActiveTab');
        if (lastActiveTab) {
            $('a[href="' + lastActiveTab + '"]').tab('show');
        }

        // Guardar la pestaña activa cuando cambie

// Guardar la pestaña activa cuando cambie
$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var activeTab = $(e.target).attr('href');
            localStorage.setItem('lastActiveTab', activeTab);
        });
    });

    let loadingAnimation;
    let loadingTimeout;

    function showLoading() {
        document.getElementById('loading-screen').style.display = 'block';
        if (!loadingAnimation) {
            loadingAnimation = lottie.loadAnimation({
                container: document.getElementById('lottie-container'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: '../public/animation_Hamster.json'
            });
        } else {
            loadingAnimation.play();
        }
        
        // Configurar el temporizador para ocultar después de 5 segundos exactos
        if (loadingTimeout) {
            clearTimeout(loadingTimeout);
        }
        loadingTimeout = setTimeout(hideLoading, 5000);
    }

    function hideLoading() {
        if (loadingAnimation) {
            loadingAnimation.stop();
        }
        document.getElementById('loading-screen').style.display = 'none';
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        showLoading();
        // No llames a hideLoading() aquí
    });

    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($showLoading): ?>
        showLoading();
        <?php endif; ?>
    });
</script>
</body>
</html>