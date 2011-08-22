#!/bin/bash

BASE_DIR=$(cd $(dirname $0); pwd)
LOCAL_BIN_FILE=$BASE_DIR/git-daily-local
HOME_BIN_FILE=$HOME/bin/git-daily

cat $BASE_DIR/git-daily | sed -e 's/@php_bin@/'$(which php | sed -e 's/\//\\\//g')'/' > $LOCAL_BIN_FILE && echo "Generated: $LOCAL_BIN_FILE"
chmod +x $LOCAL_BIN_FILE

# set

for p in $(echo $PATH | sed -s 's/:/ /g' )
do
    if [ "$p" = "$HOME/bin" ]
    then
        ln -snf $LOCAL_BIN_FILE $HOME_BIN_FILE && echo "Symlinked: $HOME_BIN_FILE -> $LOCAL_BIN_FILE"
    fi
done
