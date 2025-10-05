# Uruchomienie projektu z Docker Compose

Ten projekt korzysta z konteneryzacji za pomocą **Docker** oraz **Docker Compose**.

## Wymagania

- Zainstalowany **Docker** i **Docker Compose**
- Dostęp do pliku `docker-compose.yml`
- Plik wzorcowy `/.env.dist`

> Jeśli nie masz Dockera: https://docs.docker.com/get-docker/

---

## Szybki start (TL;DR)

```bash
cp .env.dist .env
# edytuj .env i uzupełnij wymagane pola
docker-compose up -d --build
