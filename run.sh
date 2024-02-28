docker stop EDCWD
docker rm EDCWD

docker run -dit -p 3004:80 --restart unless-stopped -v $(pwd):/var/www/html/cwd-files --name EDCWD edcwd
