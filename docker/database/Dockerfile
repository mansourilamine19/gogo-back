# docker/database/Dockerfile
ARG VERSION
FROM mysql:${VERSION}
#CMD ['mysqld', '--character-set-server=utf8mb4', '--collation-server=utf8mb4_unicode_ci', '--default-authentication-plugin=mysql_native_password']
ADD mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf

EXPOSE 3306