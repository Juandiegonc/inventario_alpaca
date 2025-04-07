<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../modelos/kardex.modelo.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ControladorKardex {

    /*=============================================
    MOSTRAR KARDEX
    =============================================*/
    static public function ctrMostrarKardex() {
        if (isset($_GET['fechaInicial']) && isset($_GET['fechaFinal'])) {
            
            $fechaInicial = $_GET['fechaInicial'];
            $fechaFinal = $_GET['fechaFinal'];
            
            $idProducto = null;
            if (isset($_GET['idProducto']) && $_GET['idProducto'] != "0") {
                $idProducto = $_GET['idProducto'];
            }
            
            // Obtener movimientos de kardex
            $movimientos = ModeloKardex::mdlObtenerDatosKardex($fechaInicial, $fechaFinal, $idProducto);
            
            // Obtener saldo inicial
            $saldosIniciales = ModeloKardex::mdlObtenerSaldoInicial($fechaInicial, $idProducto);
            
            // Procesar los datos para el kardex UEPS
            return self::procesarKardexUEPS($movimientos, $saldosIniciales);
        }
        
        return null;
    }
    
    /*=============================================
    PROCESAR KARDEX METODO UEPS
    =============================================*/
    static public function procesarKardexUEPS($movimientos, $saldosIniciales) {
        // Resultado final del Kardex
        $kardex = [];
        
        // Si no hay saldo inicial, inicializamos con un kardex vacío
        if (empty($saldosIniciales)) {
            return $kardex;
        }
        
        // Organizamos por producto
        $productosProcesados = [];
        
        // Si es un solo producto en específico
        if (isset($saldosIniciales['id_producto'])) {
            $idProducto = $saldosIniciales['id_producto'];
            $productosProcesados[$idProducto] = [
                'info' => [
                    'id_producto' => $saldosIniciales['id_producto'],
                    'descripcion' => $saldosIniciales['descripcion'],
                    'codigo' => $saldosIniciales['codigo'],
                    'talla' => $saldosIniciales['talla']
                ],
                'saldo_inicial' => [
                    'cantidad' => $saldosIniciales['cantidad'],
                    'costo_unitario' => $saldosIniciales['costo_unitario'],
                    'valor_total' => $saldosIniciales['valor_total']
                ],
                'capas' => [
                    [
                        'cantidad' => $saldosIniciales['cantidad'],
                        'costo_unitario' => $saldosIniciales['costo_unitario'],
                        'valor_total' => $saldosIniciales['valor_total'],
                        'fecha' => null // La capa inicial no tiene fecha específica
                    ]
                ],
                'movimientos' => []
            ];
        } else {
            // Múltiples productos
            foreach ($saldosIniciales as $idProducto => $saldo) {
                $productosProcesados[$idProducto] = [
                    'info' => [
                        'id_producto' => $saldo['id_producto'],
                        'descripcion' => $saldo['descripcion'],
                        'codigo' => $saldo['codigo'],
                        'talla' => $saldo['talla']
                    ],
                    'saldo_inicial' => [
                        'cantidad' => $saldo['cantidad'],
                        'costo_unitario' => $saldo['costo_unitario'],
                        'valor_total' => $saldo['valor_total']
                    ],
                    'capas' => [
                        [
                            'cantidad' => $saldo['cantidad'],
                            'costo_unitario' => $saldo['costo_unitario'],
                            'valor_total' => $saldo['valor_total'],
                            'fecha' => null // La capa inicial no tiene fecha específica
                        ]
                    ],
                    'movimientos' => []
                ];
            }
        }
        
        // Procesar los movimientos
        foreach ($movimientos as $movimiento) {
            $idProducto = $movimiento['id_producto'];
            
            // Verificar si tenemos este producto en el procesamiento
            if (!isset($productosProcesados[$idProducto])) {
                // Si no existe, inicializamos el producto
                $productosProcesados[$idProducto] = [
                    'info' => [
                        'id_producto' => $idProducto,
                        'descripcion' => $movimiento['descripcion'],
                        'codigo' => $movimiento['codigo'],
                        'talla' => $movimiento['talla']
                    ],
                    'saldo_inicial' => [
                        'cantidad' => 0,
                        'costo_unitario' => 0,
                        'valor_total' => 0
                    ],
                    'capas' => [],
                    'movimientos' => []
                ];
            }
            
            // Crear un registro base para este movimiento
            $registroMovimiento = [
                'fecha' => $movimiento['fecha'],
                'tipo' => $movimiento['tipo_movimiento'],
                'motivo' => $movimiento['motivo'],
                'ubicacion' => $movimiento['ubicacion'],
                'entrada' => [
                    'cantidad' => 0,
                    'costo_unitario' => 0,
                    'valor_total' => 0
                ],
                'salida' => [
                    'cantidad' => 0,
                    'costo_unitario' => 0,
                    'valor_total' => 0
                ],
                'saldo' => [
                    'cantidad' => $productosProcesados[$idProducto]['capas'] ? array_sum(array_column($productosProcesados[$idProducto]['capas'], 'cantidad')) : 0,
                    'valor_total' => $productosProcesados[$idProducto]['capas'] ? array_sum(array_column($productosProcesados[$idProducto]['capas'], 'valor_total')) : 0
                ]
            ];
            
            // Procesar según el tipo de movimiento
            if ($movimiento['tipo_movimiento'] == 'Entrada' || $movimiento['tipo_movimiento'] == 'Devolución' || $movimiento['tipo_movimiento'] == 'Cambio Entrada') {
                // Es una entrada - agregamos una nueva capa UEPS
                $cantidad = (float)$movimiento['cantidad'];
                $costoUnitario = (float)$movimiento['costo_unitario'];
                $valorTotal = $cantidad * $costoUnitario;
                
                // Agregar la nueva capa
                $productosProcesados[$idProducto]['capas'][] = [
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costoUnitario,
                    'valor_total' => $valorTotal,
                    'fecha' => $movimiento['fecha']
                ];
                
                // Actualizar el registro de movimiento
                $registroMovimiento['entrada']['cantidad'] = $cantidad;
                $registroMovimiento['entrada']['costo_unitario'] = $costoUnitario;
                $registroMovimiento['entrada']['valor_total'] = $valorTotal;
                
                // Actualizar el saldo
                $registroMovimiento['saldo']['cantidad'] += $cantidad;
                $registroMovimiento['saldo']['valor_total'] += $valorTotal;
                
            } else if ($movimiento['tipo_movimiento'] == 'Venta' || $movimiento['tipo_movimiento'] == 'Merma' || $movimiento['tipo_movimiento'] == 'Cambio Salida') {
                // Es una salida - aplicamos UEPS
                $cantidadSalida = (float)$movimiento['cantidad'];
                $cantidadPendiente = $cantidadSalida;
                $valorSalida = 0;
                
                // Si no hay capas o la cantidad pendiente es cero, no procesamos
                if (empty($productosProcesados[$idProducto]['capas']) || $cantidadPendiente <= 0) {
                    continue;
                }
                
                // Ordenamos las capas por fecha en orden descendente (para UEPS)
                usort($productosProcesados[$idProducto]['capas'], function($a, $b) {
                    if ($a['fecha'] === null) return -1; // La capa inicial va al final
                    if ($b['fecha'] === null) return 1;
                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                });
                
                $nuevasCapas = [];
                $costoPromedioSalida = 0;
                
                // Procesamos las capas en orden UEPS
                foreach ($productosProcesados[$idProducto]['capas'] as $key => $capa) {
                    if ($cantidadPendiente <= 0) {
                        // Ya no necesitamos más cantidad, solo añadimos la capa completa
                        $nuevasCapas[] = $capa;
                        continue;
                    }
                    
                    $cantidadDisponible = (float)$capa['cantidad'];
                    $costoUnitario = (float)$capa['costo_unitario'];
                    
                    if ($cantidadPendiente >= $cantidadDisponible) {
                        // Consumimos toda la capa
                        $cantidadPendiente -= $cantidadDisponible;
                        $valorSalida += $cantidadDisponible * $costoUnitario;
                        // Esta capa se elimina
                    } else {
                        // Consumimos parte de la capa
                        $cantidadUsada = $cantidadPendiente;
                        $valorSalidaParcial = $cantidadUsada * $costoUnitario;
                        $valorSalida += $valorSalidaParcial;
                        
                        // Actualizamos la capa con la cantidad restante
                        $nuevasCapas[] = [
                            'cantidad' => $cantidadDisponible - $cantidadUsada,
                            'costo_unitario' => $costoUnitario,
                            'valor_total' => ($cantidadDisponible - $cantidadUsada) * $costoUnitario,
                            'fecha' => $capa['fecha']
                        ];
                        
                        $cantidadPendiente = 0;
                    }
                }
                
                // Si todavía hay cantidad pendiente, manejamos el error (stock insuficiente)
                if ($cantidadPendiente > 0) {
                    // En este caso podríamos mostrar una advertencia, pero seguimos procesando
                    $valorSalida = $cantidadSalida * ($valorSalida / ($cantidadSalida - $cantidadPendiente));
                }
                
                // Actualizamos las capas del producto
                $productosProcesados[$idProducto]['capas'] = $nuevasCapas;
                
                // Costo unitario promedio para esta salida
                $costoUnitarioSalida = $cantidadSalida > 0 ? $valorSalida / $cantidadSalida : 0;
                
                // Actualizar el registro de movimiento
                $registroMovimiento['salida']['cantidad'] = $cantidadSalida;
                $registroMovimiento['salida']['costo_unitario'] = $costoUnitarioSalida;
                $registroMovimiento['salida']['valor_total'] = $valorSalida;
                
                // Actualizar el saldo
                $registroMovimiento['saldo']['cantidad'] = array_sum(array_column($nuevasCapas, 'cantidad'));
                $registroMovimiento['saldo']['valor_total'] = array_sum(array_column($nuevasCapas, 'valor_total'));
            }
            
            // Calcular costo unitario promedio del saldo
            if ($registroMovimiento['saldo']['cantidad'] > 0) {
                $registroMovimiento['saldo']['costo_unitario'] = $registroMovimiento['saldo']['valor_total'] / $registroMovimiento['saldo']['cantidad'];
            } else {
                $registroMovimiento['saldo']['costo_unitario'] = 0;
            }
            
            // Agregar el movimiento procesado
            $productosProcesados[$idProducto]['movimientos'][] = $registroMovimiento;
        }
        
        // Ahora formateamos todo para nuestro resultado final
        foreach ($productosProcesados as $idProducto => $producto) {
            // Información del producto
            $datosProducto = [
                'id_producto' => $producto['info']['id_producto'],
                'descripcion' => $producto['info']['descripcion'],
                'codigo' => $producto['info']['codigo'],
                'talla' => $producto['info']['talla'],
                'saldo_inicial' => $producto['saldo_inicial'],
                'movimientos' => []
            ];
            
            // Si no hay movimientos pero sí saldo inicial, añadimos un registro inicial
            if (empty($producto['movimientos']) && $producto['saldo_inicial']['cantidad'] > 0) {
                $datosProducto['movimientos'][] = [
                    'fecha' => $fechaInicial ?? date('Y-m-d'),
                    'tipo' => 'Saldo Inicial',
                    'motivo' => '',
                    'ubicacion' => '',
                    'entrada' => [
                        'cantidad' => 0,
                        'costo_unitario' => 0,
                        'valor_total' => 0
                    ],
                    'salida' => [
                        'cantidad' => 0,
                        'costo_unitario' => 0,
                        'valor_total' => 0
                    ],
                    'saldo' => [
                        'cantidad' => $producto['saldo_inicial']['cantidad'],
                        'costo_unitario' => $producto['saldo_inicial']['costo_unitario'],
                        'valor_total' => $producto['saldo_inicial']['valor_total']
                    ]
                ];
            } else {
                // Añadimos todos los movimientos procesados
                foreach ($producto['movimientos'] as $mov) {
                    $datosProducto['movimientos'][] = $mov;
                }
            }
            
            // Añadimos este producto a nuestro kardex
            $kardex[] = $datosProducto;
        }
        
        return $kardex;
    }
    
    /*=============================================
    EXPORTAR KARDEX A EXCEL
    =============================================*/
    static public function ctrDescargarKardex() {
        if (isset($_GET['fechaInicial']) && isset($_GET['fechaFinal'])) {
            
            $fechaInicial = $_GET['fechaInicial'];
            $fechaFinal = $_GET['fechaFinal'];
            
            $idProducto = null;
            if (isset($_GET['idProducto']) && $_GET['idProducto'] != "0") {
                $idProducto = $_GET['idProducto'];
            }
            
            // Obtener datos del kardex
            $kardex = self::ctrMostrarKardex();
            
            if (empty($kardex)) {
                return "No hay datos disponibles para exportar";
            }
            
            // Crear una instancia de Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Estilo para los encabezados
            $styleHeader = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            
            // Estilo para los datos
            $styleData = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            
            // Estilo para datos numéricos
            $styleNumber = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            
            // Estilo para el saldo inicial
            $styleSaldoInicial = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F8F5'],
                ],
            ];
            
            // Estilo para las filas de entradas
            $styleEntrada = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EBFAEB'],
                ],
            ];
            
            // Estilo para las filas de salidas
            $styleSalida = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFEAEA'],
                ],
            ];
            
            // Fila actual
            $fila = 1;
            
            // Para cada producto, creamos una hoja
            foreach ($kardex as $key => $producto) {
                // Si hay más de un producto, creamos hojas adicionales
                if ($key > 0) {
                    $spreadsheet->createSheet();
                    $spreadsheet->setActiveSheetIndex($key);
                    $sheet = $spreadsheet->getActiveSheet();
                }
                
                // Nombre de la hoja (limitado a 31 caracteres por Excel)
                $nombreHoja = substr('Kardex - ' . $producto['codigo'] . ' ' . $producto['talla'], 0, 31);
                $sheet->setTitle($nombreHoja);
                
                // Encabezado del documento
                $sheet->setCellValue('A1', 'KARDEX DE INVENTARIO - MÉTODO UEPS');
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Información del producto
                $fila = 3;
                $sheet->setCellValue('A' . $fila, 'Producto:');
                $sheet->setCellValue('B' . $fila, $producto['descripcion']);
                $sheet->mergeCells('B' . $fila . ':D' . $fila);
                $sheet->getStyle('A' . $fila)->getFont()->setBold(true);
                
                $fila++;
                $sheet->setCellValue('A' . $fila, 'Código:');
                $sheet->setCellValue('B' . $fila, $producto['codigo']);
                $sheet->getStyle('A' . $fila)->getFont()->setBold(true);
                
                $sheet->setCellValue('C' . $fila, 'Talla:');
                $sheet->setCellValue('D' . $fila, $producto['talla']);
                $sheet->getStyle('C' . $fila)->getFont()->setBold(true);
                
                $fila++;
                $sheet->setCellValue('A' . $fila, 'Período:');
                $sheet->setCellValue('B' . $fila, date('d/m/Y', strtotime($fechaInicial)) . ' al ' . date('d/m/Y', strtotime($fechaFinal)));
                $sheet->mergeCells('B' . $fila . ':D' . $fila);
                $sheet->getStyle('A' . $fila)->getFont()->setBold(true);
                
                $fila += 2;
                
                // Encabezados de la tabla
                $sheet->setCellValue('A' . $fila, 'FECHA');
                $sheet->setCellValue('B' . $fila, 'TIPO');
                $sheet->setCellValue('C' . $fila, 'MOTIVO');
                $sheet->setCellValue('D' . $fila, 'UBICACIÓN');
                
                $sheet->setCellValue('E' . $fila, 'ENTRADAS');
                $sheet->mergeCells('E' . $fila . ':G' . $fila);
                
                $sheet->setCellValue('H' . $fila, 'SALIDAS');
                $sheet->mergeCells('H' . $fila . ':J' . $fila);
                
                $sheet->setCellValue('K' . $fila, 'SALDO');
                $sheet->mergeCells('K' . $fila . ':M' . $fila);
                
                $sheet->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($styleHeader);
                
                $fila++;
                $sheet->setCellValue('E' . $fila, 'CANTIDAD');
                $sheet->setCellValue('F' . $fila, 'COSTO UNIT.');
                $sheet->setCellValue('G' . $fila, 'VALOR TOTAL');
                
                $sheet->setCellValue('H' . $fila, 'CANTIDAD');
                $sheet->setCellValue('I' . $fila, 'COSTO UNIT.');
                $sheet->setCellValue('J' . $fila, 'VALOR TOTAL');
                
                $sheet->setCellValue('K' . $fila, 'CANTIDAD');
                $sheet->setCellValue('L' . $fila, 'COSTO UNIT.');
                $sheet->setCellValue('M' . $fila, 'VALOR TOTAL');
                
                $sheet->getStyle('A' . ($fila-1) . ':M' . $fila)->applyFromArray($styleHeader);
                
                $fila++;
                
                // Saldo inicial
                $sheet->setCellValue('A' . $fila, date('d/m/Y', strtotime($fechaInicial)));
                $sheet->setCellValue('B' . $fila, 'Saldo Inicial');
                $sheet->setCellValue('C' . $fila, '');
                $sheet->setCellValue('D' . $fila, '');
                $sheet->setCellValue('E' . $fila, '');
                $sheet->setCellValue('F' . $fila, '');
                $sheet->setCellValue('G' . $fila, '');
                $sheet->setCellValue('H' . $fila, '');
                $sheet->setCellValue('I' . $fila, '');
                $sheet->setCellValue('J' . $fila, '');
                $sheet->setCellValue('K' . $fila, $producto['saldo_inicial']['cantidad']);
                $sheet->setCellValue('L' . $fila, $producto['saldo_inicial']['costo_unitario']);
                $sheet->setCellValue('M' . $fila, $producto['saldo_inicial']['valor_total']);
                
                $sheet->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($styleData);
                $sheet->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($styleSaldoInicial);
                $sheet->getStyle('E' . $fila . ':M' . $fila)->applyFromArray($styleNumber);
                
                $fila++;
                
                // Movimientos
                foreach ($producto['movimientos'] as $movimiento) {
                    if ($movimiento['tipo'] == 'Saldo Inicial') {
                        // Ya añadimos el saldo inicial anteriormente
                        continue;
                    }
                    
                    $sheet->setCellValue('A' . $fila, date('d/m/Y', strtotime($movimiento['fecha'])));
                    $sheet->setCellValue('B' . $fila, $movimiento['tipo']);
                    $sheet->setCellValue('C' . $fila, $movimiento['motivo']);
                    $sheet->setCellValue('D' . $fila, $movimiento['ubicacion']);
                    
                    // Entrada
                    $sheet->setCellValue('E' . $fila, $movimiento['entrada']['cantidad'] > 0 ? $movimiento['entrada']['cantidad'] : '');
                    $sheet->setCellValue('F' . $fila, $movimiento['entrada']['costo_unitario'] > 0 ? $movimiento['entrada']['costo_unitario'] : '');
                    $sheet->setCellValue('G' . $fila, $movimiento['entrada']['valor_total'] > 0 ? $movimiento['entrada']['valor_total'] : '');
                    
                    // Salida
                    $sheet->setCellValue('H' . $fila, $movimiento['salida']['cantidad'] > 0 ? $movimiento['salida']['cantidad'] : '');
                    $sheet->setCellValue('I' . $fila, $movimiento['salida']['costo_unitario'] > 0 ? $movimiento['salida']['costo_unitario'] : '');
                    $sheet->setCellValue('J' . $fila, $movimiento['salida']['valor_total'] > 0 ? $movimiento['salida']['valor_total'] : '');
                    
                    // Saldo
                    $sheet->setCellValue('K' . $fila, $movimiento['saldo']['cantidad']);
                    $sheet->setCellValue('L' . $fila, $movimiento['saldo']['costo_unitario']);
                    $sheet->setCellValue('M' . $fila, $movimiento['saldo']['valor_total']);
                    
                    // Aplicar estilos según el tipo de movimiento
                    $sheet->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($styleData);
                    $sheet->getStyle('E' . $fila . ':M' . $fila)->applyFromArray($styleNumber);
                    
                    if ($movimiento['entrada']['cantidad'] > 0) {
                        $sheet->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($styleEntrada);
                    } else if ($movimiento['salida']['cantidad'] > 0) {
                        $sheet->getStyle('A' . $fila . ':M' . $fila)->applyFromArray($styleSalida);
                    }
                    
                    $fila++;
                }
                
                // Establecer anchos de columna
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(10);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(12);
                $sheet->getColumnDimension('K')->setWidth(10);
                $sheet->getColumnDimension('L')->setWidth(12);
                $sheet->getColumnDimension('M')->setWidth(12);
                
                // Formato numérico para precios y cantidades
                $lastRow = $fila - 1;
                $sheet->getStyle('E8:M' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            
            // Establecer la primera hoja como activa
            $spreadsheet->setActiveSheetIndex(0);
            
            // Generar el archivo Excel
            $writer = new Xlsx($spreadsheet);
            
            // Nombre del archivo
            $nombreArchivo = 'Kardex_' . date('YmdHis') . '.xlsx';
            
            // Configurar las cabeceras para descargar el archivo
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
            header('Cache-Control: max-age=0');
            
            // Guardar el archivo en salida
            $writer->save('php://output');
            exit;
        }
    }
    
    /*=============================================
    OBTENER PRODUCTOS PARA EL SELECTOR
    =============================================*/
    static public function ctrListarProductos() {
        return ModeloKardex::mdlListarProductos();
    }
}