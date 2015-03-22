# Introduction #

This page explains the configuration options.


# Configuration.  Release >= 0.1.1 #

## Local database ##

Locate and open file **class.appmedia.php** and search function _construct()_

  * Modify variable _$this->dsn_ with your local access.

## Scan directories ##

New **Configuración tab** added. It's not necessary edit any file, only run once **Crear base de datos** for store configuration in database.

## Database management ##

New **Configuración tab** added for this feature.

# Configuration v0.1.0 #

## Local database ##

Locate and open file **class.appmedia.php** and search function _construct()_

  * Modify variable _$this->dsn_ with your local access.

## Scan directories ##

Locate and open file **class.appmedia.php** and search function _construct()_

  * Modify the array variable _$this->dirSources_ with your directories to be scanned.

## Database management ##

Locate and open file **class.appmedia.php** and search function _construct()_

  * Modify boolean variable _$this->manageBBDD_. Set **true** to enable Create or Drop database, set to **false** disable Create or Drop functions.