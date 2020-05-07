#!/usr/bin/env bash
rm -rf ./yui/build
shifter --walk --recursive --no-lint & p1=$!
git archive --format=zip --prefix=echo360attoplugin/ HEAD > echo360attoplugin-"$1".zip & p2=$!

wait $p1 && wait $p2
