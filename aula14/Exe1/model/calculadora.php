<?php 
    class calculadora {
        private $numero1;
        private $numero2;

        public function calculaSubtracao() {
            return $this->numero1 - $this->numero2;
        }

        public function calculaAdicao() {
            return $this->numero1 + $this->numero2;
        }

        public function calculaDivisao() {
            return $this->numero1 / $this->numero2;
        }

        public function calculaMultiplicacao() {
            return $this->numero1 * $this->numero2;
        }

        public function __construct() {
            $this->inicializaClasse();
        }

        private function inicializaClasse() {
            $this->numero1 = 0;
            $this->numero2 = 0;
        }

        public function __call($metodo, $parametro) {
            echo "Metodo ".$metodo." não implementado 🚧";
        }

        // Getters and Setters
        public function getNumero1() {
            return $this->numero1;
        }

        public function setNumero1($numero1) {
            $this->numero1 = $numero1;
        }

        public function getNumero2() {
            return $this->Sobrenome;
        }

        public function setNumero2($numero2) {
            $this->numero2 = $numero2;
        }
    }
?>