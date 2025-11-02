<?php 
    class Pessoa {
        private $nome;
        private $sobreNome;
        private $dataNascimento;
        private $cpfCnpj;
        private $tipo; // 1 - Fisica | 2 - Juridica
        private $contato;
        private $endereco;

        public function __construct() {
            $this->inicializaClasse();
        }  

        private function inicializaClasse(){
            $this->tipo = 1;
            $this->contato = array();
            $this->endereco = array();
        }
        
        public function getNomeCompleto() {
            return $this->nome . " " . $this->sobreNome . ".";
        }

        public function getIdade() {
            $dataAtual = new DateTime();
            $idade = $dataAtual->diff($this->dataNascimento);
            return $idade->y;
        }

        public function getDescricaoTipo() {
            switch($this->tipo){
                case 1:
                    return "Física";
                case 2:
                    return "Jurídica";
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

        public function getSobreNome() {
            return $this->sobreNome;
        }

        public function setSobreNome($sobreNome) {
            $this->sobreNome = $sobreNome;
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

        public function getTipo() {
            return $this->tipo;
        }

        public function setTipo($tipo) {
            if($tipo < 1 || $tipo > 2){
                throw new Exception("Tipo inválido. Use 1 para Física ou 2 para Jurídica.");
            }
            $this->tipo = $tipo;
        }

        public function addEndereco( $endereco) {
            array_push($this->endereco, $endereco);
            
        }

        public function getEnderecos() {
            return $this->endereco; 
        }

        public function getEnderecoPorCep(string $cep) {
            foreach ($this->endereco as $endereco) {
                if ($endereco->getCep() === $cep) {
                    return $endereco;
                }
            }
            return null; 
        }

        public function addContato($contato) {
            array_push($this->contato, $contato);
        }

        public function getContatos() {
            return $this->contato;
        }

        public function getContatoTelefone() {
            foreach($this->contato as $contato){
                if($contato->getTipo() == 2){
                    return $contato->getValor();
                }
            }
            return null;
        }

        public function getContatoEmail() {
            foreach($this->contato as $contato){
                if($contato->getTipo() == 1){
                    return $contato->getValor();
                }
            }
            return null;
        }

    }
?>