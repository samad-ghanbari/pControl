FROM ubuntu:22.04
MAINTAINER ghanbari <ghanbari.samad@gmail.com>

# Installing packages
RUN apt update 
RUN apt upgrade -y
RUN DEBIAN_FRONTEND=noninteractive apt install -y apache2 php8.1
RUN DEBIAN_FRONTEND=noninteractive apt install -y libapache2-mod-php  php-xdebug php-gd php-curl php-xml php-mbstring php-zip
RUN DEBIAN_FRONTEND=noninteractive apt install -y php8.1-mysql php8.1-pgsql
RUN apt clean

# Startup Scripts
ADD start.sh /start.sh
RUN chmod 755 /start.sh

# root directories
RUN mkdir /var/www/html8080/
RUN chmod 775 /var/www/html8080/
RUN chown root:www-data /var/www/html8080/
RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf
COPY ./001-default.conf /etc/apache2/sites-available/001-default.conf
RUN ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN ln -s /etc/apache2/sites-available/001-default.conf /etc/apache2/sites-enabled/001-default.conf 
RUN sed -i '/^Listen 80/a Listen 8080' /etc/apache2/ports.conf

# domain name
RUN echo "ServerName 127.0.0.1" >> /etc/apache2/apache2.conf 

RUN a2enmod rewrite

EXPOSE 80 8080
ENTRYPOINT ["/start.sh"]

# sudo docker build -t lap .     # apache-php
# sudo docker run --rm -d  --network=host -v /home/.docker/www/pcontrol/:/var/www/html/ -v /home/.docker/www/:/var/www/html8080/  lap
#DNS 178.22.122.100, 185.51.200.2



