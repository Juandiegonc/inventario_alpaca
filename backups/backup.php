<?php
// Configuración de la base de datos
$host = "localhost"; // Puede ser diferente según tu hosting
$usuario = "juanuc"; // Usuario de la base de datos
$password = "ALPACAct900"; // Contraseña de la base de datos
$base_datos = "inventariofinal"; // Nombre de la base de datos

// Carpeta donde se guardará el backup
$backup_dir = __DIR__ . "/backups";
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0777, true); // Crea la carpeta si no existe
}

// Nombre del archivo de backup con fecha
$fecha = date("Y-m-d_H-i-s");
$archivo_backup = "$backup_dir/backup_$fecha.sql";

// Comando para generar el backup
$comando = "mysqldump --user=$usuario --password=$password --host=$host $base_datos > $archivo_backup";

// Ejecutar el comando
exec($comando, $output, $resultado);

// Verificar si se generó correctamente
if ($resultado === 0) {
    echo "✅ Backup creado: $archivo_backup";
} else {
    echo "❌ Error al generar el backup";
}
?>
