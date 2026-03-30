A demo application for CRUD operations on contacts. The index allows full-text filtering and paginating the results. The API also accepts XML for bulk import using queues. 

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


Frontend on http://localhost:3001
