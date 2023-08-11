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

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class UsuariosSucursalController extends Controller
{
    /**
     * Muestra la vista de Asignacion de Sucursal
     * @Route("/UsuariosSucursal", name="UsuariosSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function UsuariosSucursalAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosSucursal")) {
                return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:UsuariosSucursal.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AgregarUsuarioSucursal", name="AgregarUsuarioSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarUsuarioSucursalAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosSucursal")) {
                if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                    $params = [
                        'usuarios'=>$this->ObtenerUsuariosSinAsignacion(),
                        'sucursales'=>$this->ObtenerSucursalesActivas()
                    ];
                    $array = array_merge($params, $session->get('menu'));
                    return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:AgregarUsuarioSucursal.html.twig', $array);
                }
                else {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (isset($datos['sucs_asignar'])) {
                        if ($this->validarInfo($datos)) {
                            $Userid = $session->get('Userid');
                            if(sizeof($datos['sucs_asignar']) == 1) { // Si el arrelo solo contiene un elemento, el arreglo tiene una estructura diferente a cuando contiene mas de 1, por lo que es necesario modificarlo mediante una reasignacion
                                $menus_asignar [] = $datos['sucs_asignar'];
                                $datos['sucs_asignar'] = $menus_asignar;
                            }
                            if($this->AgregarUsuarioSucursal($datos, $Userid))
                                die("1");
                            else
                                die("3");
                        }
                        else {
                            die("3");
                        }
                    }
                    else {
                        die("2");
                    }
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerUsuariosConSucursalesAsignadas", name="ObtenerUsuariosConSucursalesAsignadas")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerUsuariosConSucursalesAsignadasAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosSucursal")) {
                $params = [
                    'info_usrs'=>$this->ObtenerUsuariosConSucursalesAsignadas()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:TablaUsuariosSucursales.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * Muestra la vista ReasignarSucursal
     * @Route("/ModificarUsuarioSucursal", name="ModificarUsuarioSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarUsuarioSucursalAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosSucursal")) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['id'])) {
                        $idusr = $_POST['id'];
                        $params = [
                            'usuario'=>$this->ObtenerSucursalesUsuario($idusr),
                            'sucursales'=>$this->ObtenerSucursalesDisponiblesUsuario($idusr)
                        ];
                        $array = array_merge($params, $session->get('menu'));
                        return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:ModificarUsuarioSucursal.html.twig', $array);
                    }
                    else {
                        $getdata = new DefaultController();
                        $datos = $getdata->getData($request);
                        if (isset($datos['sucs_asignar'])) {
                            $Userid = $session->get('Userid');
                            if(sizeof($datos['sucs_asignar']) == 1) {
                                $sucs_asignar [] = $datos['sucs_asignar'];
                                $datos['sucs_asignar'] = $sucs_asignar;
                            }
                            $this->ModificarUsuarioSucursal($datos, $Userid);
                            die("1");
                        }
                        else
                            die("2");
                    }
                } else return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:UsuariosSucursal.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ActivarDesactivarAccionSucursal", name="ActivarDesactivarAccionSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarAccionSucursalAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosSucursal")) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    $Userid = $session->get('Userid');
                    if (isset($datos['ids_sucs'])) {
                        if ($this->ActivarDesactivarTodo($datos, $Userid))
                            die("1");
                    }
                    else {
                        if($this->ActivarDesactivarAccionSucursal($datos, $Userid))
                            die("1");
                    }
                } else return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:UsuariosSucursal.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/EliminarRelacionUsuarioSucursal", name="EliminarRelacionUsuarioSucursal")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function EliminarRelacionUsuarioSucursalAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosSucursal")) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $idusr = $_POST['idusr'];
                    $Userid = $session->get('Userid');
                    if ($this->EliminarRelacionUsuarioSucursal($idusr, $Userid))
                        die("1");
                    else
                        die("2");
                } else return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosSucursal:UsuariosSucursal.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /* ------------------FUNCIONES-------------------- */

    public function ObtenerUsuariosConSucursalesAsignadas() {
        $conn = new Model();
        $sql = "SELECT B.idUsr, nomusr, B.id_sucursal, sucursal, nombre, captura, consulta
                FROM sis_usr A INNER JOIN sis_usrrol B ON A.idUsr=B.idUsr INNER JOIN sucursales C ON B.id_sucursal=C.id_sucursal
                WHERE C.activa=1
                ORDER BY B.idUsr;"; // Al cambiar el orden en esta consulta se altera la vista que contiene la libreria datatable para este listado
        $info_usrs = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $info_usrs;
    }
    
    public function ObtenerSucursalesUsuario($idusr) {
        $conn = new Model();
        $sql = "SELECT B.idUsr, nomusr, B.id_sucursal, sucursal, nombre, captura, consulta
                FROM sis_usr A INNER JOIN sis_usrrol B ON A.idUsr=B.idUsr INNER JOIN sucursales C ON B.id_sucursal=C.id_sucursal
                WHERE C.activa=1 AND B.idUsr=$idusr
                ORDER BY nombre;";
        $info_usr = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $info_usr;
    }
    
    public function ObtenerUsuariosSinAsignacion() {
        $conn = new Model();
        $sql = "SELECT A.idUsr, nomusr
                FROM sis_usr A LEFT OUTER JOIN sis_usrrol B ON A.idUsr = B.idUsr
                WHERE B.idUsr IS NULL
                ORDER BY nomusr";
        $usuarios = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $usuarios;
    }
    

    public function ObtenerSucursalesActivas() {
        $conn = new Model();
        $sql = "SELECT id_sucursal, sucursal, nombre FROM sucursales WHERE activa=1 ORDER BY nombre;";
        $sucursales = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $sucursales;
    }
    
    public function ObtenerSucursalesDisponiblesUsuario($idusr) {
        $conn = new Model();
        $sql = "SELECT id_sucursal, sucursal, nombre
                FROM sucursales
                WHERE activa=1 AND id_sucursal NOT IN (SELECT A.id_sucursal
                                                        FROM sis_usrrol A INNER JOIN sucursales B ON A.id_sucursal=B.id_sucursal
                                                        WHERE idUsr=$idusr AND B.activa=1)
                ORDER BY nombre;";
        $sucursales = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $sucursales;
    }
    
    public function AgregarUsuarioSucursal($datos, $Userid) {
        $conn = new Model();
        $result = $this->AgregarRelacionSucursal($datos['usuario'], $datos['sucs_asignar'], $conn);
        if ($result) {
            $detalle = "Agregó sucursal(es): ";
            $noms_sucs = $this->obtenerNombresSucursales($conn, $datos['sucs_asignar']);
            $nomusr = $this->ObtenerNombreUsuario($datos['usuario'], $conn);
            $detalle .= (isset($datos['detalle'])) ? $noms_sucs.$datos['detalle'] : $noms_sucs.", al usuario: ".$nomusr;
            $accion = "Agregar asignación usuario sucursal";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function ModificarUsuarioSucursal($datos, $Userid) {
        // Obtener las sucursales asignadas del usuario
        $conn = new Model();
        $sql = "SELECT id_sucursal FROM sis_usrrol WHERE idUsr=".$datos['usuario'];
        $result = $conn->query($sql);
        $sucs_asignadas = [];
        while($row = $conn->fetch_array($result)) {
            $sucs_asignadas[] = $row['id_sucursal'];
        }
        
        $sucs_asignar = [];
            // Ciclo for compara la sucursales asignadas ($sucs_asignadas) contra las sucursales por asignar ($datos['sucs_asignar']) y almacena la diferencia en $sucs_asignar
            // Se realiza esta tarea antes de evaluar si hay menus por eliminar porque en este punto debe haber menus por asignar 
        for ($i = 0; $i < sizeof($datos['sucs_asignar']); $i++) {
            if (!in_array($datos['sucs_asignar'][$i], $sucs_asignadas)) 
                $sucs_asignar [] = $datos['sucs_asignar'][$i];
        }
        // Si el usuario no asigno todas las sucursales, se verifica si hay sucursales por quitar
        if (isset($datos['sucs_disp'])) { 
            $nomusr = $this->ObtenerNombreUsuario($datos['usuario'], $conn);
            $sucs_quitar = [];
            // Ciclo for compara las sucursales asignadas ($sucs_asignadas) contra las sucursales disponibles ($datos['sucs_disp']) y almacena la diferencia en $sucs_quitar
            for ($i = 0;$i < sizeof($datos['sucs_disp']); $i++) {
                if (in_array($datos['sucs_disp'][$i], $sucs_asignadas)) 
                    $sucs_quitar [] = $datos['sucs_disp'][$i];
            }
            
            if($sucs_asignar && $sucs_quitar) { // Asignar y Eliminar
                $this->QuitarSucursalesUsuario($datos['usuario'], $sucs_quitar);
                $nombres = $this->obtenerNombresSucursales($conn, $sucs_quitar); // Sucursales que se quitaron
                $detalle = "y quitó sucursal(es): ".$nombres.", al usuario: ".$nomusr;
                $datos['detalle'] = $detalle;
                $datos['sucs_asignar'] = $sucs_asignar;
                return $this->AgregarUsuarioSucursal($datos, $Userid);
            }
            
            if(!$sucs_asignar && $sucs_quitar) { // Eliminar
                $this->QuitarSucursalesUsuario($datos['usuario'], $sucs_quitar);
                $accion = "Quitar asignacion usuario sucursal";
                $nombres = $this->obtenerNombresSucursales($conn, $sucs_quitar);
                $detalle = "Quitó sucursal(es): ".$nombres.", al usuario: ".$nomusr;
                $this->registrarBitacora($accion, $detalle, $Userid, $conn);
                $conn->close();
                return 1;
            }
        }
        $datos['sucs_asignar'] = $sucs_asignar;
        return $this->AgregarUsuarioSucursal($datos, $Userid);
    }
    
    public function ActivarDesactivarAccionSucursal ($datos, $Userid) {
        $conn = new Model();
        $sql = "UPDATE sis_usrrol SET ".$datos['campo']."=".$datos['valor']." WHERE idUsr=".$datos['idusr']." AND id_sucursal=".$datos['idsuc'].";";
        $result = $conn->query($sql);
        $nomusr = $this->ObtenerNombreUsuario($datos['idusr'], $conn);
        $nomsuc = $this->obtenerNombresSucursales($conn, $datos['idsuc']);
        if ($datos['valor'] == 1) {
            $accion = "Activar acción de sucursal";
            //$detalle="Activó la acción ".$datos['accion']." de la sucursal: ".$datos['nomsuc']." para el usuario: $nomusr";
            $detalle = "Activó la acción ".$datos['accion']." de la sucursal: ".$nomsuc."para el usuario: $nomusr";
        }
        else {
            $accion = "Desactivar acción de sucursal";
            //$detalle="Desactivó la acción ".$datos['accion']." de la sucursal: ".$datos['nomsuc']." para el usuario: $nomusr";
            $detalle = "Desactivó la acción ".$datos['accion']." de la sucursal: ".$nomsuc."para el usuario: $nomusr";
        }
        $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        $conn->close();
        return $result;
    }
    
    public function ActivarDesactivarTodo($datos, $Userid) {
        $conn = new Model();
        $sql = "UPDATE sis_usrrol SET ".$datos['campo']."=".$datos['valor']." WHERE idUsr=".$datos['id_usr'].";";
        $result = $conn->query($sql);
        $nomusr = $this->ObtenerNombreUsuario($datos['id_usr'], $conn);
        if ($datos['valor'] == 1) {
            $accion = "Activar acción de sucursal";
            $detalle = "Activó acción ".$datos['accion'];
        }
        else {
            $accion = "Desactivar acción de sucursal";
            $detalle = "Desactivó acción ".$datos['accion'];
        }
        $nombres = $this->obtenerNombresSucursales($conn, $datos['ids_sucs']);
        $detalle .= " de la(s) sucursal(es): ".$nombres.", para el usuario: $nomusr";
        $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        $conn->close();
        return $result;
    }
    
    public function EliminarRelacionUsuarioSucursal ($idusr, $Userid) {
        $conn = new Model();
        $nomusr = $this->ObtenerNombreUsuario($idusr, $conn);
        $sql = "DELETE FROM sis_usrrol WHERE idUsr=$idusr";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Eliminar relación usuario-sucursal";
            $detalle = "Se eliminó toda la relación usuario-sucursal del usuario: $nomusr";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }

    public function AgregarRelacionSucursal($idusr, $sucursales, $conn) {
        for ($i = 0; $i < sizeof($sucursales); $i++) {
            $sql = "INSERT INTO sis_usrrol(idUsr, id_sucursal, captura, consulta) VALUES($idusr, ".$sucursales[$i].", 0, 0)";
            $result = $conn->query($sql);
            if ($conn->num_rows($result) == 0)
                return 0;
        }
        return 1;
    }
    
    public function QuitarSucursalesUsuario($idusr, $sucursales) {
        $conn = new Model();
        for ($i = 0; $i < sizeof($sucursales); $i++) {
            $sql = "DELETE FROM sis_usrrol WHERE idUsr=$idusr AND id_sucursal=".$sucursales[$i];
            $conn->query($sql); 
        }
    }
    
    public function ObtenerNombreUsuario($idusr, $conn) {
        $sql_select="SELECT nomusr FROM sis_usr WHERE idUsr=$idusr";
        $result = $conn->query($sql_select);
        $nomusr = utf8_encode($conn->fetch_array($result)['nomusr']);
        return $nomusr;
    }
    
    public function validarInfo($datos) {
        if (!isset($datos['usuario']))
            return 0; //No Valido
        
        // Validar enteros (sucursales)
        $sucursales = $datos['sucs_asignar'];
        for ($i = 0; $i < sizeof($sucursales); $i++) {
            if (!ctype_digit($sucursales[$i]))
                return 0;
        }
        
        // Validar entero (idUsr)
        if (!ctype_digit($datos['usuario']))
            return 0;
        
        return 1;
    }
    
    public function registrarBitacora($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('UsuariosSucursal');
        $sqlbitacora = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
        $conn->query($sqlbitacora);
    }
    
    public function setCodingUtf8($sql, $conn) {
        $r = $conn->query($sql);
        $array = [];
        while ($row = $conn->fetch_array($r)) {
            $keys = array_keys($row);
            for ($i = 0; $i < sizeof($row); $i++) {
                $a[$keys[$i]] = utf8_encode($row[$keys[$i]]);
            }
            $array [] = $a;
        }
        return $array;
    }
    
    public function obtenerNombresSucursales ($conn, $sucursales) {
        $nombres = "";
        if (!is_array($sucursales) == 1)
            $ids[] = $sucursales;
        else
            $ids = $sucursales;
        for ($i = 0; $i < sizeof($sucursales); $i++) {
            $sql="SELECT sucursal, nombre FROM sucursales WHERE id_sucursal=".$ids[$i];
            $result = $conn->query($sql);
            $row = $conn->fetch_array($result);
            $nombres .= "--".$row['sucursal']."-".utf8_encode($row['nombre'])." ";
        }
        return $nombres;
    }
}