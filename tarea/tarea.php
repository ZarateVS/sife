<?php

	include_once 'C:/inetpub/wwwroot/sife/vendor/autoload.php';
	include_once 'C:/inetpub/wwwroot/sife/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

    $dns      = 'CNN_FE';
    $usuario  = "";
    $password = "";
    $conexion = "";

    if($conexion == ""){
        $conexion = (odbc_connect($dns, $usuario, $password)) or die ('En Mantenimiento...');
        if ($conexion) {
        	$certificados = ObtenerInfoCertificadosAVencer($conexion);
        	if ($certificados) {
        		for ($i = 0; $i < count($certificados); $i++) {
					for ($k = 0; $k < count($certificados[$i]['correos']); $k++) {
						if (EnviarEmail($certificados[$i]['correos'][$k], $certificados[$i]['vistas'][$k]))
							echo 'Enviado';
						else
							echo 'Fallo envio de correos';
        			}
        		}
        	}
            else {
                echo 'No hay certificados a vencer';
                exit();
            }

        	odbc_close($conexion);
        }
        else {
            echo 'No se pudo conectar';
        	exit();
        }
    }
    else {
    	echo 'exit';
        exit();
    }

    function fechas (){
        $f_act = '05-02-2018'; // 05-02-2018
        $f_vig = '19-12-2018'; // 19-12-2018
        $f_env = '19-11-2018'; // 19-11-2018
        $dia = 86400;
        while ($f_act != $f_vig) {
            $plazo_vig = (strtotime($f_vig) - strtotime($f_env)) /2;
            $plazo_actual = strtotime($f_vig) - strtotime($f_act);
            $a [] = $f_act .' - '.$plazo_actual;
            $f_act = date("d-m-Y", strtotime($f_act) + $dia);
        }
        return $a;
    }

    function ObtenerInfoCertificadosAVencer($conexion) {
        $sql = "SELECT A.idcertificado, no_cert, vig_fin, C.mail, B.correoE, B.razonsocial
				FROM certificado A INNER JOIN clientes B ON A.id_cliente=B.id_cliente INNER JOIN sis_usr C ON A.usr_crea=C.idUsr;";
        $result = query($conexion, $sql);
        $fch_actual = date('Y-m-d');
        $certificados = [];
        while ($row = fetch_array($result)) {
        	$fch_vigencia = $row['vig_fin'];
			$fch_alerta_1 = generarFechaAlerta1($fch_vigencia);
            $dias_formato_unix = 604800; // Equivale a 7 dias en formato UNIX
            $plazo_actual = (strtotime($fch_vigencia) - strtotime($fch_actual));

            if (strtotime($fch_actual) == strtotime($fch_alerta_1)) {
                $certificados [] = prepararDatosEnvioCorreo($row, '1 mes');
            }

            if ($plazo_actual == ($dias_formato_unix * 3)) {
                $certificados [] = prepararDatosEnvioCorreo($row, '21 dias');
            }

            if ($plazo_actual == ($dias_formato_unix * 2)) {
                $certificados [] = prepararDatosEnvioCorreo($row, '14 dias');
            }

            if ($plazo_actual == $dias_formato_unix) {
                $certificados [] = prepararDatosEnvioCorreo($row, '7 dias');
            }
        }
        return $certificados;
    }

    function prepararDatosEnvioCorreo ($row, $tiempo) {
        $mensaje1 = "El certificado con numero de serie ".$row['no_cert']." correspondiente a la empresa ".$row['razonsocial']." vencera en ".$tiempo;
        $mensaje2 = "Su certificado con numero de serie ".$row['no_cert']." vencera en ".$tiempo;
        $certificado = array(
                'idcertificado' => $row['idcertificado'],
                'no_cert' => $row['no_cert'],
                'correos' => array($row['mail'], 'salvador.zarate@kurigage.com', $row['correoE']),
                'vistas' => array($mensaje1, $mensaje1, $mensaje2)
        );
        return $certificado;
    }

    function fetch_array($consulta){
        if( !$consulta ){
            return 0;
        }
        return odbc_fetch_array($consulta);
    }

    function query($conexion, $consulta){
        $resultado = odbc_exec($conexion, utf8_decode($consulta));
        if( $resultado === false ){
            return 0;
        }
        return $resultado;
    }

    function generarFechaAlerta1($fch_vigencia) {
        $fch_vigencia = explode('-', $fch_vigencia);
        $año_vig = $fch_vigencia[0];
        $mes_vig = $fch_vigencia[1];
        $dia_vig = $fch_vigencia[2];
        
        $año = $año_vig;
        $mes = "";
        $dia = $dia_vig;
        
        switch ($mes_vig) {
            case 5: case 7: case 10: case 12:
                if ($dia_vig == 31)
                    $dia = 30;
                break;
            case 3:
                if ($dia_vig == 29 || $dia_vig == 30 || $dia_vig == 31){
                    if($año_vig%4 == 0)
                        $dia = 29;
                    else
                        $dia = 28;
                }
                break;
        }
        
        if ($mes_vig == 1) {
            $mes = 12;
            $año = $año_vig - 1;
        }
        else {
            $mes = $mes_vig - 1;
        }
        
        $fch_envio = "$año-$mes-$dia";
        return $fch_envio;
    }

    function EnviarEmail($correo, $vista){
        $transport = Swift_SmtpTransport::newInstance('smtp.live.com', 587, 'tls')
                  ->setUsername('kurigage24@hotmail.com')
                  ->setPassword('pruebasKuri');
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance('prueba')
                  ->setSubject('SIFE')
                  ->setFrom('kurigage24@hotmail.com')
                  ->setTo($correo)
                  ->setBody($vista);
                  //->setBody($this->renderView($vista, $datos), 'text/html');
        
        return $mailer->send($message);
    }
?>