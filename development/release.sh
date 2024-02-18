#!/bin/bash
set -ex

readonly next_version="$1"

if [[ -z "$next_version" ]]; then
	echo "Usage: $0 <version>" >&2
	exit 1
fi

# work on develop branch only
git checkout develop

# determine previous version number
readonly prev_version=$(grep game_version include/game_version.inc.php  | cut -d"'" -f4)

# replace version number
sed -i "s/$prev_version/$next_version/" include/game_version.inc.php

# create new changelog entry
sed -i -e "/^## \[Unreleased\]/a - no changes yet\n\n## [$next_version] - $(date +%Y-%m-%d)" \
    -e "/^## \[Unreleased\]/{G;}" \
    CHANGELOG.md

# change link for unreleased diff
sed -i "s/v$prev_version...HEAD/v$next_version...HEAD/" CHANGELOG.md

# insert link for new release
sed -i -e "/^\[Unreleased\]/a [$next_version]: https://github.com/bratkartoffel/blm2/compare/v$prev_version...v$next_version" \
    -e "/^\[Unreleased\]/{G;}"  \
    CHANGELOG.md

# commit
git add include/game_version.inc.php CHANGELOG.md
git commit -m "version to release"
git tag -m "Release v$next_version" v$next_version

# merge into master
git checkout master
git merge --ff-only develop

# push upstream
git push --atomic origin develop master tags/v$next_version

# change back to develop branch
git checkout develop
git branch -D master

# create patch file
git diff --stat --patch --binary v$prev_version v$next_version | xz - >patch_v$prev_version-v$next_version.patch.xz