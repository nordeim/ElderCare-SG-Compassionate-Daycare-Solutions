# Starting the Backend PHP "serve" (container and non-container)

This guide describes how to start the Laravel backend so the frontend can connect to it. It covers two common workflows:

- Container mode (recommended for full-stack local development): use Docker Compose to run backend + DB + Redis + MeiliSearch + Mailhog.
- Non-container mode (quick local PHP development): run PHP's built-in server via `php artisan serve` on your host machine.

Each section includes exact commands and verification steps.

---

## Quick overview

- Default backend bind: `0.0.0.0:8000` (the docker-compose service maps host port 8000 to container port 8000).
- Frontend expects the API at `http://localhost:8000/api/v1` by default (see `docker-compose.yml` and frontend env).

---

## A — Container mode (Docker Compose) — recommended for running the full stack

Why use this:
- Starts MySQL, Redis, MeiliSearch and other dependencies alongside the backend.
- Mirrors staging environment more closely.
- Easier for other developers and CI to reproduce.

1) From the repository root, start services:

```bash
# Start all services (foreground; press Ctrl+C to stop)
docker compose up

# Or run in detached/background mode
docker compose up -d
```

2) To bring up only backend and required infra (faster):

```bash
# Start DB, Redis and backend (detached)
docker compose up -d mysql redis backend
```

3) Check running containers and logs:

```bash
# List running containers
docker ps

# Follow backend logs
docker compose logs -f backend
```

4) Enter the backend container to run artisan commands interactively:

```bash
docker compose exec backend bash
# inside container now
# Generate app key (if not present)
php artisan key:generate
# Run migrations (careful in non-dev environments)
php artisan migrate --force
# Create storage symlink if necessary
php artisan storage:link
```

5) Verify the backend HTTP server is reachable from the host machine:

```bash
curl -I http://localhost:8000
curl -I http://localhost:8000/api/v1
```

Notes and tips:
- The `backend` service in `docker-compose.yml` runs `php artisan serve --host=0.0.0.0 --port=8000`, which listens on all interfaces so the host (and other containers) can reach it.
- If you change the backend port, update the frontend's `NEXT_PUBLIC_API_URL` accordingly (in `docker-compose.yml` or `.env` for the frontend).
- If frontend runs in a separate container and the backend is on the host (or vice versa), use the correct host name:
  - Containers talking to containers: use service name (e.g., `http://backend:8000`) in docker networks.
  - Host -> container (Linux): `http://localhost:8000` works when compose publishes the port.
  - Host -> container (Mac/Windows with Docker Desktop): `http://localhost:8000` works as well.

---

## B — Non-container mode (artisan serve) — fast local dev

Use this when you want to run PHP on your host machine without Docker. You still need a database and Redis; you can use Docker for those or configure local services.

1) Install dependencies and prepare environment:

```bash
cd backend
composer install --no-interaction --prefer-dist
cp .env.example .env
php artisan key:generate
```

2) Configure `.env` for local DB/Redis. Options:
- Use local MySQL/Redis instances and set `DB_HOST`, `DB_PORT`, etc.
- Or use Docker for DB/Redis only, and keep the app on host. Example Docker command to start just DB and Redis:

```bash
# from repo root
docker compose up -d mysql redis
```

3) Run migrations and start server:

```bash
php artisan migrate
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

4) Verify (from host or frontend):

```bash
curl -I http://127.0.0.1:8000
curl -I http://127.0.0.1:8000/api/v1
```

Notes:
- If the frontend runs in Docker and backend is on host, the frontend may need to talk to `host.docker.internal` (on Mac/Windows). On Linux, mapping host services to containers varies — using Docker Compose for both frontend and backend avoids these issues.

---

## Environment variables and frontend connectivity

- The frontend reads its API URL from `NEXT_PUBLIC_API_URL`. Default in `docker-compose.yml` is `http://localhost:8000/api/v1`.
- If you run the backend on a different host/port, update the frontend env and rebuild/start the frontend.

---

## Health checks and quick smoke tests

- A simple HTTP check:

```bash
curl -fsS --max-time 5 http://localhost:8000 || echo "backend unreachable"
```

- List routes (inside container or local app):

```bash
php artisan route:list
```

- Run unit tests inside container:

```bash
docker compose exec backend ./vendor/bin/phpunit --testsuite=Unit
```

---

## Troubleshooting

- "Connection refused" from frontend:
  - Ensure backend container is up and port 8000 is published (docker ps shows port mapping).
  - Check backend logs: `docker compose logs -f backend`.
  - Confirm the frontend's `NEXT_PUBLIC_API_URL` is correct.

- Database connection errors:
  - Verify DB container (`mysql`) is healthy and env vars match (DB_HOST, DB_USER, DB_PASSWORD).
  - If using host MySQL, ensure `DB_HOST` points to the correct host and the DB accepts connections from the app host.

- If `php artisan migrate` fails:
  - Confirm DB credentials and database existence; check MySQL container logs.

---

## Quick commands summary

```bash
# Start full stack (detached)
docker compose up -d

# Start only backend and infra
docker compose up -d mysql redis backend

# Exec into backend container
docker compose exec backend bash

# From backend (container or host):
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

---

## What I recommend

- Use Docker Compose for consistent local stacks (recommended).
- Use `php artisan serve` on host for quick, narrow development sessions when you already have DB/Redis running.
- If you want, I can add a tiny `/health` endpoint to the API (or a dedicated artisan `health` command) so the frontend or orchestrator can easily verify the backend is ready.

---

## File location

Saved as `docs/start-backend-serve.md` in this repository.

---

## Kubernetes readiness & liveness probes

Below are minimal example snippets you can drop into a Pod/Deployment spec to probe the application using the `/health` endpoint.

Readiness probe (checks whether the app is ready to receive traffic):

```yaml
readinessProbe:
  httpGet:
    path: /health
    port: 8000
  initialDelaySeconds: 5
  periodSeconds: 10
  timeoutSeconds: 2
  failureThreshold: 3
```

Liveness probe (checks application liveliness — consider using a cheaper `/live` endpoint if you add one):

```yaml
livenessProbe:
  httpGet:
    path: /health
    port: 8000
  initialDelaySeconds: 30
  periodSeconds: 30
  timeoutSeconds: 2
  failureThreshold: 3
```

Notes:

- Readiness should be used by the load balancer to decide when to send traffic. It is okay for readiness to include DB+cache checks.
- Liveness should be cheap — if your `/health` includes external network calls (Mailchimp, third-party), prefer adding a `/live` endpoint that only checks in-process state for liveness.
- Tune probe timings to your environment; the examples above are conservative defaults for most dev/staging setups.
