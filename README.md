# NEWSA
## Steps to install
- run: cp .env.example .env
- update .env
```
DB_HOST=db
DB_DATABASE=newsa
...
NEWS_API_KEY=your-key
NEWS_API_BASE_URL=https://newsapi.org/v2
```
- run: docker compose up -d
- attach shell to newsa.com image and run: php artisan key:generate && php artisan migrate

### and here you go, happy hacking!