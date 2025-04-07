<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class ControladorDevoluciones {
    /* =============================================
    CREAR DEVOLUCIÓN
    ============================================= */
        static public function ctrCrearDevolucion() {
        if (isset($_POST["nuevaDevolucion"])) {
            // Validar que la cantidad sea mayor a cero
            if ($_POST["cantidad"] <= 0) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "La cantidad debe ser mayor a cero",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // Insertar la devolución con estado "Pendiente" por defecto
            $tabla = "devolucion";
            $datos = array(
                "id_producto" => $_POST["id_producto"],
                "cantidad" => $_POST["cantidad"],
                "motivo" => $_POST["motivo"],
                "usuario" => $_SESSION["id"],
                "estado" => "Pendiente" // Establecemos el estado como "Pendiente" por defecto
            );

            $respuesta = ModeloDevolucion::mdlIngresarDevolucion($tabla, $datos);

            if ($respuesta == "ok") {
                // Ya no necesitamos actualizar el stock aquí porque el estado siempre será "Pendiente"
                echo '<script>
                    swal({
                        type: "success",
                        title: "La Devolución ha sido guardada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "devolucion";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al guardar la devolución",
                        text: "Por favor, intenta nuevamente.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /* =============================================
    MOSTRAR DEVOLUCIONES
    ============================================= */
    static public function ctrMostrarDevoluciones($item, $valor) {
        $tabla = "devolucion"; // Tabla de devoluciones
        $respuesta = ModeloDevolucion::mdlMostrarDevolucion($tabla, $item, $valor);
        return $respuesta;
    }

    /* =============================================
    EDITAR DEVOLUCIÓN
    ============================================= */
    static public function ctrEditarDevolucion() {
        try {
            if (isset($_POST["id"])) {
                // Validar que los campos no estén vacíos
                if (empty($_POST["id"]) || empty($_POST["cantidad"]) || empty($_POST["motivo"])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Todos los campos son obligatorios",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }

                // Validar que la cantidad sea un número positivo mayor a cero
                if ($_POST["cantidad"] <= 0 || !is_numeric($_POST["cantidad"])) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "La cantidad debe ser un número positivo mayor a cero",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }

                // Obtener la devolución actual
                $tabla = "devolucion";
                $item = "id";
                $valor = $_POST["id"];
                $devolucionActual = ModeloDevolucion::mdlMostrarDevolucion($tabla, $item, $valor);

                if (!$devolucionActual) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "No se encontró la devolución",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                    return;
                }

                // Calcular la diferencia de cantidad si el estado cambia a "Aprobado"
                $nuevoEstado = $_POST["estado"];
                $diferenciaCantidad = 0;

                if ($nuevoEstado == "Aprobado" && $devolucionActual["estado"] != "Aprobado") {
                    $diferenciaCantidad = $_POST["cantidad"];
                } elseif ($nuevoEstado != "Aprobado" && $devolucionActual["estado"] == "Aprobado") {
                    $diferenciaCantidad = -$_POST["cantidad"];
                }

                // Actualizar el stock del producto si hay una diferencia
                if ($diferenciaCantidad != 0) {
                    $tablaProducto = "productos";
                    $idProducto = $devolucionActual["id_producto"];
                    
                    // Obtener el producto actual
                    $productoActual = ModeloProductos::mdlMostrarProductos($tablaProducto, "id", $idProducto, null);
                    
                    // Verificar si se obtuvo el producto
                    if (empty($productoActual)) {
                        echo '<script>
                            swal({
                                type: "error",
                                title: "No se encontró el producto",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            });
                        </script>';
                        return;
                    }

                    // Verificar que tengamos acceso al stock
                    if (!isset($productoActual["stock"])) {
                        error_log("Error: Estructura de producto incorrecta: " . print_r($productoActual, true));
                        echo '<script>
                            swal({
                                type: "error",
                                title: "Error al obtener el stock del producto",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            });
                        </script>';
                        return;
                    }

                    $nuevoStock = $productoActual["stock"] + $diferenciaCantidad;

                    // Llamar al modelo de productos para actualizar SOLO el stock
                    $actualizarStock = ModeloProductos::mdlActualizarStockProducto($tablaProducto, $idProducto, $nuevoStock);
                    if ($actualizarStock != "ok") {
                        error_log("Error: No se pudo actualizar el stock del producto.");
                        echo '<script>
                            swal({
                                type: "error",
                                title: "Error al actualizar el stock",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            });
                        </script>';
                        return;
                    }
                }

                // Actualizar la devolución
                $datos = array(
                    "id" => $_POST["id"],
                    "cantidad" => $_POST["cantidad"],
                    "motivo" => $_POST["motivo"],
                    "estado" => $nuevoEstado
                );

                $respuesta = ModeloDevolucion::mdlEditarDevolucion($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "La devolución ha sido editada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if (result.value) {
                                window.location = "devolucion";
                            }
                        });
                    </script>';
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al editar la devolución",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        });
                    </script>';
                }
            }
        } catch (Exception $e) {
            error_log("Error en ctrEditarDevolucion: " . $e->getMessage());
            echo '<script>
                swal({
                    type: "error",
                    title: "Error inesperado",
                    text: "' . $e->getMessage() . '",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
    }


/* =============================================
MOSTRAR DEVOLUCIONES CON NOMBRE DE USUARIO Y DESCRIPCIÓN DEL PRODUCTO
============================================= */
static public function ctrMostrarDevolucionesConUsuarioYProducto($item, $valor) {
    try {
        $tabla = "devolucion";
        // Llamar al modelo para obtener la devolución con el nombre del usuario y la descripción del producto
        $respuesta = ModeloDevolucion::mdlMostrarDevolucionConUsuarioYProducto($tabla, $item, $valor);
        
        // Log para verificar los datos recibidos del modelo (opcional)
        error_log("Datos recibidos en ctrMostrarDevolucionesConUsuarioYProducto: " . print_r($respuesta, true));
        
        return $respuesta;
    } catch (Exception $e) {
        // Log para capturar errores en el controlador
        error_log("Error en ctrMostrarDevolucionesConUsuarioYProducto: " . $e->getMessage());
        return [];
    }
}

    /* =============================================
    BORRAR DEVOLUCIÓN
    ============================================= */
    static public function ctrBorrarDevolucion() {
        if (isset($_GET["id"])) {
            $tabla = "devolucion";
            $id = $_GET["id"]; 


            $item = "id";
            $valor = $_GET["id"];
            $devolucionEliminada = ModeloDevolucion::mdlMostrarDevolucion($tabla, $item, $valor);
    
            if (!$devolucionEliminada) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al obtener la devolución",
                        text: "Por favor, intenta nuevamente.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
    
            // Eliminar la devolución
            $respuesta = ModeloDevolucion::mdlBorrarDevolucion($tabla, $id);
    
            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "La devolución ha sido eliminada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "devolucion";
                        }
                    });
                </script>';
                
                if ($devolucionEliminada["estado"] == "aprobado") {
                    $tablaProductos = "productos";
                    $idProducto = $devolucionEliminada["id_producto"];
                    $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, "id", $idProducto, "fecha_creacion DESC");
    
                    if (!empty($traerProducto)) {
                        $item2 = "stock";
                        $valor2 = $traerProducto["stock"] - $devolucionEliminada["cantidad"];
                        ModeloProductos::mdlActualizarProducto($tablaProductos, $item2, $valor2, $idProducto);
                    }
                }
    
    
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al eliminar la devolución",
                        text: "Por favor, intenta nuevamente.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }
        /*=============================================
DESCARGAR EXCEL - DEVOLUCIONES
=============================================*/
static public function ctrDescargarReporteDevoluciones() {
    if (isset($_GET["reporte"])) {
        // Obtener fechas del rango, si están definidas
        $fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
        $fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;

        // Obtener datos de devoluciones
        $tablaDevoluciones = "devolucion";
        if ($fechaInicial && $fechaFinal) {
            $devoluciones = ModeloDevolucion::mdlRangoFechasDevoluciones($tablaDevoluciones, $fechaInicial, $fechaFinal);
        } else {
            $item = null;
            $valor = null;
            $devoluciones = ModeloDevolucion::mdlMostrarDevolucion($tablaDevoluciones, $item, $valor);
        }

        // Crear nuevo documento Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar encabezados
        $sheet->setCellValue('A1', 'TIPO MOVIMIENTO');
        $sheet->setCellValue('B1', 'PRODUCTO');
        // $sheet->setCellValue('C1', 'TALLA');
        $sheet->setCellValue('C1', 'CANTIDAD');
        $sheet->setCellValue('D1', 'USUARIO');
        $sheet->setCellValue('E1', 'FECHA');

        // Aplicar estilos a los encabezados (negritas)
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Llenar la hoja con los datos
        $row = 2; // Comenzar desde la fila 2 (debajo de los encabezados)
        foreach ($devoluciones as $devolucion) {
            // Obtener el nombre del producto y la talla
            $tablaProductos = "productos";
            $itemProducto = "id";
            $valorProducto = $devolucion["id_producto"];
            $producto = ModeloProductos::mdlMostrarProductos($tablaProductos, $itemProducto, $valorProducto, null);

            $nombreProducto = isset($producto["codigo"]) ? $producto["codigo"] : "Producto no encontrado";
            // $tallaProducto = isset($producto["talla"]) ? $producto["talla"] : "Sin talla";

            // Obtener el nombre del usuario
            $nombreUsuario = "Usuario no encontrado"; // Valor predeterminado
            if (isset($devolucion["usuario"])) { // Verificar si existe el campo id_usuario
                $tablaUsuarios = "usuarios";
                $itemUsuario = "id";
                $valorUsuario = $devolucion["usuario"];
                $usuario = ModeloUsuarios::mdlMostrarUsuarios($tablaUsuarios, $itemUsuario, $valorUsuario);
                $nombreUsuario = isset($usuario["nombre"]) ? $usuario["nombre"] : "Usuario no encontrado";
            }

            // Escribir los datos en el archivo Excel
            $sheet->setCellValue('A' . $row, 'Devolución');
            $sheet->setCellValue('B' . $row, $nombreProducto);
            // $sheet->setCellValue('C' . $row, $tallaProducto);
            $sheet->setCellValue('C' . $row, $devolucion["cantidad"]);
            $sheet->setCellValue('D' . $row, $nombreUsuario);
            $sheet->setCellValue('E' . $row, ($devolucion["fecha"]));
            $row++;
        }

        // Establecer el nombre del archivo
        $fileName = $_GET["reporte"] . '_' . date('Y-m-d') . ".xlsx";

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

}
