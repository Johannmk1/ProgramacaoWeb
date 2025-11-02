<?php   

    class Endereco {
        private $logradouro;
        private $bairro;
        private $cidade;
        private $estado;
        private $cep;

        public function __construct($logradouro, $bairro, $cidade, $estado, $cep) {
            $this->logradouro = $logradouro;
            $this->bairro = $bairro;
            $this->cidade = $cidade;
            $this->estado = $estado;
            $this->cep = $cep;
        }

        public function getEndereco() {
            return $this->logradouro . ", " . $this->bairro . ", " . $this->cidade . ", " . $this->estado . ", " . $this->cep . ".";
        }

        // Getters and Setters
        public function getLogradouro() {
            return $this->logradouro;
        }   

        public function setLogradouro($logradouro) {
            $this->logradouro = $logradouro;
        }

        public function getBairro() {
            return $this->bairro;
        }

        public function setBairro($bairro) {
            $this->bairro = $bairro;
        }

        public function getCidade() {
            return $this->cidade;
        }

        public function setCidade($cidade) {
            $this->cidade = $cidade;
        }

        public function getEstado() {
            return $this->estado;
        }

        public function setEstado($estado) {
            $this->estado = $estado;
        }

        public function getCep() {
            return $this->cep;
        }

        public function setCep($cep) {
            $this->cep = $cep;
        }
    }

?>