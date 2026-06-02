# 🔧 Sistema de Gestão de Ordens de Serviço — Assistência Técnica

API REST profissional desenvolvida em **Laravel 12** para gerenciamento completo do fluxo operacional de uma assistência técnica.

Abner Cosmo - 2320046
Ana Luiza Martins Aires - 2310847
Davi Montandon de Siqueira - 2311965
Isabella Guerra de Moraes Barbosa - 2320494
Igor Alexandre Ribeiro de Paulo - 2313308
Santhiago Lopes Abreu - 2321229
Gustavo Lopes da Silva - 2411165

---

## 🚀 Tecnologias Utilizadas

| Tecnologia | Versão | Finalidade |
|---|---|---|
| PHP | ^8.2 | Linguagem principal |
| Laravel | ^12.0 | Framework |
| Laravel Sanctum | ^4.0 | Autenticação via Token |
| SQLite | — | Banco de dados (padrão) |
| ViaCEP API | — | Integração externa (consulta de CEP) |
| PHPUnit | ^11 | Testes automatizados |

---

## 📁 Arquitetura do Projeto

```
app/
├── Http/
│   ├── Controllers/        # Recebem requisições HTTP, validam input, delegam
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── OrdemController.php
│   │   ├── PecaController.php
│   │   └── UserController.php
│   ├── Services/           # Regras de negócio
│   │   ├── AuthService.php
│   │   ├── ClienteService.php
│   │   ├── OrdemService.php
│   │   ├── PecaService.php
│   │   └── UserService.php
│   ├── Repositories/       # Acesso ao banco de dados
│   │   ├── ClienteRepository.php
│   │   ├── OrdemRepository.php
│   │   ├── PecaRepository.php
│   │   └── UserRepository.php
│   └── Middleware/
│       └── CheckPerfil.php # Controle de acesso por perfil
├── Models/
│   ├── User.php
│   ├── Cliente.php
│   ├── OrdemServico.php
│   ├── HistoricoOrdem.php
│   ├── Peca.php
│   ├── OrdemPeca.php
│   └── MovimentacaoEstoque.php
database/
├── migrations/             # Estrutura do banco
├── seeders/                # Dados de exemplo
└── factories/              # Factories para testes
routes/
└── api.php                 # Definição de todas as rotas
tests/
└── Feature/
    ├── AuthTest.php
    └── OrdemTest.php
```

---

## ⚙️ Instalação e Execução

