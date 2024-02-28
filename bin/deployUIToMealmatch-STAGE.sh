#!/usr/bin/env bash
# Mealmatch deployment helper for the local developer workplace
# Author: wizard@mealmatch.de
# =====================================================================================================================
console fos:js-routing:dump --target=web/static/prod/js-routing.js --env=prod --no-debug
# Build ./web/static/prod
yarn encore production
# Rsync to mealmatch-stage
rsync -av ./web/static/ mealmatch-stage@deploy.eu2.frbit.com:web/static/
exit 0
