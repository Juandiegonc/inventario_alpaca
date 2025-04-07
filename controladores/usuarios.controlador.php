<?php

class ControladorUsuarios
{

    /*=============================================
    INGRESO DE USUARIO
    =============================================*/

    static public function ctrIngresoUsuario()
    {

        if (isset($_POST["ingUsuario"])) {

            if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingPassword"])) {

                $encriptar = crypt($_POST["ingPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

                $tabla = "usuarios";

                $item = "usuario";
                $valor = $_POST["ingUsuario"];

                $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

                if ($respuesta["usuario"] == $_POST["ingUsuario"] && $respuesta["password"] == $encriptar) {

                    if ($respuesta["estado"] == 1) {

                        $_SESSION["iniciarSesion"] = "ok";
                        $_SESSION["id"] = $respuesta["id"];
                        $_SESSION["nombre"] = $respuesta["nombre"];
                        $_SESSION["usuario"] = $respuesta["usuario"];
                        $_SESSION["correo"] = $respuesta["correo"];
                        $_SESSION["foto"] = $respuesta["foto"];
                        $_SESSION["perfil"] = $respuesta["perfil"];

                        /*=============================================
                        REGISTRAR FECHA PARA SABER EL ÚLTIMO LOGIN
                        =============================================*/

                        date_default_timezone_set('America/Bogota');

                        $fecha = date('Y-m-d');
                        $hora = date('H:i:s');

                        $fechaActual = $fecha . ' ' . $hora;

                        $item1 = "ultimo_login";
                        $valor1 = $fechaActual;

                        $item2 = "id";
                        $valor2 = $respuesta["id"];

                        $ultimoLogin = ModeloUsuarios::mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2);

                        if ($ultimoLogin == "ok") {

                            echo '<script>

								window.location = "inicio";

							</script>';

                        }

                    } else {

                        echo '<br>
							<div class="alert alert-danger">El usuario aún no está activado</div>';

                    }

                } else {

                    echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';

                }

            }

        }

    }

