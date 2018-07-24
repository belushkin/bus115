#Synopsis 
This is the application for traffic control for Kyiv users

#Installation
```bash
./toolbox.sh up
```

#Useful commands
```bash
./vendor/bin/doctrine-migrations generate
vendor/bin/doctrine orm:schema-tool:update --force --dump-sql
```
###Tests
```bash
./toolbox.sh tests
```
###CLI mode
```bash
./toolbox.sh exec php console.php GET "/api/v1/getstops?lat=4711&lng=4567"
```
###APIDOC
```bash
./toolbox.sh apidoc
```

### SSL CERTIFICATE
```bash
~/workspace $ sudo http-server ssl -p 80
~/workspace $ sudo letsencrypt certonly --webroot -w ssl/ -d bus115.kiev.ua
~/workspace/haproxy $ docker build -t haproxy .
~/workspace/haproxy/private $ sudo cat /etc/letsencrypt/live/bus115.kiev.ua-0001/fullchain.pem /etc/letsencrypt/live/bus115.kiev.ua-0001/privkey.pem > bus115.kiev.ua.pem
~/workspace/haproxy $ docker run --net=host -it haproxy
~/workspace/haproxy $ docker run -d --net=host --restart always haproxy
```

##Contributors @belushkin

##License MIT License
