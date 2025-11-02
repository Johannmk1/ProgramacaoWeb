<?php
require_once "model/pessoa.php";

class RepositorioPessoa {

    private $pessoas;

    public function __construct() {
        $this->pessoas = [];
    }

    public function adicionarPessoa(Pessoa $pessoa) {
        $this->pessoas[] = $pessoa;
    }

    public function getPessoas() {
        return $this->pessoas;
    }

    public function salvarEmArquivoTxt(string $caminhoArquivo) {
        $conteudo = "";

        foreach ($this->pessoas as $pessoa) {
            $conteudo .= "======================================" . PHP_EOL;
            $conteudo .= "Nome: " . $pessoa->getNome() . PHP_EOL;
            $conteudo .= "Sobrenome: " . $pessoa->getSobreNome() . PHP_EOL;
            $conteudo .= "CPF/CNPJ: " . $pessoa->getCpfCnpj() . PHP_EOL;
            $conteudo .= "Data de Nascimento: " . $pessoa->getDataNascimento()->format('d/m/Y') . PHP_EOL;
            $conteudo .= "Tipo: " . $pessoa->getDescricaoTipo() . PHP_EOL . PHP_EOL;

            $conteudo .= "Contatos:" . PHP_EOL;
            $contatos = $pessoa->getContatos();
            if (!empty($contatos)) {
                foreach ($contatos as $contato) {
                    $conteudo .= "- " . $contato->getDescricaoTipo() . ": " . $contato->getValor() . PHP_EOL;
                }
            } else {
                $conteudo .= "- Nenhum contato cadastrado" . PHP_EOL;
            }

            $conteudo .= PHP_EOL . "Endereços:" . PHP_EOL;
            $enderecos = $pessoa->getEnderecos();
            if (!empty($enderecos)) {
                foreach ($enderecos as $endereco) {
                    $conteudo .= "- " . $endereco->getEndereco() . PHP_EOL;
                }
            } else {
                $conteudo .= "- Nenhum endereço cadastrado" . PHP_EOL;
            }

            $conteudo .= PHP_EOL . "======================================" . PHP_EOL . PHP_EOL;
        }

        file_put_contents($caminhoArquivo, $conteudo, FILE_APPEND);
    }

    public function salvarEmJson(string $caminhoArquivo) {
        $dadosExistentes = [];

        if (file_exists($caminhoArquivo) && filesize($caminhoArquivo) > 0) {
            $conteudoExistente = file_get_contents($caminhoArquivo);
            $dadosExistentes = json_decode($conteudoExistente, true);

            if (!is_array($dadosExistentes)) {
                $dadosExistentes = [];
            }
        }

        foreach ($this->pessoas as $pessoa) {
            $contatos = [];
            foreach ($pessoa->getContatos() as $contato) {
                $contatos[] = [
                    'tipo' => $contato->getDescricaoTipo(),
                    'valor' => $contato->getValor()
                ];
            }

            $enderecos = [];
            foreach ($pessoa->getEnderecos() as $endereco) {
                $enderecos[] = [
                    'logradouro' => $endereco->getLogradouro(),
                    'bairro' => $endereco->getBairro(),
                    'cidade' => $endereco->getCidade(),
                    'estado' => $endereco->getEstado(),
                    'cep' => $endereco->getCep()
                ];
            }

            $dadosExistentes[] = [
                'nome' => $pessoa->getNome(),
                'sobrenome' => $pessoa->getSobreNome(),
                'cpfCnpj' => $pessoa->getCpfCnpj(),
                'dataNascimento' => $pessoa->getDataNascimento()->format('Y-m-d'),
                'tipo' => $pessoa->getDescricaoTipo(),
                'contatos' => $contatos,
                'enderecos' => $enderecos
            ];
        }

        file_put_contents($caminhoArquivo, json_encode($dadosExistentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
?>