### Pré-requisitos
- [Laragon](https://laragon.dev/download) (inclui PHP, MySQL, Composer)

### 1. Clonar e instalar dependências
```bash
git clone <url-do-repositorio>
cd assistencia_tecnica
composer install
```

### 2. Configurar ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurar banco de dados (SQLite)

Criar o arquivo do banco:
```bash
# Linux/Mac
touch database/database.sqlite

# Windows (Command Prompt)
type nul > database/database.sqlite
```

Confirmar no `.env`:
```env
DB_CONNECTION=sqlite
```

### 4. Criar pasta public
O arquivo `public/index.php` deve conter:
```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
```

### 5. Criar pastas de storage necessárias
```bash
mkdir storage\framework\cache
mkdir storage\framework\views
mkdir storage\framework\sessions
mkdir storage\logs
```

### 6. Publicar migrations do Sanctum
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 7. Executar migrations e seed
```bash
php artisan migrate --seed
```

### 8. Iniciar servidor
```bash
php artisan serve
```

API disponível em: `http://localhost:8000/api`

### 9. Executar testes
```bash
php artisan test
```

---

## 👤 Usuários de Exemplo (após seed)

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@assistencia.com | password |
| Atendente | atendente@assistencia.com | password |
| Técnico 1 | tecnico1@assistencia.com | password |
| Técnico 2 | tecnico2@assistencia.com | password |
| Técnico 3 | tecnico3@assistencia.com | password |

---

## 🔐 Autenticação

Todas as rotas (exceto `/api/login`) requerem o header:
```
Authorization: Bearer {token}
```

O token é obtido via `POST /api/login`.

---

## 📡 Endpoints da API

### Autenticação

| Método | Rota | Descrição | Acesso |
|---|---|---|---|
| POST | /api/login | Realizar login | Público |
| POST | /api/logout | Realizar logout | Autenticado |
| GET | /api/me | Dados do usuário logado | Autenticado |

### Clientes

| Método | Rota | Descrição |
|---|---|---|
| GET | /api/clientes | Listar (filtros: busca, ativo) |
| POST | /api/clientes | Cadastrar |
| GET | /api/clientes/{id} | Buscar por ID |
| PUT | /api/clientes/{id} | Atualizar |
| GET | /api/cep/{cep} | Consultar endereço via ViaCEP |

### Ordens de Serviço

| Método | Rota | Descrição |
|---|---|---|
| GET | /api/ordens | Listar (filtros: status, prioridade, tecnico_id) |
| POST | /api/ordens | Abrir nova OS |
| GET | /api/ordens/{id} | Buscar OS com histórico e peças |
| PUT | /api/ordens/{id} | Atualizar status, técnico, diagnóstico |
| POST | /api/ordens/{id}/pecas | Vincular peça à OS |
| GET | /api/relatorios/ordens | Relatório completo |

### Peças e Estoque

| Método | Rota | Descrição |
|---|---|---|
| GET | /api/pecas | Listar peças |
| POST | /api/pecas | Cadastrar peça |
| GET | /api/pecas/{id} | Buscar peça com histórico |
| PUT | /api/pecas/{id} | Atualizar peça |
| POST | /api/pecas/{id}/estoque | Movimentar estoque (entrada/saida/ajuste) |
| GET | /api/relatorios/pecas-mais-utilizadas | Ranking de uso |

### Usuários (admin only)

| Método | Rota | Descrição |
|---|---|---|
| GET | /api/usuarios | Listar usuários |
| POST | /api/usuarios | Criar usuário |
| GET | /api/usuarios/{id} | Buscar usuário |
| PUT | /api/usuarios/{id} | Atualizar usuário |
| GET | /api/tecnicos-disponiveis | Técnicos com < 5 ordens ativas |

---

## ⚙️ Regras de Negócio Implementadas

- ✅ OS não pode ser **concluída sem técnico** responsável
- ✅ OS **concluídas e canceladas** não podem ser editadas
- ✅ Histórico de alterações de status registrado automaticamente
- ✅ Cada técnico pode ter **no máximo 5 ordens** simultâneas em andamento
- ✅ Prioridades: **baixa, média, alta, urgente**
- ✅ Cálculo automático do **tempo de atendimento**
- ✅ OS cancelada **exige motivo obrigatório**
- ✅ Estoque de peças **não pode ficar negativo**
- ✅ Toda movimentação de estoque **gera histórico**
- ✅ Peças **sem estoque bloqueiam** utilização em OS
- ✅ CEP preenchido automaticamente via **ViaCEP**

---

## 🌐 Integração Externa

**ViaCEP** (`https://viacep.com.br`): ao cadastrar ou atualizar cliente com CEP, o sistema consulta automaticamente logradouro, bairro, cidade e estado.

---

## 📊 Relatórios

`GET /api/relatorios/ordens` retorna:
- Quantidade de OS por status
- OS em atraso
- Técnicos com mais atendimentos
- Tempo médio de atendimento (em horas)

`GET /api/relatorios/pecas-mais-utilizadas` retorna ranking das peças mais utilizadas nas OS.

---

## ⚠️ Observações para SQLite

O projeto usa SQLite por padrão. Algumas diferenças em relação ao MySQL:

- Ordenação por prioridade usa `CASE WHEN` em vez de `FIELD()` (já ajustado no código)
- Não suporta `ALTER TABLE` com múltiplas operações em uma única migration

Para usar MySQL em produção, basta alterar o `DB_CONNECTION` no `.env` e criar o banco manualmente antes de rodar as migrations.

---

## 📄 Documentação

A coleção completa de endpoints está em `docs/api-collection.json`, compatível com **Postman** e **Insomnia**.

Para importar no Postman: **Import → selecionar o arquivo** → configurar a variável `token` após o login.
