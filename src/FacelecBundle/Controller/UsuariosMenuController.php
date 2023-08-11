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

class UsuariosMenuController extends Controller
{
    /**
     * @Route("/UsuariosMenu", name="UsuariosMenu")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function UsuariosMenuAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosMenu")) {
                $menu = new GenerarMenuController();//Crea instancia de la clase GenerarMenuController
                $Usuarios_id = $session->get('Userid');
                $Usuarios_nombre = $session->get('Username');
                $mnu = $menu->Generamenu($Usuarios_id, $Usuarios_nombre);
                return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:UsuariosMenu.html.twig', $mnu);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/AgregarUsuarioMenu", name="AgregarUsuarioMenu")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function AgregarUsuarioMenuAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosMenu")) {
                if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                    $params = [
                        'usuarios'=>$this->ObtenerUsuariosSinAsignacion(),
                        'menus'=>$this->ObtenerMenusDisponiblesUsuarios()
                    ];
                    $array = array_merge($params, $session->get('menu'));
                    return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:AgregarUsuarioMenu.html.twig', $array);
                }
                else {
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if (isset($datos['menus_asignar'])) {
                        $Userid = $session->get('Userid');
                        if(sizeof($datos['menus_asignar']) == 1) {
                            $menus_asignar [] = $datos['menus_asignar'];
                            $datos['menus_asignar'] = $menus_asignar;
                        }
                        if($this->AgregarUsuarioMenu($datos, $Userid)) {
                            $this->redirect($this->generateUrl('Inicio'));
                            die("1"); // Operación completa
                        }
                        else
                            die("3"); // No se pudo realizar la operación
                    }
                    else {
                        die("2"); // Se debe asignar un menú como mínimo para guardar
                    }
                }
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ObtenerUsuariosConMenusAsignados", name="ObtenerUsuariosConMenusAsignados")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ObtenerUsuariosConMenusAsignadosAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosMenu")) {
                $params = [
                    'info_usrs'=>$this->ObtenerUsuariosConMenusAsignados()
                ];
                $array = array_merge($params, $session->get('menu'));
                return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:TablaUsuariosMenu.html.twig', $array);
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * Muestra la vista ReasignarMenu
     * @Route("/ModificarUsuarioMenu", name="ModificarUsuarioMenu")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ModificarUsuarioMenuAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosMenu")) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['id'])) {
                        $idusr = $_POST['id'];
                        $params = [
                            'usuario'=>$this->ObtenerMenusUsuario($idusr),
                            'menus'=>$this->ObtenerMenusDisponiblesUsuario($idusr)
                        ];
                        $array = array_merge($params, $session->get('menu'));
                        return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:ModificarUsuarioMenu.html.twig', $array);
                    }
                    else {
                        $getdata = new DefaultController();
                        $datos = $getdata->getData($request);
                        if (isset($datos['menus_asignar'])) {
                            $Userid = $session->get('Userid');
                            if(sizeof($datos['menus_asignar']) == 1) {
                                $menus_asignar [] = $datos['menus_asignar'];
                                $datos['menus_asignar'] = $menus_asignar;
                            }
                            if ($this->ModificarUsuarioMenu($datos, $Userid))
                                die("1"); // Operación completa
                            else
                                die("3"); // No se pudo realizar la operación
                        }
                        else {
                            die("2"); // Se debe asignar un menú como mínimo para guardar
                        }
                    }
                } else return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:UsuariosMenu.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/ActivarDesactivarAccionMenu", name="ActivarDesactivarAccionMenu")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function ActivarDesactivarAccionMenuAction(SessionInterface $session, Request $request) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosMenu")) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $Userid = $session->get('Userid');
                    $getdata = new DefaultController();
                    $datos = $getdata->getData($request);
                    if($this->ActivarDesactivarAccionMenu($datos, $Userid)) {
                        $menu = new GenerarMenuController();//Crea instancia de la clase GenerarMenuController
                        $Usuarios_id = $session->get('Userid');
                        $Usuarios_nombre = $session->get('Username');
                        $menu->Generamenu($Usuarios_id, $Usuarios_nombre);
                        die("1"); // Operación completa
                    }
                    else
                        die("2"); // No se pudo realizar la operación
                } else return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:UsuariosMenu.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /**
     * @Route("/EliminarRelacionUsuarioMenu", name="EliminarRelacionUsuarioMenu")
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function EliminarRelacionUsuarioMenuAction(SessionInterface $session) {
        if ($session->has("Userid")) {
            if ($session->has("UsuariosMenu")) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $idusr = $_POST['idusr'];
                    $Userid = $session->get('Userid');
                    if ($this->EliminarRelacionUsuarioMenu($idusr, $Userid))
                        die("1"); // Operación completa
                    else
                        die("2"); // No se pudo realizar la operación
                } else return $this->render('FacelecBundle:Catalogos/Sistema/UsuariosMenu:UsuariosMenu.html.twig', $session->get('menu'));
            } else return $this->render('FacelecBundle:Default:layout.html.twig', $session->get('menu'));
        } else return $this->redirect($this->generateUrl('Login'));
    }
    
    /* ------------------FUNCIONES SQL-------------------- */

