<?php 
    class pessoa {
        private $nome;
        private $sobrenome;
        private $dataNascimento;
        private $cpfCnpj;
        private $telefone;
        private $tipo;
        private $endereco;

        public function getNomeCompleto() {
            return $this->nome." ".$this->sobrenome.".";
        }

        public function getIdade() {
            $dataAtual = new datetime();
            $idade = $dataAtual->diff($this->dataNascimento);
            return $idade->y;
        }

        public function __construct() {
            $this->inicializaClasse();
        }

        private function inicializaClasse() {
            $this->tipo = 1;
        }

        public function __call($metodo, $parametro) {
            echo "Metodo ".$metodo." não implementado 🚧";
        }

        public function getDescricaoTipo() {
            switch ($this->tipo) {
                case 1:
                    return "Fisica";
                case 2:
                    return "Juridica";
                default:
                    return "Desconhecido";
            }
        }

        // Getters and Setters
        public function getNome() {
            return $this->nome;
        }

        public function setNome($nome) {
            $this->nome = $nome;
        }

        public function getSobrenome() {
            return $this->Sobrenome;
        }

        public function setSobrenome($Sobrenome) {
            $this->sobrenome = $Sobrenome;
        }

        public function getDataNascimento() {
            return $this->dataNascimento;
        }

        public function setDataNascimento($dataNascimento) {
            $this->dataNascimento = $dataNascimento;
        }

        public function getCpfCnpj() {
            return $this->cpfCnpj;
        }

        public function setCpfCnpj($cpfCnpj) {
            $this->cpfCnpj = $cpfCnpj;
        }

        public function getTelefone() {
            return $this->telefone;
        }

        public function setTelefone($telefone) {
            $this->telefone = $telefone;
        }

        public function getTipo() {
            return $this->tipo;
        }

        public function setTipo($tipo) {
            $this->tipo = $tipo;
        }

        public function getEndereco() {
            return $this->endereco;
        }

        public function setEndereco($endereco) {
            $this->endereco = $endereco;
        }
    }
?>