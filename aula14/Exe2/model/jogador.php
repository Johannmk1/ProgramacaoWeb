<?php   
    class jogador {
        private $nome;
        private $posicao;
        private $dataNascimento;

        public function setNome($nome) {
            $this->nome = $nome;
        } 

        public function getNome() {
            return $this->nome;
        } 

        public function setPosicao($posicao) {
            $this->posicao = $posicao;
        } 

        public function getPosicao() {
            return $this->posicao;
        } 

        public function setDataNascimento($dataNascimento) {
            $this->dataNascimento = $dataNascimento;
        } 

        public function getDataNascimento() {
            return $this->dataNascimento;
        } 
    }

?>