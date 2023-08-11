<?php

namespace FacelecBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use FacelecBundle\Controller\GenerarMenuController;
use FacelecBundle\Model\ModelCorporativo;
use FacelecBundle\Model\Model;

class DefaultController extends Controller
{
    /**
     * Muestra pantalla de login al SIIF
     * Se ejecuta al:
     * Accesar al SIIF a través de la ruta "/"
     * Realiza:
     * Si el mmétodo de petición es POST
     * 1.- Obtiene usuario enviado desde ajax a través de la vista "Login.html.twig"
     *     separa el nombre de usuario por el simbolo @
     * 2.- Ejecuta función para validar el dominio ingresado por el usuario
     * 3.- Ejecuta función para obtener el identificador del corporativo y el DSN de conexión
     * 4.- Inicia la sesión y establece variables de sesión
     *   1.- nombrecorporativo: Nombre corto del corporativo actual
     *   2.- corporativo: ID del corporativo actual
     *   3.- dsn: Fuente de datos a la que se conectará
     * 4.- Establece en sesión el nombre corto del dominio actual
     * 5.- Verifica que los datos de usuario y contraseña sean validos para accesar al sistema y establece variables de sesión
     *   1.- Username: Nombre de usuario que acceso al SIIF
     *   2.- Userid: ID del usuario que acceso al SIIF
     * 6.- Realiza registro en bitacora
     * 7.- Devuelve una respuesta a ajax a través de la cual muestra un mensaje al usuario
     * De lo contrario
     * 1.- Muestra en pantalla la vista del archivo "Login.html.twig"
     * Funciones que ejecuta:
     * 1.- validadominio($corporativo): Verifica que el dominio ingresado por el usuario sea correcto
     *     $corporativo: Nombre del corporativo al que pertenece el usuario
     * @Route("/", name="Login")
     * @param SessionInterface $session Gestiona la sesión
     * @return Response
     */
    public function indexAction(SessionInterface $session)
    {
        $opc = ""; $row = "";
        if($_SERVER['REQUEST_METHOD'] === 'POST') {//Metodo de peticion para acceder a la pagina
            $val = explode('@', $_POST['usuario']);//explode:devuelve un arreglo
            $corporativo = strtoupper($val[1]);//strtoupper--Convierte una cadena en mayuculas
            $cnn = new ModelCorporativo();//CRea una instancia de esta clase
            $sql = "CALL rtACCDet('$corporativo');";
            $res = $cnn->query($sql);
            if($row = $cnn->fetch_array($res)) {
                $session->start();
                $session->set('corporativo', $row['corporativo']);
                $session->set('dsn', $row['dsn1']);
                $session->set('web', $this->get('kernel')->getWebDir());
                $usuario = $val[0];
                $password = $_POST['password'];
                $conn = new Model();//crea una instancia de la clase Model
                $sql = "Select * from sis_usr where usr = '". $usuario ."' and pass = '". md5($password) ."' and activo = 1";
                $res = $conn->query($sql);
                if($row = $conn->fetch_array($res)) {
                    $session->set('Username', $row['nomusr']);
                    $session->set('Userid', $row['idUsr']);
                    $Usuarios_id = $session->get('Userid');
                    $Nombre = $session->get('Username');
                    $detalle = "Inicio sesión el usuario: $Nombre";
                    $accion = "Inicio de sesión";
                    $sqlbitacora = "Insert into sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) values ($Usuarios_id, NULL, now(), '$accion' , '$detalle');";
                    $conn->query($sqlbitacora);
                    $conn->close();
                    $opc = "1";
                } else {
                    $opc = "0";
                }    
            } else {
                $opc = "0";
            }
            $cnn->close();
            die($opc);
        }
        return $this->render('FacelecBundle:Default:Login.html.twig');
    }

