<?php 
    class pessoa {
        public $nome;
        public $sobrenome;
        public $cpf;
        private $dataInstancia;

        public function __construct() {
            $this->dataInstancia = date("d/m/y h:i:s");
        }

        public function getNomeCompleto() {
            return $this->nome." ".$this->sobrenome.".";
        }

        public function getdataInstancia() {
            return $this->dataInstancia;
        }

        public function __call($metodo, $parametro) {
            echo "Metodo ".$metodo." não implementado 🚧";
        }
    }
?>