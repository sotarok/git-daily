#!/bin/bash

BASE_DIR=$(cd $(dirname $0); pwd)
LOCAL_BIN_FILE=$BASE_DIR/git-daily-local
cat $BASE_DIR/git-daily | sed -e 's/@php_bin@/'$(which php | sed -e 's/\//\\\//g')'/' > $LOCAL_BIN_FILE
chmod +x $LOCAL_BIN_FILE
