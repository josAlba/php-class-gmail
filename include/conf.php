<?php

    $configuracion = new class{
        
        public $ip;
        public $puerto;
        public $hilos;

        public function __construct(){

            $arch       = __DIR__ .'/../conf.json';
            $gestor     = file_get_contents($arch, true);
            $buffero    = json_decode($gestor);
            
            $this->ip       = $buffero->ip;
            $this->puerto   = $buffero->puerto;
            $this->hilos    = $buffero->hilos;
        }
    };