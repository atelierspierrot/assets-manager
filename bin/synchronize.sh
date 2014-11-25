#!/usr/bin/env bash

if [ $# -eq 0 ]; then
    echo "This script will synchronize some files of the 'gh-pages' branch with versions of another branch"
    echo "(default is to also synchronize the 'phpdoc/' directory from the 'dev' branch)"
    echo
    echo "usage: $0 <branch_name> [phpdoc = true]"
    echo
    exit 1
fi

_BRANCH=$1
_PHPDOC=${2:true}

# composer.json
git mv _includes/composer.json ./composer.json
git checkout $_BRANCH -- composer.json
git mv ./composer.json _includes/composer.json

# ChangeLog
git checkout $_BRANCH -- CHANGELOG.md

# README
git checkout $_BRANCH -- README.md

# phpdoc/
if $_PHPDOC || [ "$_PHPDOC" == 'true' ]; then
    git checkout origin/dev -- phpdoc/
fi

echo "_ ok, you may now commit your changes"
