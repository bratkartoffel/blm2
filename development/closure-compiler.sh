#!/bin/sh
set -e

export JAVA_HOME=/c/devel/jdk-17.0.5+8
export PATH=$JAVA_HOME:$PATH
$JAVA_HOME/bin/java -jar /c/devel/closure-compiler-v20230103.jar $@
