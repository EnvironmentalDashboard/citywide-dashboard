docker stop EDCWD
docker rm EDCWD

# create a "includes" directory containing the relavents files & add into volume $(pwd)/../includes, 
# As these can not be pushed to the production server

docker run \
 --restart unless-stopped \
 -dit -p 3004:80 \
 -v $(pwd):/var/www/html/cwd-files \
 -v $(pwd)/../includes:/var/www/html/includes \
 --link ch-mysql:ch-mysql \
 --name EDCWD edcwd