    /*=============================================
    REGISTRO DE USUARIO
    =============================================*/
    static public function ctrCrearUsuario()
    {
        if (isset($_POST["nuevoUsuario"])) {
            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"]) &&
                filter_var($_POST["nuevoCorreo"], FILTER_VALIDATE_EMAIL)) {
                /*=============================================
                VALIDAR IMAGEN
                =============================================*/
                $ruta = "vistas/img/usuarios/default/anonymous.png"; // Imagen predeterminada

                if (isset($_FILES["nuevaFoto"]["tmp_name"]) && !empty($_FILES["nuevaFoto"]["tmp_name"])) {
                    // Se subió una imagen
                    list($ancho, $alto) = getimagesize($_FILES["nuevaFoto"]["tmp_name"]);
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                    /*=============================================
                    CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
                    =============================================*/
                    $directorio = "vistas/img/usuarios/" . $_POST["nuevoUsuario"];
                    if (!is_dir($directorio)) {
                        mkdir($directorio, 0755, true); // Crear el directorio con permisos
                    }

                    /*=============================================
                    DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
                    =============================================*/
                    if ($_FILES["nuevaFoto"]["type"] == "image/jpeg") {
                        /*=============================================
                        GUARDAMOS LA IMAGEN EN EL DIRECTORIO
                        =============================================*/
                        $aleatorio = mt_rand(100, 999);
                        $ruta = $directorio . "/" . $aleatorio . ".jpg";
                        $origen = imagecreatefromjpeg($_FILES["nuevaFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }
                    if ($_FILES["nuevaFoto"]["type"] == "image/png") {
                        /*=============================================
                        GUARDAMOS LA IMAGEN EN EL DIRECTORIO
                        =============================================*/
                        $aleatorio = mt_rand(100, 999);
                        $ruta = $directorio . "/" . $aleatorio . ".png";
                        $origen = imagecreatefrompng($_FILES["nuevaFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }

                /*=============================================
                GUARDAR USUARIO EN LA BASE DE DATOS
                =============================================*/
                $tabla = "usuarios";
                $encriptar = crypt($_POST["nuevoPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                $datos = array(
                    "nombre" => $_POST["nuevoNombre"],
                    "usuario" => $_POST["nuevoUsuario"],
                    "password" => $encriptar,
                    "correo" => $_POST["nuevoCorreo"],
                    "perfil" => $_POST["nuevoPerfil"],
                    "foto" => $ruta // Guardar la ruta de la imagen (nueva o predeterminada)
                );

                $respuesta = ModeloUsuarios::mdlIngresarUsuario($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                swal({
                    type: "success",
                    title: "¡El usuario ha sido guardado correctamente!",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then(function(result){
                    if(result.value){
                        window.location = "usuarios";
                    }
                });
                </script>';
                }

            } else {
                echo '<script>
            swal({
                type: "error",
                title: "¡El usuario no puede ir vacío o llevar caracteres especiales!",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            }).then(function(result){
                if(result.value){
                    window.location = "usuarios";
                }
            });
            </script>';
            }
        }
    }

    /*=============================================
    MOSTRAR USUARIO
    =============================================*/

    static public function ctrMostrarUsuarios($item, $valor)
    {

        $tabla = "usuarios";

        $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

        return $respuesta;
    }

    /*=============================================
    EDITAR USUARIO
    =============================================*/
    static public function ctrEditarUsuario()
    {
        if (isset($_POST["editarUsuario"])) {
            // Validar el nombre
            if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarNombre"])) {
                // Validar el correo electrónico
                if (!filter_var($_POST["editarCorreo"], FILTER_VALIDATE_EMAIL)) {
                    echo '<script>
                        swal({
                              type: "error",
                              title: "¡El correo electrónico no es válido!",
                              showConfirmButton: true,
                              confirmButtonText: "Cerrar"
                              }).then(function(result) {
                                if (result.value) {
                                window.location = "usuarios";
                                }
                            })
                      </script>';
                    return;
                }

                /*=============================================
                VALIDAR IMAGEN
                =============================================*/
                $ruta = $_POST["fotoActual"];
                if (isset($_FILES["editarFoto"]["tmp_name"]) && !empty($_FILES["editarFoto"]["tmp_name"])) {
                    list($ancho, $alto) = getimagesize($_FILES["editarFoto"]["tmp_name"]);
                    $nuevoAncho = 500;
                    $nuevoAlto = 500;

                    /*=============================================
                    CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
                    =============================================*/
                    $directorio = "vistas/img/usuarios/" . $_POST["editarUsuario"];

                    /*=============================================
                    PRIMERO PREGUNTAMOS SI EXISTE OTRA IMAGEN EN LA BD
                    =============================================*/
                    if (!empty($_POST["fotoActual"])) {
                        unlink($_POST["fotoActual"]);
                    } else {
                        mkdir($directorio, 0755);
                    }

                    /*=============================================
                    DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
                    =============================================*/
                    if ($_FILES["editarFoto"]["type"] == "image/jpeg") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/" . $_POST["editarUsuario"] . "/" . $aleatorio . ".jpg";
                        $origen = imagecreatefromjpeg($_FILES["editarFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagejpeg($destino, $ruta);
                    }
                    if ($_FILES["editarFoto"]["type"] == "image/png") {
                        $aleatorio = mt_rand(100, 999);
                        $ruta = "vistas/img/usuarios/" . $_POST["editarUsuario"] . "/" . $aleatorio . ".png";
                        $origen = imagecreatefrompng($_FILES["editarFoto"]["tmp_name"]);
                        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
                        imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
                        imagepng($destino, $ruta);
                    }
                }

                /*=============================================
                ACTUALIZAR CONTRASEÑA SI SE CAMBIA
                =============================================*/
                $tabla = "usuarios";
                if ($_POST["editarPassword"] != "") {
                    if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["editarPassword"])) {
                        $encriptar = crypt($_POST["editarPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
                    } else {
                        echo '<script>
                            swal({
                                  type: "error",
                                  title: "¡La contraseña no puede ir vacía o llevar caracteres especiales!",
                                  showConfirmButton: true,
                                  confirmButtonText: "Cerrar"
                                  }).then(function(result) {
                                    if (result.value) {
                                    window.location = "usuarios";
                                    }
                                })
                          </script>';
                        return;
                    }
                } else {
                    $encriptar = $_POST["passwordActual"];
                }

                /*=============================================
                PREPARAR DATOS PARA ACTUALIZAR
                =============================================*/
                $datos = array(
                    "nombre" => $_POST["editarNombre"],
                    "usuario" => $_POST["editarUsuario"],
                    "password" => $encriptar,
                    "correo" => $_POST["editarCorreo"],
                    "perfil" => $_POST["editarPerfil"],
                    "foto" => $ruta
                );

                /*=============================================
                LLAMAR AL MODELO PARA EDITAR EL USUARIO
                =============================================*/
                $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        swal({
                              type: "success",
                              title: "El usuario ha sido editado correctamente",
                              showConfirmButton: true,
                              confirmButtonText: "Cerrar"
                              }).then(function(result) {
                                    if (result.value) {
                                    window.location = "usuarios";
                                    }
                                })
                      </script>';
                }

            } else {
                echo '<script>
                    swal({
                          type: "error",
                          title: "¡El nombre no puede ir vacío o llevar caracteres especiales!",
                          showConfirmButton: true,
                          confirmButtonText: "Cerrar"
                          }).then(function(result) {
                            if (result.value) {
                            window.location = "usuarios";
                            }
                        })
                  </script>';
            }
        }
    }

    /*=============================================
    BORRAR USUARIO
    =============================================*/

    static public function ctrBorrarUsuario()
    {

        if (isset($_GET["idUsuario"])) {

            $tabla = "usuarios";
            $datos = $_GET["idUsuario"];

            if ($_GET["fotoUsuario"] != "") {

                unlink($_GET["fotoUsuario"]);
                rmdir('vistas/img/usuarios/' . $_GET["usuario"]);

            }

            $respuesta = ModeloUsuarios::mdlBorrarUsuario($tabla, $datos);

            if ($respuesta == "ok") {

                echo '<script>

				swal({
					  type: "success",
					  title: "El usuario ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result) {
								if (result.value) {

								window.location = "usuarios";

								}
							})

				</script>';

            }

        }

    }
    /*=============================================
  SOLICITUD DE RECUPERACIÓN DE CONTRASEÑA
  =============================================*/
    /*static public function ctrRecuperarContrasena(){
        if(isset($_POST["correoRecuperacion"])){
            // Validar el correo
            if(filter_var($_POST["correoRecuperacion"], FILTER_VALIDATE_EMAIL)){
                $tabla = "usuarios";
                $item = "correo";
                $valor = $_POST["correoRecuperacion"];

                // Buscar el usuario por correo
                $usuario = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);

                if($usuario){
                    // Generar una contraseña temporal
                    $contrasenaTemporal = substr(md5(uniqid(rand(), true)), 0, 8); // Contraseña aleatoria de 8 caracteres
                    $encriptar = crypt($contrasenaTemporal, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');

                    // Actualizar la contraseña en la base de datos
                    $datos = array(
                        "id" => $usuario["id"],
                        "password" => $encriptar
                    );
                    $respuesta = ModeloUsuarios::mdlActualizarContrasenaTemporal($tabla, $datos);

                    if($respuesta == "ok"){
                        // Enviar correo con la contraseña temporal
                        $mensaje = "Tu contraseña temporal es: <strong>$contrasenaTemporal</strong>. Úsala para iniciar sesión y cámbiala después.";
                        $asunto = "Contraseña Temporal";

                        if(self::enviarCorreo($usuario["correo"], $asunto, $mensaje)){
                            echo '<div class="alert alert-success">Se ha enviado una contraseña temporal a tu correo.</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error al enviar el correo. Por favor, intenta nuevamente.</div>';
                        }
                    }
                } else {
                    echo '<div class="alert alert-danger">No existe ningún usuario registrado con ese correo.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Por favor, ingresa un correo válido.</div>';
            }
        }
    }

        /*=============================================
        ENVIAR CORREO CON PHPMAILER
        =============================================*/
    /*static public function enviarCorreo($destinatario, $asunto, $mensaje){
        require 'vendor/autoload.php'; // Asegúrate de incluir el autoload de Composer

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuración del servidor SMTP (usando Gmail)
            $mail->isSMTP();
            $mail->Host = 'p3plzcpnl508720.prod.phx3.secureserver.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'alpacasupport@alpdigi.online'; // Tu correo de Gmail
            $mail->Password = 'ALPACAsoporte890'; // Contraseña o contraseña de aplicación
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('alpacasupport@alpdigi.online', 'Usuario');
            $mail->addAddress($destinatario);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;
            $mail->CharSet = 'UTF-8';

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
            return false;
        }
    }*/


    /*=============================================
SOLICITUD DE RECUPERACIÓN DE CONTRASEÑA CON TOKENS
=============================================*/
    static public function ctrRecuperarContrasena() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correo'])) {
            // Validar el correo
            $email = $_POST['correo'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="alert alert-danger">Por favor, ingresa un correo válido.</div>';
                return;
            }

            try {
                // Conexión a la base de datos
                $pdo = new PDO("mysql:host=localhost;dbname=inventariofinal2", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Verificar si el usuario existe
                $stmt = $pdo->prepare("SELECT id, usuario, correo FROM usuarios WHERE correo = :correo");
                $stmt->execute(['correo' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Generar un token único
                    do {
                        $token = bin2hex(random_bytes(32)); // Token de 64 caracteres
                        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM mdl_password_reset WHERE token = :token");
                        $checkStmt->execute(['token' => $token]);
                        $exists = $checkStmt->fetchColumn();
                    } while ($exists > 0); // Asegurarse de que el token sea único

                    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira en 1 hora

                    // Guardar el token en la base de datos
                    $insertStmt = $pdo->prepare("INSERT INTO mdl_password_reset (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
                    $insertStmt->execute([
                        'user_id' => $user['id'],
                        'token' => $token,
                        'expires_at' => $expiresAt
                    ]);

                    // Construir el enlace de recuperación
                    $resetLink = "http://localhost/inventario_final_prueba/index.php?ruta=cambiar-contraseña&token=$token";

                    // Enviar el correo con el enlace
                    if (self::enviarCorreo($user['correo'], 'Recuperación de Contraseña', "Haz clic en el siguiente enlace para restablecer tu contraseña:\n\n$resetLink\n\nEste enlace expirará en 1 hora.")) {
                        echo '<div class="alert alert-success">Se ha enviado un enlace de recuperación a tu correo.</div>';
                    } else {
                        echo '<div class="alert alert-danger">Error al enviar el correo. Por favor, intenta nuevamente.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">No existe ningún usuario registrado con ese correo.</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error de base de datos: ' . $e->getMessage() . '</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">Error general: ' . $e->getMessage() . '</div>';
            }
        }
    }

    /*=============================================
    ENVIAR CORREO CON PHPMAILER
    =============================================*/
    static public function enviarCorreo($destinatario, $asunto, $mensaje) {
        require 'vendor/autoload.php'; // Incluir el autoload de Composer

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuración del servidor SMTP (usando Gmail o cualquier otro servicio)
            $mail->isSMTP();
            $mail->Host = 'p3plzcpnl508720.prod.phx3.secureserver.net'; // Reemplaza con tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'alpacasupport@alpdigi.online'; // Tu correo
            $mail->Password = 'ALPACAsoporte890'; // Contraseña o contraseña de aplicación
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('alpacasupport@alpdigi.online', 'Soporte Alpaca Clothing');
            $mail->addAddress($destinatario);

            // Contenido del correo
            $mail->isHTML(false); // Establecer a false para texto plano
            $mail->Subject = $asunto;
            $mail->Body    = $mensaje;
            $mail->CharSet = 'UTF-8';

            // Enviar el correo
            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
            return false;
        }
    }


    // MODULO DE  CAMBIAR DE CONTRASEÑA
    public function ctrCambiarcontrasena()
    {
        // Conexión a la base de datos
        try {
            $dbhost = 'localhost';
            $dbname = 'inventariofinal2';
            $dbuser = 'root';
            $dbpass = '';
            $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error de conexión a la base de datos: " . $e->getMessage();
            return false;
        }
    
        // Obtener el token de la URL
        $token = $_GET['token'] ?? null;
    
        // Validar el token
        if (!$token) {
            $_SESSION['mensaje_error'] = "Token no proporcionado.";
            return false;
        }
    
        // Verificar información del token
        try {
            $stmt = $pdo->prepare("SELECT * FROM mdl_password_reset WHERE token = :token");
            $stmt->execute(['token' => $token]);
            $resetData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$resetData) {
                $_SESSION['mensaje_error'] = "Token no encontrado en la base de datos.";
                return false;
            }
    
            // Verificar expiración y estado del token
            if ($resetData['expires_at'] < date('Y-m-d H:i:s')) {
                $_SESSION['mensaje_error'] = "El token ha expirado.";
                return false;
            }
    
            if ($resetData['used'] == 1) {
                $_SESSION['mensaje_error'] = "El token ya ha sido utilizado.";
                return false;
            }
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al verificar el token: " . $e->getMessage();
            return false;
        }
    
        // Procesar el formulario de nueva contraseña
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['password'] ?? '';
    
            // Validar la contraseña
            if (strlen($newPassword) < 8) {
                $_SESSION['mensaje_error'] = "La contraseña debe tener al menos 8 caracteres.";
                return false;
            }
    
            try {
                // Verificar que el usuario existe
                $userStmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
                $userStmt->execute(['id' => $resetData['user_id']]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$userData) {
                    $_SESSION['mensaje_error'] = "Usuario no encontrado.";
                    return false;
                }
                
                // IMPORTANTE: Usar el formato y método de encriptación del sistema actual
                // Este formato funciona con el sistema PrestaShop y otros sistemas similares
                $salt = "asxx54ahjppf45sd87a5a4dDDGsystemdev";
                $hashedPassword = crypt($newPassword, '$2a$07$'.$salt.'$');
    
                // Actualizar la contraseña del usuario
                $updateStmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $updateResult = $updateStmt->execute([
                    'password' => $hashedPassword, 
                    'id' => $resetData['user_id']
                ]);
                
                if (!$updateResult || $updateStmt->rowCount() == 0) {
                    $_SESSION['mensaje_error'] = "No se pudo actualizar la contraseña.";
                    return false;
                }
    
                // Marcar el token como usado
                $markUsedStmt = $pdo->prepare("UPDATE mdl_password_reset SET used = 1 WHERE token = :token");
                $markUsedStmt->execute(['token' => $token]);
    
                // Establecer mensaje de éxito
                $_SESSION['mensaje_exito'] = "Contraseña cambiada exitosamente. Por favor inicie sesión con su nueva contraseña.";
                
                // Redirigir usando JavaScript
                echo "<script>window.location.href = 'login';</script>";
                exit();
                
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = "Error al actualizar la contraseña: " . $e->getMessage();
                return false;
            }
        }
        
        return true;
    }


    
}

