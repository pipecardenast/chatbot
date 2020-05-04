# chatbot
This is the REST API that supports the Chat that is pending to be developed. This was develped with Lumen and all the dependencies were added into the composer.json file

# Currency API Service
Finally a free API service was used for the currencies convertion due to the suggested need a plan to do currency convertions. 
https://free.currconv.com/api/v7


# Installaton

Please make sure the server meets the following requirements:
* PHP >= 7.2
* OpenSSL PHP Extension
* PDO PHP Extension
* Mbstring PHP Extension

1. Run composer install

2. Rename the file .env.example in order to remove the .example part and add the DB connection values

3. To create the DB please run the command: php artisan migrate

4. To start a server for test purposes you can use the command: php -S localhost:8000 -t public


# Endpoints

## GET /exchange
GET /api/v1/users/exchange?from=GBP&to=COP&amount=110 HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache

## POST /signup
POST /signup HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache

{
  "name": "Andres Cárdenas",
  "email": "andres.cardenas@jobsity.com",
  "password": "testing",
  "currency": "COP"
}

## POST /login
POST /login HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache

{
  "email": "felipe.cardenas@jobsity.com",
  "password": "testing"
}

## PUT /setcurrency
PUT /setcurrency HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache
api-token: Qzpox4JAyvuUKQRPha7ZDX2cwDLUUwQxhzGrRqSMNTVGVE76YOIzb4cVRQHt

{
  "currency": "USD"
}

## PUT /deposit
PUT /deposit HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache
api-token: Qzpox4JAyvuUKQRPha7ZDX2cwDLUUwQxhzGrRqSMNTVGVE76YOIzb4cVRQHt

{
  "amount": "1000",
  "currency": "USD"
}

## PUT /withdraw
PUT /withdraw HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache
api-token: Qzpox4JAyvuUKQRPha7ZDX2cwDLUUwQxhzGrRqSMNTVGVE76YOIzb4cVRQHt

{
  "amount": "1000",
  "currency": "USD"
}

## GET /balance
GET /balance HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache
api-token: Qzpox4JAyvuUKQRPha7ZDX2cwDLUUwQxhzGrRqSMNTVGVE76YOIzb4cVRQHt

