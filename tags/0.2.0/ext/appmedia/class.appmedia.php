<?php
/**
 * Clase AppMedia
 *
 * Esta clase facilita la capa de abstraccion a la aplicacion AppMedia
 *
 * @author Ricard Forner
 * @version 0.1.2
 * @package appmedia
 */

require_once('class.appmediabase.php');

class AppMedia extends AppMediaBase {

	public function __construct() {
		$this->dsn = "sqlite:/mnt/cfinterno/usr/www/bbdd/media_series.sdb";
	}
	
	
} // fin de la classe

?>