    /**
     * Muestra la pantalla de inicio del SIIF
     * Realiza:
     * 1.- Verifica que el usuario este logueado
     * 2.- Consulta los permisos que el usuario tiene sobre el sistema y lo retorna en un arreglo
     * 3.- Muestra en pantalla la vista del archivo "layout.html.twig"
     * Funciones que ejecuta:
     * 1.- Generamenu($Usuarios_id, $Usuarios_nombre): Obtiene un arreglo con los permisos que el usuario posee sobre el SIIF
     *     $Usuarios_id: ID del usuario que acceso al SIIF
     *     $Usuarios_nombre: Nombre de usuario del usuario que acceso al SIIF
     * @Route("/Inicio", name="Inicio")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function InicioAction(SessionInterface $session){
        if($session->has("Userid")) {
            $menu = new GenerarMenuController();//Crea instancia de la clase GenerarMenuController
            $Usuarios_id = $session->get('Userid');
            $Usuarios_nombre = $session->get('Username');
            return $this->render('FacelecBundle:Default:layout.html.twig', $menu->Generamenu($Usuarios_id, $Usuarios_nombre));
        } else return $this->redirect($this->generateUrl('Login'));
    }

    /**
     * Finaliza la sesión actual
     * Realiza:
     * 1.- Realiza un registro en bitacora de la acción
     * 2.- Destruye la sesión
     * 3.- Redirige a la ruta "/"
     * Sale de la sesión actual destruyendo las variables de sesión inicializadas y registra en bitacora
     * @Route("/Salir", name="Salir")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function SalirAction(SessionInterface $session){
        $conn = new Model();
        $Usuarios_id = $session->get('Userid');
        $Usuarios_nombre = $session->get('Username');
        $detalle = "Cerró sesión el usuario: $Usuarios_nombre";
        $accion = "Cerrar sesión";
        $sqlbitacora = "Insert into sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) values ($Usuarios_id, NULL, now(), '$accion', '$detalle');";
        $conn->query($sqlbitacora);
        $session->invalidate();
        return $this->redirect($this->generateUrl('Login'));
    }

    /**
     * Muestra en pantalla imagen de permisos insuficientes
     * @Route("Alerta", name="Alerta")
     * @param SessionInterface $session Gestiona la sesión
     * @return Response
     */
    public function AlertaAction(SessionInterface $session){
        return $this->render('FacelecBundle:Default:Alerta1.html.twig', $session->get('menu'));
    }

    /**
     * Registra un movimiento de consulta en el menu del SIIF
     * Realiza:
     * 1.- Valida que el usuario este logueado
     * 2.- OBtiene el id del menu al que el usuario quiere accesar
     * 3.- Registra en la base de datos
     * @Route("Bitacora_Consulta", name="Bitacora_Consulta")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function BitacoraConsultaAction(SessionInterface $session){
        if($session->has("Userid")) {
            $conn = new Model();
            $detalle = 'Consultó: '.$_POST['menu'];
            $idmnu = $_POST['id'];
            $href = $_POST['href'];
            $Usuarios_id = $session->get('Userid');
            $accion = "Consulta";
            $sqlbitacora = "Insert into sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) values ($Usuarios_id, $idmnu, now(), '$accion', '$detalle');";
            $conn->query($sqlbitacora);
            //return $this->redirect($this->generateUrl($href));
            die("Exito");
        } else return $this->redirect($this->generateUrl('Login'));
    }

    /**
     * Obtiene los parametros enviados desde ajax y los convierte en arreglo
     * @param Request $request Obtiene
     * @return mixed arreglo de parametros
     */
    public function getData(Request $request){
        $errores = '';
        $params = json_decode($request->getContent(), true);
        return $params;
    }

    public function log($error){
        $sesion = new Session();
        $web = $sesion->get('web');
        $carpeta = $web."log";
        $archivo = $carpeta."/USERS_LOG.txt";
        $time = date("Y-m-d H:i:s");
        $registro = $time ." - ". $error;
        if (file_exists($carpeta)) {
            $file = fopen($archivo, "a");
            fwrite($file, $registro . PHP_EOL);
            fclose($file);
        } else {
            if(mkdir($carpeta, 0777, true)) {
                $file = fopen($archivo, "a");
                fwrite($file, $registro . PHP_EOL);
                fclose($file);
            } else {
                die('Fallo al crear las carpetas...');
            }
        }
    }

    public function webdir(){
        return $this->get('kernel')->getWebDir();
    }

    /**
     * Devuelve arreglo UTF-8
     * Convierte un arreglo de  parametros recibidos a UTF-8
     * Es ejecutada por:
     *
     * @param $params arreglo de datos a codificar
     * @return array arreglo con datos preparados
     */
    public function utf8convert($params){
        $array = [];
        $json = ['valorid' => '', 'valornombre' => ''];
        $i = 0;
        foreach ($params as $param){
            $json['valorid'] = utf8_encode($param['valorid']);
            $json['valornombre'] = utf8_encode($param['valornombre']);
            $array[$i] = $json;
            $i++;
        }
        return $array;
    }
}
