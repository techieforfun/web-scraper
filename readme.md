<p align="center">
<img src="https://travis-ci.org/TechieForFun/web-scraper.svg?branch=master" alt="Build Status">
</p>

# Web Scraper
## Table of contents
- [Intro](#intro)
- [Development](#development)
- [Documenting](#documenting)
- [Testing](#testing)
- [Continuous Integration](#continuous-integration)
- [Deployment](#deployment)
- [Usage](#usage)

---

### Intro
Web is a messy place, So scraping to find out what's what is a super difficult process.
Current application using [DOMDocument](https://secure.php.net/manual/en/class.domdocument.php) & [XPath](https://secure.php.net/manual/en/class.domxpath.php) to go through XML resources. But there are other options to extract data from these resources like using Regular Expressions and ...

Currently IMDB Movies are supported as a model.

---

### Development
- Using TDD approach (Unit & Feature testing)
- Using RESTful API
- Using polymorphism for the Link DB entity
- Using Scraper Helper class for the sake of dependency injection (parseUrl, downloadResource, processHtml)

---

### Documenting
Using [PHPDocs](https://www.phpdoc.org/).

---

### Testing
Using [PHPUnit](https://phpunit.de/). Run (from the root of project):
- `./vendor/bin/phpunit`

---

### Continuous Integration
Using [Travis-CI](https://travis-ci.com/): config file is `./.travis.yml`

---

### Deployment
- [system requirement](https://laravel.com/docs/5.7/installation#server-requirements)
  - you need [cURL](https://secure.php.net/manual/en/curl.installation.php), too
- install [composer](https://getcomposer.org/download/)
- clone the repo
- copy `.env.example` to `.env` and then config DB info (name, username, password or maybe driver if you wanna use something other than MySQL) inside it
- run:
  - `composer install`
  - `php artisan key:generate`
  - `php artisan migrate`
  - `php artisan serve`

---

### Usage
There are currently 5 actions: (OpenAPI specification is in the roadmap :))
- List the IMDB movies => `GET  {host:port}/api/imdb-movie`
- Create an IMDB movie => `POST  {host:port}/api/imdb-movie  url={imdb_movie_url}`
- Get a specific IMDB movie => `GET  {host:port}/api/imdb-movie/{id}`
- Update an existing IMDB movie => `PATCH  {host:port}/api/imdb-movie/{id}  url={imdb_movie_url}`
- Delete an IMDB movies => `DELETE  {host:port}/api/imdb-movie/{id}`

___Note___ that currently there is no implementation for OAuth or other authentication system in the current version, so you can send the requests without going through any authentication process.
