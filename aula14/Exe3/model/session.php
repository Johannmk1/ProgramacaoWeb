<?php
    class Session {
        private $sessionId;
        private $status;
        private $usuario;
        private $dataHoraInicio;
        private $DataHoraUltimoAcesso;

        public function inciaSessao() {
            session_start();
            $this->sessionId = session_id();
            if($this->getDadosSesaso('datahorainicio')) {
                $this->dataHoraInicio = $this->getDadoSessao('datahorainicio');
                $this->DataHoraUltimoAcesso = date("y-m-d h:i:s");
                $this->setDadoSessao(dat);
            } else {
                $this->dataHoraInicio = date("y-m-d h:i:s");
                $this->setDadosSessao('usuario', null);
            }
        }

        public function finalisaSessao() {
       
        }

        public function getUsuarioSessao() {
       
        }
    }
?>