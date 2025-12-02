# Sistema de Avaliações

Um sistema web projetado para capturar, organizar e transformar feedback em inteligência acionável. Totens de avaliação, painel administrativo robusto e análise em tempo real, tudo integrado em uma arquitetura clara e escalável.

## Características Principais

- Totens interativos com interface responsiva
- Autenticação segura com controle de acesso
- Painel administrativo completo para perguntas, setores, dispositivos e usuários
- Tema totalmente customizável via JSON
- Banco PostgreSQL para alta confiabilidade
- Arquitetura MVC para manutenção simplificada
- Design responsivo para desktop, tablet e mobile
- Perguntas personalizadas por setor

## Estrutura do Projeto

```
avaliacoes/
├── README.md                           # Documentação principal
├── database/
│   └── schema.sql                      # Schema PostgreSQL
├── public/                             # Raiz do servidor web
│   ├── index.html                      # Página inicial do toten
│   ├── avaliacao.html                  # Formulário de avaliação
│   ├── obrigado.html                   # Página de confirmação
│   ├── login/
│   │   ├── login.php                   # Controlador de login
│   │   └── loginView.php               # View de login
│   ├── admin/                          # Painel administrativo
│   │   ├── index.php                   # Dashboard admin
│   │   ├── perguntas.html              # Gerenciar perguntas
│   │   ├── setores.html                # Gerenciar setores
│   │   ├── dispositivos.html           # Gerenciar dispositivos
│   │   ├── usuarios.html               # Gerenciar usuários
│   │   ├── tema.html                   # Customizar tema
│   │   ├── bi.html                     # Dashboard BI
│   │   ├── css/
│   │   │   └── admin.css               # Estilos admin
│   │   └── js/
│   │       ├── auth.js                 # Autenticação
│   │       ├── perguntas.js            # Gerenciar perguntas
│   │       ├── setores.js              # Gerenciar setores
│   │       ├── dispositivos.js         # Gerenciar dispositivos
│   │       ├── usuarios.js             # Gerenciar usuários
│   │       ├── tema.js                 # Customizar tema
│   │       └── bi.js                   # Gráficos BI
│   ├── css/
│   │   ├── style.css                   # Estilos principais
│   │   ├── login.css                   # Estilos de login
│   │   └── kiosk.css                   # Estilos do toten
│   ├── js/
│   │   ├── app.js                      # Script principal
│   │   ├── avaliacao.js                # Lógica de avaliação
│   │   ├── inicio.js                   # Inicialização
│   │   ├── login-page.js               # Lógica de login
│   │   ├── obrigado.js                 # Página de obrigado
│   │   └── theme-preload.js            # Carregamento de tema
│   └── config/
│       └── theme.json                  # Configuração de cores/tema
└── src/                                # Backend PHP
    ├── Config/
    │   └── Database.php                # Conexão com PostgreSQL
    ├── Controllers/
    │   ├── Http.php                    # Base para controladores
    │   ├── AuthController.php          # Autenticação
    │   ├── AdminController.php         # Painel administrativo
    │   ├── AvaliacaoController.php     # Gerenciar avaliações
    │   └── DispositivoController.php   # Gerenciar dispositivos
    ├── Models/
    │   ├── Auth.php                    # Modelo de autenticação
    │   ├── Usuario.php                 # Modelo de usuários
    │   ├── Avaliacao.php               # Modelo de avaliações
    │   ├── Pergunta.php                # Modelo de perguntas
    │   ├── Setor.php                   # Modelo de setores
    │   ├── Dispositivo.php             # Modelo de dispositivos
    │   ├── PerguntaSetor.php           # Modelo de relacionamento
    │   └── RelatorioBI.php             # Modelo de BI/relatórios
    └── Views/                          # Templates PHP (se necessário)
```

## Instalação e Configuração

### Requisitos

- PHP 7.4+
- PostgreSQL 12+
- Apache

### Passos

1. Clonar o repositório:

   git clone https://github.com/Johannmk1/ProgramacaoWeb.git

2. Alterar arquivo src\Config\Database.php:

   DB_HOST=localhost
   DB_PORT=5432
   DB_NAME=SistemaAvaliacao
   DB_USER=postgres
   DB_PASS=sua_senha

3. Criar banco de dados:

   Em PostgreSQL crie um database chamado "SistemaAvaliacao"
   após rode o sql do arquivio "database/schema.sql"

4. Configurar servidor web Apache:

    edite o php. ini e descomentes alinhas:
    extension=pdo_pgsql
    extension=pgsql

5. Acesso ao sistema:

- Totem: http://localhost/avaliacoes/public/
- Admin: http://localhost/avaliacoes/public/admin/
- Login padrão: usuário root / senha root  
  (alterar imediatamente em produção)

## Guia de Uso

### Totem (Usuário Final)

- Acesse a página de avaliação
- Responda usando NPS ou modelo configurado
- Confirme e finalize

### Administradores

- Gerenciar perguntas, setores, dispositivos e usuários
- Personalizar tema
- Consultar BI e indicadores de satisfação

## Estrutura do Banco

### perguntas
- id
- texto
- tipo
- status
- ordem

### avaliacoes
- id
- id_pergunta
- id_dispositivo
- resposta
- resposta_texto
- data_hora

### setores
- id
- nome
- status

### dispositivos
- id
- nome
- codigo
- id_setor
- status

### usuarios
- id
- username
- password_hash
- status
- created_at

Relacionamento adicional: perguntas_setor (N:N)

## Customização de Tema

Arquivo: public/config/theme.json

{
    "primaryColor": "#2b86fd",
    "secondaryColor": "#bcd1f5",
    "tertiaryColor": "#dfeefb",
    "cardMaxWidth": "890px"
}

## Segurança

- Hash seguro para senhas
- Prepared statements contra SQL Injection
- Validação de inputs
- Controle de acesso administrativo

## Licença

Uso restrito ao desenvolvedor.

## Desenvolvedor

Johann M. 
GitHub: https://github.com/Johannmk1

## Suporte

Abra uma issue no repositório ou entre em contato diretamente.

Última atualização: Dezembro 2025
