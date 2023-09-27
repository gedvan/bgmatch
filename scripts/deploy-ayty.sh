#!/bin/bash

# NOT WORKING YET!

PROJECT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." &> /dev/null && pwd)

cd $PROJECT_DIR
npm run build
rsync -azP --port=2222 --include=app,artisan,bootstrap,composer.*,database,frontend,public,resources,routes,storage,vendor . rra100@ssh.web1.us.cloudlogin.co:/home/www/bgmatch.ayty.org

