
```bash
cp api/.env.example api/.env
```

```bash
docker compose up -d --build
```

```bash
docker compose exec app composer install
```

```bash
docker compose exec app php artisan key:generate
```

```bash
docker compose exec app php artisan migrate
```

```bash
docker compose restart worker
```


Frontend běží na http://localhost:3001
