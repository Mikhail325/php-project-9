
### Hexlet tests and linter status:
[![Actions Status](https://github.com/Mikhail325/php-project-9/workflows/hexlet-check/badge.svg)](https://github.com/Mikhail325/php-project-9/actions)
[![Actions Status](https://github.com/Mikhail325/php-project-9/actions/workflows/github-actions.yml/badge.svg)](https://github.com/Mikhail325/php-project-9/actions)
<a href="https://codeclimate.com/github/Mikhail325/php-project-9/maintainability"><img src="https://api.codeclimate.com/v1/badges/a7e2b5652b577e578ee8/maintainability" /></a>

# Анализатор страниц
Page Analyzer – сайт, который анализирует указанные страницы на SEO пригодность по аналогии с PageSpeed Insights

Пример реализации сайта: https://php-project-9.onrender.com/

## Минимальные требования
* Composer >= 2.2;
* PHP >= 8.1;
* GNU Make >= 4.3;
* PostgreSQL >= 14.8;
* Docker >= 24.0.


## Инструкции по установке

С клонируйте репозиторий с GitHub и перейдите в директорию проекта используя команды:
```
git clone https://github.com/Mikhail325/php-project-9.git
cd php-project-9
```
### Подключения БД к приложению

Заполните данные о БД в строку имеющий следующий формат:
{provider}://{user}:{password}@{host}:{port}/{db}
Выполните команду в терминале подставив получившуюся строку
```
export DATABASE_URL=postgresql://janedoe:mypassword@localhost:5432/mydb
```

### Инструкции по установке c помощью GNU Make

Для установки зависимостей используйте команду **make install**.
Для запуска сайта используйте команду **make start**.

### Инструкции по установке c помощью Docker

Для сборки образа в директории с Dockerfile используйте команду, указанную ниже:
```
docker build -t userName/page-analis .
```

### запуска сайта используйте команду:
```
docker run -p 8000:8000 userName/page-analis
```
### Проверка работы сайта

Откройте в браузере ссылку **http://localhost:8000** и убедитесь, что сайт открылся.
