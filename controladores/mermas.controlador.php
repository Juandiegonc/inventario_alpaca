<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ControladorMermas {

    /* =============================================
    MOSTRAR MERMAS
    ============================================= */
    static public function ctrMostrarMermas($item, $valor) {
        return ModeloMermas::mdlMostrarMermas("mermas", $item, $valor);
    }
    

    /* =============================================
    CREAR MERMA
    ============================================= */
    static public function ctrCrearMerma() {
        if (isset($_POST["nuevaMerma"])) {
            try {
                // Validaciones básicas
                if (empty($_POST["id_producto"]) || 
                    empty($_POST["cantidad"]) || 
                    empty($_POST["motivo"])) {
                    
                    throw new Exception("Todos los campos son obligatorios");
                }

                // Validar que la cantidad sea positiva
                if ($_POST["cantidad"] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a cero");
                }

                // Verificar stock disponible
                $producto = ModeloProductos::mdlMostrarProductos("productos", "id", $_POST["id_producto"], null);
                if ($producto["stock"] < $_POST["cantidad"]) {
                    throw new Exception("No hay suficiente stock disponible");
                }

                // Crear la merma
                $tabla = "mermas";
                $datos = array(
                    "id_producto" => $_POST["id_producto"],
                    "cantidad" => $_POST["cantidad"],
                    "motivo" => $_POST["motivo"],
                    "usuario" => $_SESSION["id"]
                );

                $respuesta = ModeloMermas::mdlIngresarMerma($tabla, $datos);

                if ($respuesta == "ok") {
                    // Actualizar el stock del producto
                    $nuevoStock = $producto["stock"] - $_POST["cantidad"];
                    ModeloProductos::mdlActualizarStockProducto("productos", $_POST["id_producto"], $nuevoStock);

                    echo '<script>
                        swal({
                            type: "success",
                            title: "La merma ha sido registrada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "merma";
                            }
                        });
                    </script>';
                }

            } catch (Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "'.$e->getMessage().'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /* =============================================
    EDITAR MERMA
    ============================================= */
    static public function ctrEditarMerma() {
        if (isset($_POST["editarMerma"])) {
            try {
                // Validar que los campos obligatorios no estén vacíos
                if (empty($_POST["id"]) || empty($_POST["cantidad"]) || empty($_POST["motivo"])) {
                    throw new Exception("Todos los campos son obligatorios");
                }
    
                // Validar que la cantidad sea un número positivo
                if (!is_numeric($_POST["cantidad"]) || $_POST["cantidad"] <= 0) {
                    throw new Exception("La cantidad debe ser un número positivo");
                }
    
                // Obtener la merma actual
                $mermaActual = ModeloMermas::mdlMostrarMermas("mermas", "id", $_POST["id"]);
    
                // Si no se encuentra la merma, lanzar una excepción
                if (!$mermaActual) {
                    throw new Exception("No se encontró la merma especificada");
                }
    
                // Si cambia la cantidad, actualizar el stock del producto
                if ($mermaActual["cantidad"] != $_POST["cantidad"]) {
                    $producto = ModeloProductos::mdlMostrarProductos("productos", "id", $mermaActual["id_producto"], null);
    
                    // Si no se encuentra el producto, lanzar una excepción
                    if (!$producto) {
                        throw new Exception("No se encontró el producto asociado a la merma");
                    }
    
                    // Calcular el stock temporal
                    $stockTemporal = $producto["stock"] + $mermaActual["cantidad"];
    
                    // Verificar si hay suficiente stock para la nueva cantidad
                    if ($stockTemporal < $_POST["cantidad"]) {
                        throw new Exception("No hay suficiente stock disponible");
                    }
    
                    // Calcular el nuevo stock
                    $nuevoStock = $stockTemporal - $_POST["cantidad"];
    
                    // Actualizar el stock del producto
                    ModeloProductos::mdlActualizarStockProducto("productos", $mermaActual["id_producto"], $nuevoStock);
                }
    
                // Preparar los datos para actualizar la merma
                $datos = array(
                    "id" => $_POST["id"],
                    "cantidad" => $_POST["cantidad"],
                    "motivo" => $_POST["motivo"]
                );
    
                // Actualizar la merma
                $respuesta = ModeloMermas::mdlEditarMerma("mermas", $datos);
    
                // Mostrar mensaje de éxito o error
                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "La merma ha sido actualizada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "merma";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al actualizar la merma");
                }
    
            } catch (Exception $e) {
                // Mostrar mensaje de error
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "'.$e->getMessage().'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /* =============================================
    BORRAR MERMA
    ============================================= */
    static public function ctrBorrarMerma() {
        if (isset($_GET["id"])) {
            try {
                // Obtener información de la merma antes de borrarla
                $merma = ModeloMermas::mdlMostrarMermas("mermas", "id", $_GET["id"]);
                
                if (!$merma) {
                    throw new Exception("No se encontró la merma");
                }

                // Devolver el stock al producto
                $producto = ModeloProductos::mdlMostrarProductos("productos", "id", $merma["id_producto"], null);
                $nuevoStock = $producto["stock"] + $merma["cantidad"];
                ModeloProductos::mdlActualizarStockProducto("productos", $merma["id_producto"], $nuevoStock);

                // Eliminar la merma
                $respuesta = ModeloMermas::mdlBorrarMerma("mermas", $_GET["id"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "La merma ha sido eliminada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "merma";
                            }
                        });
                    </script>';
                }

            } catch (Exception $e) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "'.$e->getMessage().'",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /* =============================================
    MOSTRAR MERMAS CON DETALLES
    ============================================= */
    static public function ctrMostrarMermasDetalladas($item, $valor) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT m.*, 
                p.descripcion as producto_descripcion,
                u.nombre as nombre_usuario
                FROM mermas m
                LEFT JOIN productos p ON m.id_producto = p.id
                LEFT JOIN usuarios u ON m.usuario = u.id
                WHERE m.$item = :valor");

            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error en ctrMostrarMermasDetalladas: " . $e->getMessage());
            return [];
        }
    }
    
     /*=============================================
DESCARGAR EXCEL - MERMAS
=============================================*/
public function ctrDescargarReporteMermas($fechaInicial, $fechaFinal) {
    // Obtener datos de mermas
    $tabla = "mermas";
    if ($fechaInicial && $fechaFinal) {
        $mermas = ModeloMermas::mdlRangoFechasMermas($tabla, $fechaInicial, $fechaFinal);
    } else {
        $item = null;
        $valor = null;
        $mermas = ModeloMermas::mdlMostrarMermas($tabla, $item, $valor);
    }

    // Crear nuevo documento Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Agregar encabezados
    $sheet->setCellValue('A1', 'TIPO MOVIMIENTO');
    $sheet->setCellValue('B1', 'CANTIDAD');
    $sheet->setCellValue('C1', 'PRODUCTOS');
    // $sheet->setCellValue('D1', 'TALLA');
    $sheet->setCellValue('D1', 'USUARIO');
    $sheet->setCellValue('E1', 'FECHA');

    // Aplicar estilos a los encabezados (negritas)
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);

    // Llenar la hoja con los datos
    $row = 2; // Comenzar desde la fila 2 (debajo de los encabezados)
    foreach ($mermas as $merma) {
        // Obtener el nombre del producto y la talla
        $tablaProductos = "productos";
        $itemProducto = "id";
        $valorProducto = $merma["id_producto"];
        $producto = ModeloProductos::mdlMostrarProductos($tablaProductos, $itemProducto, $valorProducto, null);

        $nombreProducto = isset($producto["codigo"]) ? $producto["codigo"] : "Producto no encontrado";
        $tallaProducto = isset($producto["talla"]) ? $producto["talla"] : "Sin talla";

        // Obtener el nombre del usuario
        $tablaUsuarios = "usuarios";
        $itemUsuario = "id";
        $valorUsuario = $merma["usuario"];
        $usuario = ModeloUsuarios::mdlMostrarUsuarios($tablaUsuarios, $itemUsuario, $valorUsuario);

        $nombreUsuario = isset($usuario["nombre"]) ? $usuario["nombre"] : "Usuario no encontrado";

        // Escribir los datos en el archivo Excel
        $sheet->setCellValue('A' . $row, 'Merma');
        $sheet->setCellValue('B' . $row, $merma["cantidad"]);
        $sheet->setCellValue('C' . $row, $nombreProducto);
        // $sheet->setCellValue('D' . $row, $tallaProducto);
        $sheet->setCellValue('D' . $row, $nombreUsuario);
        $sheet->setCellValue('E' . $row, ($merma["fecha"]));
        $row++;
    }

    // Establecer el nombre del archivo
    $fileName = "Reporte_Mermas_" . date('Y-m-d') . ".xlsx";

    // Configurar las cabeceras HTTP para descargar el archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    // Guardar el archivo en la salida del navegador
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
}