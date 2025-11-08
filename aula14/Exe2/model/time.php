<?php 
    require_once "jogador.php";

    class time {
        private $nome;
        private $anoFundacao;
        private $jogadores;


        public function setAnoFundacao($anoFundacao) {
            $this->anoFundacao = $anoFundacao; 
        }
        public function getAnoFundacao() {
            return $this->anoFundacao; 
        }

        public function setNome($nome) {
            $this->nome = $nome; 
        }
        public function getNome() {
            return $this->nome; 
        }

        public function AddJogadores($nome, $posicao, $dataNascimento) {
            $jogador = new jogador;    
            $jogador->setNome($nome); 
            $jogador->setPosicao($posicao);        
            $jogador->setDataNascimento($dataNascimento);

            array_push($this->jogadores, $jogador);   
        }
        public function getJogadores() {
            foreach ($this->jogadores as $jogador) {
                echo $jogador->getNome()." - ";
                echo $jogador->getPosicao()." - ";
                echo $jogador->getDataNascimento()."<br>";
            } 
        }

        public function __construct() {
            $this->inicializaClasse();
        }

        private function inicializaClasse() {
            $this->jogadores = array();
        }

        public function __call($metodo, $parametro) {
            echo "Metodo ".$metodo." não implementado 🚧";
        }
    }
?>