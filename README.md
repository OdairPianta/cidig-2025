# cidadania-digital
Uma iniciativa internacional para a promoção e educação para Cidadania Digital

Para preparar um ambiente isolado com PHP 8.4, executar os testes e gerar
relatórios de cobertura, consulte [TESTING.md](TESTING.md).

## Requirementos 
- [Docker](https://www.docker.com/get-started)
- [Composer](https://getcomposer.org/download/)

## Como rodar o projeto localmente

### Criar o arquivo .env e instalar as dependências
```bash
cp .env.example .env && composer install
```

### Iniciar o container utilizando Laravel Sail
```bash
./vendor/bin/sail up -d
```

### Instalar as dependências do projeto
```bash
./vendor/bin/sail php artisan key:generate && ./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```

### Executar as migrations e seeders
```bash
./vendor/bin/sail php artisan migrate && ./vendor/bin/sail php artisan db:seed
```

---

O projeto pode ser acessado em http://localhost

