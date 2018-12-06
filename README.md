# Synopsis 
This is the application for traffic control for Kyiv users

# Installation
```bash
./toolbox.sh up --on-production
```
# Useful commands
```bash
./vendor/bin/doctrine-migrations generate
vendor/bin/doctrine orm:schema-tool:update --force --dump-sql
./toolbox.sh logs
```
### Tests
```bash
./toolbox.sh tests
```
### CLI mode
```bash
./toolbox.sh exec php console.php GET "/api/v1/getstops?lat=4711&lng=4567"
```
### APIDOC
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
In order to check certificate:
```
~/workspace/haproxy/private (master) $ sudo ssl-cert-check -c bus115.kiev.ua.pem
```

### Portainer
```bash
docker volume create portainer_data
docker run -d -p 9000:9000 -v /var/run/docker.sock:/var/run/docker.sock -v portainer_data:/data portainer/portainer
```

###
Webhook validation
```
curl -H "Content-Type: application/json" -X POST "https://bus115.kiev.ua/api/v1/webhook" -d '{"object": "page", "entry": [{"messaging": [{"message": "TEST_MESSAGE"}]}]}'
```

### CircleCI
https://circleci.com/gh/belushkin

## Contributors 
@belushkin

## License MIT License
