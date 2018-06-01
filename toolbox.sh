#!/usr/bin/env bash
cd `dirname $0`
if [ -z ${VOLUME_DIR+x} ]; then
	export VOLUME_DIR=`pwd`
fi
if [ -z ${PROJECT_NAME+x} ]; then
	export PROJECT_NAME=zf
fi

export ZF_DOCKER_DIR=`pwd`

MYSQL_ROOT_PASS=rootpass

CONTAINER_TOOLBOX_ID=`docker ps --format '{{.ID}}\t{{.Names}}' | grep zf_zf_1 | cut -f1`

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
    docker-compose -f docker-compose.yml up	$@
}

function command_shutdown()
{
	docker-compose -f docker-compose.yml down $@
}

while (( "$#" )); do
  case "$1" in
	boot|up)
		shift
		command_boot $@
		exit
		;;
	down)
		shift
		command_shutdown $@
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
