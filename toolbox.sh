#!/usr/bin/env bash
cd `dirname $0`
if [ -z ${VOLUME_DIR+x} ]; then
	export VOLUME_DIR=`pwd`
fi
if [ -z ${PROJECT_NAME+x} ]; then
	export PROJECT_NAME=bus115
fi

export BUS115_DOCKER_DIR=`pwd`
export VOLUME_DIR="$BUS115_DOCKER_DIR"

MYSQL_ROOT_PASS=rootpass

CONTAINER_TOOLBOX_ID=`docker ps --format '{{.ID}}\t{{.Names}}' | grep bus115_bus115_1 | cut -f1`

function ssh_to()
{
	if [ -z $1 ]; then
		# Connect to toolbox
		docker exec -t -i $CONTAINER_TOOLBOX_ID /bin/bash #-u oht
	else
		# search container
		CONTAINER=`docker ps --format 'table {{.Names}}' | grep $1 | tr -d ' '`
		if [ -e $CONTAINER ]
		then
		    echo "Container '$1' is not running!. Please select container from this list:"
		    docker ps
		else
		    docker exec -t -i $CONTAINER /bin/bash
		fi
	fi
}

function command_boot()
{
    # Start the developer environment
    docker-compose -f docker-compose.yml up &

    echo -n "Waiting for the services to initialize.. "
    while [[ ! $(docker ps | grep bus115_bus115_1) ]] ; do
        echo -n "."
        sleep 1
    done
    if [ "$PHP_XDEBUG" = "0" ]; then
      echo "REMOVING XDEBUG"
      echo "rm -f /usr/local/etc/php/conf.d/xdebug.ini" |  docker exec -i  bus115_bus115_1 /bin/bash
    fi
    echo ""
    echo "composer install --prefer-source --no-interaction" |  docker exec -i  bus115_bus115_1 /bin/bash
    echo ""
}

function command_rebuild(){
	DOCKERS=`cat images_list.txt`
	for DOCKER in $DOCKERS
	do
		echo "Building image $DOCKER"
		docker build --no-cache -t "$DOCKER" .
	done
	docker-compose -f docker-compose.yml build
}

function command_shutdown()
{
	docker-compose -f docker-compose.yml down $@
}

function logs(){
	tail -f  $VOLUME_DIR/volume/logs/*
}

while (( "$#" )); do
  case "$1" in
	boot|up)
		shift
		command_boot $@
		exit
		;;
	rebuild)
		command_rebuild;
		exit;
		;;
	down)
		shift
		command_shutdown $@
		exit
		;;
	logs)
		logs
		exit
		;;
    ssh|connect)
		ssh_to $2
		exit
		;;
	clean_docker)
					docker container rm -v -f `docker container ls -aq`
					docker image rm -f `docker images -q`
					exit;
					;;
	 *)
	 COMMAND=$@
	 exit $?
	 ;;
  esac
done
