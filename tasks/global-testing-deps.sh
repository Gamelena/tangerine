wget -c https://www.phpunit.de/phpunit-4.8.18.phar
chmod +x phpunit-4.8.18.phar
alias phpunit=phpunit-4.8.18.phar

wget -c https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
chmod +x phpcs.phar
alias phpcs=phpcs.phar

wget -c https://phar.phpunit.de/phploc.phar
chmod +x phploc.phar
alias phploc=phploc.phar

wget -c http://static.pdepend.org/php/latest/pdepend.phar
chmod +x pdepend.phar
alias pdepend=pdepend.phar

wget -c http://static.phpmd.org/php/latest/phpmd.phar
chmod +x phpmd.phar
mv phpmd.phar /usr/bin/phpmd
alias phpmd=phpmd.phar

wget https://github.com/theseer/phpdox/releases/download/0.8.0/phpdox-0.8.0.phar
chmod +x phpdox-0.8.0.phar
alias phpdox=phpdox-0.8.0.phar

wget https://phar.phpunit.de/phpcpd.phar
chmod +x phpcpd.phar
alias phpcpd=phpcpd.phar

