FROM daocloud.io/php:5.6-cli

COPY . /qa
WORKDIR /qa
CMD [ "php", "./index.php" ]
