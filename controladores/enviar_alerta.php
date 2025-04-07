<?php
require_once __DIR__ . "/productos.controlador.php"; // Asegúrate de incluir los archivos necesarios
require_once __DIR__ . "/../vendor/autoload.php"; // Para PHPMailer
require_once __DIR__ . "/../modelos/productos.modelo.php"; 
require_once __DIR__ . "/../modelos/usuarios.modelo.php"; 

// Obtener productos con bajo stock
$productosBajoStock = ModeloProductos::mdlMostrarBajoStockPorCategoria();

if (!empty($productosBajoStock)) {
    // Obtener usuarios con roles de Administrador y Almacenero
    $tablaUsuarios = "usuarios";
    $roles = ["Administrador", "Almacenero"];
    $usuariosDestino = ModeloUsuarios::mdlObtenerUsuariosPorRoles($tablaUsuarios, $roles);

    // Generar una tabla HTML con los primeros 10 productos
    $tablaHTML = "
        <table border='1' style='border-collapse: collapse; width: 100%;'>
            <tr>
                <th style='padding: 8px; background-color: #f2f2f2;'>CODIGO</th>
                <th style='padding: 8px; background-color: #f2f2f2;'>CATEGORIA</th>
                <th style='padding: 8px; background-color: #f2f2f2;'>PRODUCTO</th>
                <th style='padding: 8px; background-color: #f2f2f2;'>STOCK ACTUAL</th>
                <th style='padding: 8px; background-color: #f2f2f2;'>UBICACION</th>
                <th style='padding: 8px; background-color: #f2f2f2;'>TALLA</th>
            </tr>
    ";

    $primeros10Productos = array_slice($productosBajoStock, 0, 10); // Tomar los primeros 10 productos
    foreach ($primeros10Productos as $producto) {
        $tablaHTML .= "
            <tr>
                <td style='padding: 8px;'>" . htmlspecialchars($producto['codigo']) . "</td>
                <td style='padding: 8px;'>" . htmlspecialchars($producto['categoria']) . "</td>
                <td style='padding: 8px;'>" . htmlspecialchars($producto['descripcion']) . "</td>
                <td style='padding: 8px;'>" . htmlspecialchars($producto['stock']) . "</td>
                <td style='padding: 8px;'>" . htmlspecialchars($producto['ubicacion']) . "</td>
                <td style='padding: 8px;'>" . htmlspecialchars($producto['talla']) . "</td>
            </tr>
        ";
    }

    $tablaHTML .= "</table>";

    // Determinar si se debe generar un archivo Excel
    $adjuntarExcel = count($productosBajoStock) > 10;

    if ($adjuntarExcel) {
        // Generar el archivo Excel con todos los productos
        $nombreArchivo = "Reporte_Bajo_Stock_" . date('Y-m-d') . ".xls";
        $rutaArchivo = sys_get_temp_dir() . "/" . $nombreArchivo;
        $contenidoExcel = "
            <table border='1'>
                <tr>
                    <th>CODIGO</th>
                    <th>CATEGORIA</th>
                    <th>PRODUCTO</th>
                    <th>STOCK ACTUAL</th>
                    <th>UBICACION</th>
                    <th>TALLA</th>
                </tr>
        ";

        foreach ($productosBajoStock as $producto) {
            $contenidoExcel .= "
                <tr>
                    <td>" . htmlspecialchars($producto['codigo']) . "</td>
                    <td>" . htmlspecialchars($producto['categoria']) . "</td>
                    <td>" . htmlspecialchars($producto['descripcion']) . "</td>
                    <td>" . htmlspecialchars($producto['stock']) . "</td>
                    <td>" . htmlspecialchars($producto['ubicacion']) . "</td>
                    <td>" . htmlspecialchars($producto['talla']) . "</td>
                </tr>
            ";
        }

        $contenidoExcel .= "</table>";
        file_put_contents($rutaArchivo, $contenidoExcel);
    }

    // Configurar PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    try {
        // Configuración del servidor de correo
        $mail->isSMTP();
        $mail->Host = 'p3plzcpnl508720.prod.phx3.secureserver.net'; // Cambia por tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'alpacasupport@alpdigi.online'; // Tu correo
        $mail->Password = 'ALPACAsoporte890'; // Token de aplicación
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente
        $mail->setFrom('alpacasupport@alpdigi.online', 'Soporte Alpaca F. Clothing');

        // Agregar destinatarios
        foreach ($usuariosDestino as $usuario) {
            $mail->addAddress($usuario['correo'], $usuario['nombre']);
        }

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Reporte de Bajo Stock';

        $mensajeCorreo = "<h1>Reporte de Productos con Bajo Stock</h1>";
        $mensajeCorreo .= "<p>A continuación, se muestran los primeros 10 productos con bajo stock:</p>";
        $mensajeCorreo .= $tablaHTML;

        if ($adjuntarExcel) {
            $mensajeCorreo .= "<p>Se adjunta un archivo Excel con todos los productos con bajo stock.</p>";
            $mail->addAttachment($rutaArchivo, $nombreArchivo);
        } else {
            $mensajeCorreo .= "<p>No hay más de 10 productos con bajo stock.</p>";
        }

        $mail->Body = $mensajeCorreo;

        // Enviar correo
        $mail->send();
        echo "El reporte ha sido enviado correctamente.";
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    } finally {
        // Eliminar el archivo temporal si se creó
        if ($adjuntarExcel && file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
    }
} else {
    echo "No hay productos con bajo stock.";
}
?>