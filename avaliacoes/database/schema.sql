CREATE TABLE IF NOT EXISTS perguntas (
  id SERIAL PRIMARY KEY,
  texto TEXT NOT NULL,
  tipo VARCHAR(20) NOT NULL DEFAULT 'nps',
  status BOOLEAN NOT NULL DEFAULT TRUE,
  ordem INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS avaliacoes (
  id SERIAL PRIMARY KEY,
  id_pergunta INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
  id_dispositivo TEXT NULL,
  resposta INTEGER NULL CHECK (resposta BETWEEN 0 AND 10),
  resposta_texto TEXT NULL,
  data_hora TIMESTAMP NOT NULL DEFAULT NOW()
);

INSERT INTO perguntas (texto, tipo, status, ordem) VALUES
('Atendimento foi cordial?', 'nps', TRUE, 1),
('Tempo de espera foi adequado?', 'nps', TRUE, 2),
('Solução atendeu sua necessidade?', 'nps', TRUE, 3)
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS setores (
  id SERIAL PRIMARY KEY,
  nome TEXT NOT NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS dispositivos (
  id SERIAL PRIMARY KEY,
  nome TEXT NOT NULL,
  codigo TEXT NOT NULL UNIQUE,
  id_setor INTEGER NULL REFERENCES setores(id) ON DELETE SET NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO setores (nome, status)
SELECT 'Geral', TRUE
WHERE NOT EXISTS (SELECT 1 FROM setores);

INSERT INTO setores (nome, status)
SELECT 'Atendimento Presencial', TRUE
WHERE NOT EXISTS (SELECT 1 FROM setores WHERE nome = 'Atendimento Presencial');

INSERT INTO setores (nome, status)
SELECT 'Suporte Remoto', TRUE
WHERE NOT EXISTS (SELECT 1 FROM setores WHERE nome = 'Suporte Remoto');

CREATE TABLE IF NOT EXISTS perguntas_setor (
  id_setor INTEGER NOT NULL REFERENCES setores(id) ON DELETE CASCADE,
  id_pergunta INTEGER NOT NULL REFERENCES perguntas(id) ON DELETE CASCADE,
  PRIMARY KEY (id_setor, id_pergunta)
);

INSERT INTO perguntas (texto, tipo, status, ordem)
SELECT 'Comunicação foi clara e objetiva?', 'nps', TRUE, 4
WHERE NOT EXISTS (SELECT 1 FROM perguntas WHERE texto = 'Comunicação foi clara e objetiva?');

INSERT INTO perguntas_setor (id_setor, id_pergunta)
SELECT s.id, p.id
FROM setores s
JOIN perguntas p ON p.texto = 'Atendimento foi cordial?'
WHERE s.nome = 'Atendimento Presencial'
ON CONFLICT DO NOTHING;

INSERT INTO perguntas_setor (id_setor, id_pergunta)
SELECT s.id, p.id
FROM setores s
JOIN perguntas p ON p.texto = 'Tempo de espera foi adequado?'
WHERE s.nome = 'Atendimento Presencial'
ON CONFLICT DO NOTHING;

INSERT INTO perguntas_setor (id_setor, id_pergunta)
SELECT s.id, p.id
FROM setores s
JOIN perguntas p ON p.texto = 'Solução atendeu sua necessidade?'
WHERE s.nome = 'Suporte Remoto'
ON CONFLICT DO NOTHING;

INSERT INTO perguntas_setor (id_setor, id_pergunta)
SELECT s.id, p.id
FROM setores s
JOIN perguntas p ON p.texto = 'Comunicação foi clara e objetiva?'
WHERE s.nome = 'Suporte Remoto'
ON CONFLICT DO NOTHING;

CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

INSERT INTO usuarios (username, password_hash, status)
VALUES ('root', 'root', TRUE)
ON CONFLICT (username) DO NOTHING;

INSERT INTO dispositivos (nome, codigo, id_setor, status)
SELECT 'Totem Recepção', 'TOTEM-RECEP', s.id, TRUE
FROM setores s
WHERE s.nome = 'Atendimento Presencial'
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO dispositivos (nome, codigo, id_setor, status)
SELECT 'Tablet Suporte', 'TABLET-SUP', s.id, TRUE
FROM setores s
WHERE s.nome = 'Suporte Remoto'
ON CONFLICT (codigo) DO NOTHING;
