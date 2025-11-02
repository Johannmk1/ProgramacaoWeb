-- Schema inicial para o módulo de avaliação

CREATE TABLE IF NOT EXISTS perguntas (
  id SERIAL PRIMARY KEY,
  texto TEXT NOT NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE,
  ordem INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS avaliacoes (
  id SERIAL PRIMARY KEY,
  id_pergunta INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
  id_dispositivo TEXT NULL,
  resposta INTEGER NOT NULL CHECK (resposta BETWEEN 0 AND 10),
  feedback TEXT NULL,
  data_hora TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Perguntas de exemplo
INSERT INTO perguntas (texto, status, ordem) VALUES
('Atendimento foi cordial?', TRUE, 1),
('Tempo de espera foi adequado?', TRUE, 2),
('Solução atendeu sua necessidade?', TRUE, 3)
ON CONFLICT DO NOTHING;

-- Tabela de setores (áreas/unidades)
CREATE TABLE IF NOT EXISTS setores (
  id SERIAL PRIMARY KEY,
  nome TEXT NOT NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE
);

-- Tabela de dispositivos (totens/estações), vinculados a um setor
CREATE TABLE IF NOT EXISTS dispositivos (
  id SERIAL PRIMARY KEY,
  nome TEXT NOT NULL,
  codigo TEXT NOT NULL UNIQUE,
  id_setor INTEGER NULL REFERENCES setores(id) ON DELETE SET NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE
);

-- Seed básico de setor para testes
INSERT INTO setores (nome, status)
SELECT 'Geral', TRUE
WHERE NOT EXISTS (SELECT 1 FROM setores);

-- Mapeamento de perguntas por setor (permite perguntas específicas por setor)
CREATE TABLE IF NOT EXISTS perguntas_setor (
  id_setor INTEGER NOT NULL REFERENCES setores(id) ON DELETE CASCADE,
  id_pergunta INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
  PRIMARY KEY (id_setor, id_pergunta)
);

-- Usuários para acesso às áreas de administração
CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);
