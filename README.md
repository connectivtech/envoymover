# envoymover
To get up and running on Windows:  
* Install PHP to folder
* Add PHP folder to your path
* Copy php.ini-development to php.ini
* Edit php.ini  
* uncomment curl, mysqli, openssl extensions
* uncomment extension path (should just be left at /ext/)
* download https://curl.haxx.se/ca/cacert.pem
* edit php.ini line to point to file `curl.cainfo = C:\utils\php-7.2.0-Win32-VC15-x86\extras\ssl\cacert.pem
`


