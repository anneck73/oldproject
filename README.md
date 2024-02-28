Mealmatch:WebApp
================

A Symfony project created on November 18, 2016, 10:48 am.

## Install ##

The quick'n dirty way.

It is recommended to setup the database credentials to *match* these values.
Edit app/config/parameters.yml.dist:

```bash
 database_username: mealmatch
 database_password: changeme
```

Then execute bin/setup.sh and bin/createDEV.sh
 
```php
bin/setup.sh
bin/createDEV.sh
console server:run mealmatch.local
```

See result:
http://mealmatch.local:8000/

## Documentation ##

https://docs.google.com/document/d/1d_9f17wbxkLBDC4ToKVtGj3tlakgHy_i2dCERd1YIlM/edit?usp=sharing