    /**
     * Obtiene la información de los usuarios de la BD
     * @return $info
     */
    public function ObtenerUsuariosConMenusAsignados() {
        $conn = new Model();
        $sql = "SELECT A.idUsr, A.nomusr, C.mnu_id, C.mnu_nom, rol_insert, rol_update, rol_delete, r_insert, r_update, r_delete
                FROM sis_usr A INNER JOIN sis_usrmnu B ON A.idUsr = B.idUsr INNER JOIN sis_mnu C ON B.mnu_id = C.mnu_id
                WHERE A.activo = 1 AND C.activo = 1";
        $info_usrs = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $info_usrs;
    }
    
    public function ObtenerMenusUsuario($idusr) {
        $conn = new Model();
        $sql = "SELECT A.idUsr, A.nomusr, C.mnu_nom, C.mnu_id, rol_insert, rol_update, rol_delete, r_insert, r_update, r_delete
                FROM sis_usr A INNER JOIN sis_usrmnu B ON A.idUsr = B.idUsr INNER JOIN sis_mnu C ON B.mnu_id = C.mnu_id
                WHERE A.activo = 1 AND C.activo = 1 AND B.idUsr = $idusr";
        $info_usr = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $info_usr;
    }
    
    /**
     * Obtiene los usuarios de la BD
     * @return $usuarios
     */
    public function ObtenerUsuariosSinAsignacion() {
        $conn = new Model();
        $sql = "SELECT A.idUsr, nomusr
                FROM sis_usr A LEFT OUTER JOIN sis_usrmnu B ON A.idUsr = B.idUsr
                WHERE B.idUsr IS NULL
                ORDER BY nomusr";
        $usuarios = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $usuarios;
    }
    
    /**
     * Obtiene los menus activos de la BD
     * @return $menus
     */
    public function ObtenerMenusDisponiblesUsuarios() {
        $conn = new Model();
        $sql = "SELECT mnu_id, mnu_nom FROM sis_mnu WHERE activo=1 AND mnu_nvl=2;";
        $menus = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $menus;
    }
    
    public function ObtenerMenusDisponiblesUsuario($idusr) {
        $conn = new Model();
        $sql = "SELECT mnu_nom, mnu_id
                FROM sis_mnu
                WHERE activo=1 AND mnu_nvl=2 AND mnu_id NOT IN (SELECT A.mnu_id
                                                                FROM sis_usrmnu A INNER JOIN sis_mnu B ON A.mnu_id=B.mnu_id
                                                                WHERE idUsr=$idusr AND B.activo=1 AND B.mnu_nvl=2)";
        $menus = $this->setCodingUtf8($sql, $conn);
        $conn->close();
        return $menus;
    }
    
