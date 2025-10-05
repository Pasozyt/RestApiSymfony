# Przykład REST API (Symfony) — Docker + Nginx + MySQL

To repozytorium prezentuje **przykładowe REST API oparte na Symfony**, uruchamiane w kontenerach **Docker**. Całość środowiska składa się z usług: **Nginx** (serwer WWW), **PHP/Symfony (app)** oraz **MySQL** (baza danych), zarządzanych przez **Docker Compose**.

---

## Wymagania

- Zainstalowany **Docker** i **Docker Compose**
- Dostęp do plików: `docker-compose.yml` oraz `.env.dist`

> Jeśli nie masz Dockera: https://docs.docker.com/get-docker/

---

## Szybki start (TL;DR)

```bash
cp .env.dist .env
# edytuj .env i uzupełnij wymagane pola
docker-compose up -d --build
