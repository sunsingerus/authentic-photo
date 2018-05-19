setup DB access

```bash
vim .env
```

setup nginx
```
server {
    listen 80;
    server_name localhost;

    root /home/user/dev/authentic/photoviewer/server;
    error_log       /var/log/nginx/error.log;
    access_log      /var/log/nginx/access.log;
    
    index index.html;
    
    location ~ /api/ {
        if (!-e $request_filename) {rewrite ^/(.*)$ /public/index.php?q=$1 last;}
    }
    location ~ \.php$ {
                try_files $uri =404;
                include fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
    }
}

server {
    listen 8080;
    server_name     localhost;
    error_log       /var/log/nginx/error.log;
    access_log      /var/log/nginx/access.log;

    root            /home/user/dev/authentic/photoviewer/server/public/uploads;

    location ~* (.+\.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar|woff|woff2|ttf|eot|svg))$ {
        add_header 'Access-Control-Allow-Origin' '*';
        add_header 'Access-Control-Allow-Credentials' 'true';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type,Accept,Origin,User-Agent,DNT,Cache-Control';
        try_files $uri =404;
    }
}

```

run migrations

```bash
cd server
php partisan migrate:up
php partisan run:seed
```

check MySQL tables created
```txt
mysql> show tables;
+-----------------------+
| Tables_in_photoviewer |
+-----------------------+
| access_tokens         |
| logs                  |
| media_files           |
| migrations            |
| refresh_tokens        |
| rights                |
| roles                 |
| roles_to_rights       |
| seeds                 |
| users                 |
+-----------------------+
10 rows in set (0,00 sec)
```


Now CD to `client` folder and check CLI bash-script API endpoints
```bash
ls -l client/
total 36
-rwxrwxr-x 1 user user 380 May 19 14:30 1_get_token_aka_login.sh
-rwxrwxr-x 1 user user 349 May 19 14:38 2_upload_file.sh
-rwxrwxr-x 1 user user 217 May 19 14:38 3_get_files_list.sh
-rwxrwxr-x 1 user user 311 May 19 14:39 4_get_file_details_by_id.sh
-rwxrwxr-x 1 user user 567 May 19 15:22 5_get_file_body_by_id.sh
-rwxrwxr-x 1 user user 395 May 19 15:32 6_update_file_by_id.sh
-rwxrwxr-x 1 user user 317 May 19 14:39 7_delete_file_by_id.sh
-rw-rw-r-- 1 user user 174 May 19 15:28 access_token
-rw-rw-rw- 1 user user 637 May 19 12:51 example.png
```

