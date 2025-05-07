  server {

        server_name jaimedigitalstudio.com www.jaimedigitalstudio.com;

        # IMPORTANT: This root assumes you copied the *entire* 'dist' folder
        # from your local machine into /var/www/jaimedigitalstudio.com
        root /var/www/jaimedigitalstudio.com/dist;
        index index.html index.htm;

        location / {
            try_files $uri $uri/ /index.html; # Handles SPA routing if needed, otherwise just serves files
        }

        # Optional: Add custom error pages
        # error_page 404 /404.html;
        # location = /404.html {
        #     internal;
        # }

        # Optional: Add headers for security, caching etc.
        # add_header X-Frame-Options "SAMEORIGIN";
        # add_header X-Content-Type-Options "nosniff";
        # add_header Referrer-Policy "strict-origin-when-cross-origin";
        # location ~* \.(?:css|js)$ {
        #   expires 1y;
        #   add_header Cache-Control "public";
        # }
        # location ~* \.(?:png|jpg|jpeg|gif|ico|webp|svg)$ {
        #   expires 1M;
        #   add_header Cache-Control "public";
        # }

    listen [::]:443 ssl ipv6only=on; # managed by Certbot
    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/jaimedigitalstudio.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/jaimedigitalstudio.com/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot


}
  server {
    if ($host = www.jaimedigitalstudio.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    if ($host = jaimedigitalstudio.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


        listen 80;
        listen [::]:80;

        server_name jaimedigitalstudio.com www.jaimedigitalstudio.com;
    return 404; # managed by Certbot




}

