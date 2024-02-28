#!/usr/bin/env bash
# Mealmatch deployment helper for the local developer workplace
# Author: wizard@mealmatch.de
# =====================================================================================================================
# Export JS routes
console fos:js-routing:dump --target=web/static/prod/js-routing.js --env=prod --no-debug
console fos:js-routing:dump --target=web/static/dev/js-routing.js --env=dev --no-debug
# Build ./web/static/prod
yarn encore production
# Rsync to mealmatch-dev
rsync -av ./web/static/ mealmatch-dev@deploy.eu2.frbit.com:web/static/
exit 0
