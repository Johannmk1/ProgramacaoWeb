<?php 
    require_once "model/pessoa.php";
    require_once "model/contato.php";    
    require_once "model/endereco.php";  
    require_once "model/repositorioPessoa.php";

    $pai = new Pessoa();
    $pai->setNome("Fabio");
    $pai->setSobreNome("Malkowski");
    $pai->setCpfCnpj("111.111.111-11");
    $pai->setDataNascimento(new DateTime("1975-03-12"));
    $pai->setTipo(1);
    $pai->addContato(new Contato(2, "Telefone", "(47) 99888-1111"));
    $pai->addContato(new Contato(1, "Email", "FAbio@email.com"));
    $pai->addEndereco(new Endereco("Rua Central", "Centro", "Rio do Sul", "SC", "89160000"));

    $mae = new Pessoa();
    $mae->setNome("Eliane");
    $mae->setSobreNome("Malkowski");
    $mae->setCpfCnpj("222.222.222-22");
    $mae->setDataNascimento(new DateTime("1978-09-23"));
    $mae->addContato(new Contato(1, "Email", "Eliane@email.com"));
    $mae->addEndereco(new Endereco("Rua Central", "Centro", "Rio do Sul", "SC", "89160000"));

    $johann = new Pessoa();
    $johann->setNome("Johann");
    $johann->setSobreNome("Malkowski");
    $johann->setCpfCnpj("156.856.485-15");
    $johann->setDataNascimento(new DateTime("2006-05-27"));
    $johann->addContato(new Contato(1, "Email Pessoal", "johann@gmail.com"));
    $johann->addContato(new Contato(2, "Celular", "(47) 99919-5754"));
    $johann->addEndereco(new Endereco("Rua Gustavo Hasse", "Bela Aliança", "Rio do Sul", "SC", "89160001"));
    $johann->addEndereco(new Endereco("Rua Pedro Lima", "Vila Nova", "Salto Pilão", "SC", "76520001"));

    $amigo = new Pessoa();
    $amigo->setNome("Yago");
    $amigo->setSobreNome("Giovanella");
    $amigo->setCpfCnpj("126.564.456-54");
    $amigo->setDataNascimento(new DateTime("2004-09-23"));
    $amigo->addContato(new Contato(1, "Email", "Yago@email.com"));
    $amigo->addEndereco(new Endereco("Rua Lateral", "Centro", "Lontras", "SC", "64740000"));

    $amigo2 = new Pessoa();
    $amigo2->setNome("Mikael");
    $amigo2->setSobreNome("Schilup");
    $amigo2->setCpfCnpj("456.485.158-54");
    $amigo2->setDataNascimento(new DateTime("203-04-29"));
    $amigo2->addContato(new Contato(1, "Email", "Mikaelz@email.com"));
    $amigo2->addEndereco(new Endereco("Rua do Lado", "Centro", "Lontras", "SC", "95740000"));

    $repo = new RepositorioPessoa();
    $repo->adicionarPessoa($pai);
    $repo->adicionarPessoa($mae);
    $repo->adicionarPessoa($johann);
    $repo->adicionarPessoa($amigo);
    $repo->adicionarPessoa($amigo2);

    $caminho = __DIR__ . "/familia.txt";
    $repo->salvarEmArquivoTxt($caminho);
    $caminho2 = __DIR__ . "/familia.json";
    $repo->salvarEmJson($caminho2);
    echo "Pessoas adicionadas ao arquivo com sucesso!"."<br>";
    
    echo $johann->getNomeCompleto()."<br>";
    echo $johann->getIdade()."<br>";
    echo $johann->getDataNascimento()->format('d/m/Y')."<br>";
    echo $johann->getCpfCnpj()."<br>";
    echo $johann->getContatoTelefone()."<br>";
    echo $johann->getContatoEmail()."<br>";
    echo $johann->getEnderecoPorCep("89160001")->getEndereco()."<br>";

?>

<!-- aula13/atividade -->