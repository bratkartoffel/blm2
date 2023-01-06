#!/bin/sh
set -e

export NODE_HOME=/c/devel/node-v18.13.0-win-x64
export PATH=$NODE_HOME:$PATH
$NODE_HOME/node_modules/csso-cli/bin/csso $@
