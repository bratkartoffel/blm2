#!/bin/sh
set -e

export NODE_HOME=/c/devel/node-v16.15.0-win-x64
export PATH=$NODE_HOME:$PATH
/c/devel/node-v16.15.0-win-x64/node_modules/csso-cli/bin/csso $@
