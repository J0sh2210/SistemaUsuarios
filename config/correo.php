<?php

require __DIR__ . "/../vendor/autoload.php";


use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(
    __DIR__ . "/.."
);

$dotenv->load();

 return [
"Host" => $_ENV["MAIL_HOST"],
"Port"=> $_ENV["MAIL_PORT"],
"Username" => $_ENV["MAIL_USERNAME"],
"Password" =>$_ENV["MAIL_PASSWORD"],
"Name" =>$_ENV["MAIL_FROM_NAME"]
 ];
