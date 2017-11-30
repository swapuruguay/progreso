<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sociosModel
 *
 * @author walter
 */
require_once 'Socio.php';
require_once 'categoriasModel.php';

class sociosModel extends Model{

    private $_modeloCategorias;
    public function __construct() {
        parent::__construct();
        $this->_modeloCategorias = new categoriasModel();
    }

     public function getAll($orden = 'id_socio') {

        $listado = $this->_db->query("SELECT CONCAT(IFNULL(CONCAT(apellido, ' '),''),nombre) as apel,
        nombre, apellido, id_socio, domicilio, id_categoria_fk from socios where estado='A' order by ".$orden);
        $listado = $listado->fetchall(PDO::FETCH_OBJ);
        $arreglo = array();
        foreach($listado as $valor) {
            $socio = new Socio($valor->id_socio, $valor->nombre, $valor->apel);
            $socio->setCategoria($this->_modeloCategorias->getById($valor->id_categoria_fk));
            $socio->setDomicilio($valor->domicilio);
            $arreglo[] = $socio;


        }
        return $arreglo;

    }

    public function getEliminados() {

        $listado = $this->_db->query("SELECT * FROM socios WHERE estado='B' order by nombre");
        $listado = $listado->fetchall(PDO::FETCH_OBJ);
        $arreglo = array();
        foreach($listado as $valor) {
            $arreglo[] = new Socio($valor->id_socio, $valor->nombre, $valor->apellido);

        }
        return $arreglo;

    }

    public function getPaginados($desde) {

        $listado = $this->_db->query("SELECT * FROM socios WHERE estado='A' order by id_socio limit " .$desde. ", 15");
        $listado = $listado->fetchall(PDO::FETCH_OBJ);
        $arreglo = array();
        foreach($listado as $valor) {
            $arreglo[] = new Socio($valor->id_socio, $valor->nombre, $valor->apellido);

        }
        return $arreglo;

    }

    public function getPaginadosE($desde) {

        $listado = $this->_db->query("SELECT * FROM socios WHERE estado='B' order by nombre limit " .$desde. ", 15");
        $listado = $listado->fetchall(PDO::FETCH_OBJ);
        $arreglo = array();
        foreach($listado as $valor) {
            $arreglo[] = new Socio($valor->id_socio, $valor->nombre, $valor->apellido);

        }
        return $arreglo;

    }

    public function getById($id){
        $listado = $this->_db->query("SELECT * FROM socios WHERE id_socio = " .$id);
        $listado = $listado->fetch(PDO::FETCH_OBJ);
        $socio = new Socio($listado->id_socio, $listado->nombre, $listado->apellido);
        $socio->setDocumento($listado->documento);
        $socio->setCategoria($this->_modeloCategorias->getById($listado->id_categoria_fk));
        $socio->setDomicilio($listado->domicilio);
        $socio->setEstado($listado->estado);
        $socio->setFechaIngreso($listado->fecha_ingreso);
        $socio->setFechaNacimiento($listado->fecha_nacimiento);
        $socio->setTelefono($listado->telefono);
        $socio->setEmail($listado->email);
        $socio->setExento($listado->exento == 1 ? true : false);
        $socio->setFoto($listado->foto);
        return $socio;
    }

    public function getByApellido($texto){
        $listado = $this->_db->query("SELECT id_socio,nombre, apellido FROM socios "
                . "WHERE (nombre LIKE '" .$texto . "%' OR apellido LIKE '" .$texto . "%')  AND estado='A'");
        return $listado->fetchall(PDO::FETCH_OBJ);


    }

    public function getByApellidoE($texto){
        $listado = $this->_db->query("SELECT id_socio,nombre, apellido FROM socios "
                . "WHERE (nombre LIKE '" .$texto . "%' OR apellido LIKE '" .$texto . "%')  AND estado='B'");
        return $listado->fetchall(PDO::FETCH_OBJ);


    }

    public function save(Socio $socio) {
        $datos = array(

            'nombre'            => $socio->getNombre(),
            'apellido'          => $socio->getApellido(),
            'documento'         => $socio->getDocumento(),
            'domicilio'         => $socio->getDomicilio(),
            'telefono'          => $socio->getTelefono(),
            'fecha_nacimiento'  => $socio->getFechaNacimiento(),
            'fecha_ingreso'     => $socio->getFechaIngreso(),
            'email'             => $socio->getEmail(),
            'foto'              => $socio->getFoto(),
            'exento'            => $socio->getExento(),
            'estado'            => 'A',
            'id_categoria_fk'   => $socio->getCategoria()->getId()
        );
        $sql = 'INSERT INTO socios ' . $this->preparaInsert($datos);

        return $this->_db->query($sql);
    }

    public function update(Socio $socio) {
        $datos = array(

            'nombre'            => $socio->getNombre(),
            'apellido'          => $socio->getApellido(),
            'documento'         => $socio->getDocumento(),
            'domicilio'         => $socio->getDomicilio(),
            'telefono'          => $socio->getTelefono(),
            'fecha_nacimiento'  => $socio->getFechaNacimiento(),
            'fecha_ingreso'     => $socio->getFechaIngreso(),
            'email'             => $socio->getEmail(),
            'exento'            => $socio->getExento(),
            'id_categoria_fk'   => $socio->getCategoria()->getId()
        );
        if($socio->getFoto()) {
            $datos['foto'] = $socio->getFoto();
        }

        $sql = 'UPDATE socios SET ' . $this->preparaUpdate($datos) . ' WHERE id_socio=' . $socio->getId();

        return $this->_db->query($sql);
    }

    public function buildSocio() {
        return new Socio(0, 'Nuevo', 'nuevo');
    }

    public function delete(Socio $socio) {
        $sql = "UPDATE socios SET estado='B' WHERE id_socio = ".$socio->getId();
        return $this->_db->query($sql);
    }

    public function getAtrasados() {
        $listado = $this->_db->query("SELECT socios.id_socio,socios.nombre,
            socios.apellido, socios.id_categoria_fk,sum(importe) as importe FROM socios JOIN cuotas ON
            cuotas.id_socio_fk = socios.id_socio WHERE socios.estado='A' group by id_socio having sum(importe) > 0 ORDER BY importe DESC, apellido");
        $listado = $listado->fetchall(PDO::FETCH_OBJ);
        $arreglo = array();
        foreach($listado as $valor) {

            $socio =new Socio($valor->id_socio, $valor->nombre, $valor->apellido);
            $socio->setSaldo($valor->importe);
            $socio->setCategoria($this->_modeloCategorias->getById($valor->id_categoria_fk));
            $arreglo[] = $socio;

        }
        return $arreglo;
    }

    public function getHabilitados() {
        $listado = $this->_db->query("SELECT * FROM socios WHERE estado='A' AND exento=0");
        $listado = $listado->fetchall(PDO::FETCH_OBJ);
        $arreglo = array();
        foreach($listado as $valor) {
            $soc = new Socio($valor->id_socio, $valor->nombre, $valor->apellido);
            $soc->setFechaIngreso($valor->fecha_ingreso);
            $soc->setCategoria($this->_modeloCategorias->getById($valor->id_categoria_fk));
            $arreglo[] = $soc;

        }
        return $arreglo;
    }

    public function activar(Socio $socio) {
        $sql = "UPDATE socios SET estado='A' WHERE id_socio = ".$socio->getId();
        return $this->_db->query($sql);
    }


}
