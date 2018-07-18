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

function execute_arbitraty(){

	if [ -t 1 ] ; then
		docker exec -u bus115 -t -i $CONTAINER_TOOLBOX_ID $@
		RET=$?
	else
		echo "$Name Warning: running in a non-interactive environment. Some features may not work"
		docker exec -u bus115 $CONTAINER_TOOLBOX_ID $@
		RET=$?
	fi
	return $RET
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
    echo ""
    echo "composer install --prefer-source --no-interaction" |  docker exec -u bus115 -i  bus115_bus115_1 /bin/bash
    echo ""
    if [[ $1 == "--on-production" ]]; then
      echo "REMOVING XDEBUG"
      echo "rm -f /usr/local/etc/php/conf.d/xdebug.ini" |  docker exec -i  bus115_bus115_1 /bin/bash
      echo "composer development-disable" |  docker exec -u bus115 -i  bus115_bus115_1 /bin/bash
    else
      echo "composer development-enable" |  docker exec -u bus115 -i  bus115_bus115_1 /bin/bash
    fi
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

function rebuild_apidoc(){
    echo "apidoc -i src/ -o public/apidoc/" |  docker exec -u bus115 -i  bus115_bus115_1 /bin/bash
}

function command_tests(){
    echo "vendor/bin/phpunit ./tests/" |  docker exec -i  bus115_bus115_1 /bin/bash
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
	exec)
		shift
		execute_arbitraty $@
		exit $?
		;;
	tests)
		command_tests
		exit
		;;
	logs)
		logs
		exit
		;;
	apidoc)
		rebuild_apidoc
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
