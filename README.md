# LearnAPI — E-Learning REST API

Symfony 7.4 + API Platform 4 REST API for an online learning platform with JWT authentication, real-time notifications, PDF certificates and BDD tests.

## Stack

- **Framework**: Symfony 7.4 + API Platform 4
- **Auth**: LexikJWT (RS256 keypair)
- **Database**: MySQL 8.0 + Doctrine ORM
- **Async**: Symfony Messenger (email queue)
- **Real-time**: Mercure SSE (enrollment/progress/certificate events)
- **PDF**: Dompdf (certificate generation with QR code)
- **Cache**: HTTP ETags + Cache-Control headers
- **Rate limiting**: Symfony Rate Limiter (60 req/min authenticated, 10/min anonymous)
- **Static analysis**: PHPStan level 8
- **Tests**: PHPUnit (11 unit tests) + Behat BDD (10 scenarios)
- **Containerization**: Docker + Docker Compose

## Features

- JWT authentication (register / login)
- Course management (CRUD, publish, categories, levels)
- Lesson management per course
- Student enrollment with FSM (active / completed / cancelled)
- Quiz & QuizQuestion entities
- Progress tracking per enrollment
- Certificate generation (PDF + QR code verification)
- Real-time events via Mercure SSE
- Async email notifications via Messenger
- HTTP Cache (ETags, Last-Modified, 304 Not Modified)
- API rate limiting per IP

## API Resources (API Platform)

| Resource | Endpoints |
|----------|-----------|
| Auth | POST `/api/auth/register`, POST `/api/auth/login` |
| Courses | GET/POST `/api/courses`, GET/PUT/DELETE `/api/courses/{id}` |
| Lessons | GET/POST `/api/lessons`, GET/PUT/DELETE `/api/lessons/{id}` |
| Enrollments | GET/POST `/api/enrollments`, PATCH progress |
| Certificates | GET `/api/certificates/my`, GET download/verify |

## Run with Docker

```bash
docker compose up --build
```

API available at: `http://localhost:8000/api`

Run Doctrine migrations after start:

```bash
docker exec learnapi-app php bin/console doctrine:migrations:migrate --no-interaction
```

## Run locally

```bash
# Requirements: PHP 8.3+, MySQL 8.0, Composer

composer install
# Set DATABASE_URL in .env.local
php bin/console doctrine:migrations:migrate
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
symfony server:start
```

## Run tests

```bash
# PHPUnit (unit tests)
php vendor/bin/phpunit

# Behat (BDD scenarios — requires running server)
php vendor/bin/behat
```

Results: **11 PHPUnit tests + 10 Behat scenarios**

## Project structure

```
learnapi/
├── src/
│   ├── ApiResource/      ← API Platform resource classes
│   ├── Controller/       ← Auth controller
│   ├── Entity/           ← Course, Lesson, Enrollment, Certificate, Quiz...
│   ├── Repository/       ← Doctrine repositories
│   ├── Security/         ← JWT user provider
│   ├── Service/          ← MercureService, CertificatePdfService, QrCodeService
│   ├── State/            ← API Platform State Processors
│   └── Message/          ← Messenger messages & handlers
├── features/             ← Behat BDD feature files
├── tests/Unit/           ← PHPUnit unit tests
├── migrations/           ← Doctrine migrations
└── config/               ← Symfony configuration
```

## Author

**Bayane Miguel Singcol** — Fullstack Developer  
GitHub: [Bayane-max219](https://github.com/Bayane-max219)  
Portfolio: [portfolio-python-ten.vercel.app](https://portfolio-python-ten.vercel.app)
