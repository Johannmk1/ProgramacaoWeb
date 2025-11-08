<?php 
    class computador {
        private $status;

        public function ligar() {
            $this->status = 1;
        }

        public function desligar() {
            $this->status = 0;
        }

        public function getStatus() {
            return $this->status;
        }
        public function __construct() {
            $this->inicializaClasse();
        }

        private function inicializaClasse() {
            $this->status = 0;
        }

        public function __call($metodo, $parametro) {
            echo "Metodo ".$metodo." não implementado 🚧";
        }

    }
?>