    public function AgregarUsuarioMenu($datos, $Userid) {
        $conn = new Model();
        $result = $this->AgregarRelacionMenu($datos['usuario'], $datos['menus_asignar'], $conn);
        if ($result) {
            $detalle = "Agregó menú(s): ";
            $noms_menus = $this->obtenerNombresMenus($conn, $datos['menus_asignar']);
            $nomusr = $this->ObtenerNombreUsuario($datos['usuario'], $conn);
            $detalle .= (isset($datos['detalle'])) ? $noms_menus.$datos['detalle'] : $noms_menus.", al usuario: ".$nomusr;
            $accion = "Agregar asignación usuario menú";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function ModificarUsuarioMenu($datos, $Userid) {
        // Obtener Menus de usuario
        $conn = new Model();
        $sql = "SELECT mnu_id FROM sis_usrmnu WHERE idUsr=".$datos['usuario'];
        $result = $conn->query($sql);
        $menus_asignados = [];
        while($row = $conn->fetch_array($result)) {
            $menus_asignados [] = $row['mnu_id'];
        }

        $menus_asignar = [];
        for ($i=0;$i < sizeof($datos['menus_asignar']);$i++) {
            if (!in_array($datos['menus_asignar'][$i], $menus_asignados))
                $menus_asignar [] = $datos['menus_asignar'][$i];
        }
        // Si el usuario no asigno todos los menus disponibles, se verifica si hay menus por quitar
        if (isset($datos['menus_disp'])) {
            $nomusr = $this->ObtenerNombreUsuario($datos['usuario'], $conn);
            $menus_quitar = [];
            // Ciclo for almacena los menus que fueron removidos de los menus asignados (menus por quitar)
            for ($i=0;$i < sizeof($datos['menus_disp']);$i++) {
                if (in_array($datos['menus_disp'][$i], $menus_asignados)) 
                    $menus_quitar [] = $datos['menus_disp'][$i];
            }
            
            if($menus_asignar && $menus_quitar) { // Agregar y Quitar
                $this->QuitarMenusUsuario($datos['usuario'], $menus_quitar);
                $noms_menus = $this->obtenerNombresMenus($conn, $menus_quitar); // Menus que se quitaron
                $detalle = "y quitó menú(s): ".$noms_menus.", al usuario: ".$nomusr;
                $datos['detalle'] = $detalle;
                $datos['menus_asignar'] = $menus_asignar;
                return $this->AgregarUsuarioMenu($datos, $Userid);
            }
            
            if(!$menus_asignar && $menus_quitar) { // Solo Quitar
                $this->QuitarMenusUsuario($datos['usuario'], $menus_quitar);
                $accion = "Quitar asignación usuario menú";
                $noms_menus = $this->obtenerNombresMenus($conn, $menus_quitar); // Menus que se quitaron
                $detalle = "Quitó menú(s): ".$noms_menus.", al usuario: ".$nomusr;
                $this->registrarBitacora($accion, $detalle, $Userid, $conn);
                $conn->close();
                return 1;
            }
        }
        // Solo Agregar
        $datos['menus_asignar'] = $menus_asignar;
        return $this->AgregarUsuarioMenu($datos, $Userid);
    }
    
    public function ActivarDesactivarAccionMenu ($datos, $Userid) {
        $conn = new Model();
        $nomusr = $this->ObtenerNombreUsuario($datos['idusr'], $conn);
        $sql = "UPDATE sis_usrmnu SET ".$datos['campo']."=".$datos['valor']." WHERE idUsr=".$datos['idusr']." AND mnu_id=".$datos['idmnu']."";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            if ($datos['valor'] == 1) {
                $accion = "Activar rol de menú";
                $detalle = "Activó el rol: - ".$datos['rol'].", del menú: - ".$datos['nommnu']." para el usuario: $nomusr";
            }
            else {
                $accion = "Desactivar rol de menú";
                $detalle = "Desactivó el rol: - ".$datos['rol'].", del menú: - ".$datos['nommnu']." para el usuario: $nomusr";
            }
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function EliminarRelacionUsuarioMenu ($idusr, $Userid) {
        $conn = new Model();
        $nomusr = $this->ObtenerNombreUsuario($idusr, $conn);
        $sql = "DELETE FROM sis_usrmnu WHERE idUsr=$idusr";
        $result = $conn->query($sql);
        if ($conn->num_rows($result) > 0) {
            $accion = "Eliminar relación usuario-menú";
            $detalle = "Se eliminó toda la relación usuario-menú del usuario: $nomusr";
            $this->registrarBitacora($accion, $detalle, $Userid, $conn);
        }
        $conn->close();
        return $result;
    }
    
    public function AgregarRelacionMenu($idusr, $menus, $conn) {
        for ($i=0;$i < sizeof($menus);$i++) {
            $sql = "INSERT INTO sis_usrmnu(idUsr, mnu_id, rol_insert, rol_update, rol_delete, rol_consulta) VALUES($idusr, ".$menus[$i].", 0, 0, 0, 0)";
            $result = $conn->query($sql);
            if ($conn->num_rows($result) == 0)
                return 0;
        }
        return 1;
    }
    
    public function QuitarMenusUsuario($idusr, $menus) {
        $conn = new Model();
        for ($i=0;$i < sizeof($menus);$i++) {
            $sql = "DELETE FROM sis_usrmnu WHERE idUsr=$idusr AND mnu_id=".$menus[$i];
            $conn->query($sql);
        }
    }
    
    public function ObtenerNombreUsuario($idusr, $conn) {
        $sql_select = "SELECT nomusr FROM sis_usr WHERE idUsr=$idusr";
        $result = $conn->query($sql_select);
        $nomusr = utf8_encode($conn->fetch_array($result)['nomusr']);
        return $nomusr;
    }
    
    public function registrarBitacora($accion, $detalle, $Userid, $conn) {
        $idmnu = $this->container->getParameter('UsuariosMenu');
        $sql = "INSERT INTO sis_bitacora (idusr, id_mnu, fch_bitacora, accion, detalle) VALUES ($Userid, $idmnu, now(), '$accion', '$detalle')";
        $conn->query($sql);
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
    
    public function obtenerNombresMenus ($conn, $menus) {
        $nombres = "";
        for ($i=0;$i < sizeof($menus);$i++) {
            $sql="SELECT mnu_nom FROM sis_mnu WHERE mnu_id=".$menus[$i];
            $result = $conn->query($sql);
            $nombres .= "- ".utf8_encode($conn->fetch_array($result)['mnu_nom'])." ";
        }
        return $nombres;
    }
}