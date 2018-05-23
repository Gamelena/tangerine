#!/usr/bin/env bash
unamestr=`uname`

if [[ "$unamestr" == 'Darwin' ]]; then
    if ! [ -x "$(command -v greadlink)" ]; then
      echo 'Error: greadlink is not installed, please install coreutils with brew or macport.' >&2
      exit 1
    fi
    FILENAME=~/.bash_profile
    TANGERINE_PATH=`echo $(dirname $(greadlink -e $PWD))`
else
    FILENAME=~/.bashrc
    TANGERINE_PATH=`echo $(dirname $(readlink -e $PWD))`
fi


if grep -i  "TANGERINE_PATH" $FILENAME; then
	echo "Se encontró referencia a TANGERINE_PATH en $FILENAME, no se hace nada."
else
	DATE=$(date +"%Y-%m-%d-%H-%M-%S")
	echo "Creando backup de $FILENAME en $FILENAME $FILENAME.$DATE"
	cp $FILENAME $FILENAME.$DATE

	echo "Se intentará agregar las variables de entorno en $FILENAME"
	echo "# Generado por Tangerine" >> $FILENAME
	echo "export TANGERINE_PATH=$TANGERINE_PATH"  >> $FILENAME
	echo "export PATH=\$PATH:\$TANGERINE_PATH/bin"  >> $FILENAME
	echo "export APPLICATION_ENV=development" >> $FILENAME
	bash
fi

if grep -i  "zendframework1/bin" $FILENAME; then
	echo "Se encontró referencia a zendframework1/bin en $FILENAME, no se hace nada."
else 
	DATE=$(date +"%Y-%m-%d-%H-%M-%S")
	echo "Creando backup de $FILENAME en $FILENAME $FILENAME.$DATE"
	cp $FILENAME $FILENAME.$DATE
	ZF_PATH="$TANGERINE_PATH/vendor/zendframework/zendframework1"
	echo "Copiando ejecutable zf"
	cp $ZF_PATH/bin/zf.sh  $ZF_PATH/bin/zf
	
	echo "Se intentará agregar las variables de entorno en $FILENAME"
	echo "# Generado por Tangerine" >> $FILENAME
	echo "export PATH=\$PATH:$ZF_PATH/bin"  >> $FILENAME
	bash
fi
