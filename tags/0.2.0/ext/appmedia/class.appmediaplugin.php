<?php
/**
 * Clase AppMediaPlugin
 *
 * Esta clase facilita la capa de plugins para la aplicacion AppMedia
 *
 * @author Ricard Forner
 * @version 0.2.0
 * @package appmedia
 */

class AppMediaPlugin {

	const PLUGIN_MEDIAUTILS_DOWNLOAD = 'addon-mediautils-endescarga';
	
 	private $app;
	
	function __construct($pApp) {
		$this->app = $pApp;
	}

	public function doAction($action) {
		switch ($action) {
			case self::PLUGIN_MEDIAUTILS_DOWNLOAD:
				$this->doActionMediaUtils($action);
			break;
		}
	}

	public function getHtml($option, $param=null) {
		switch ($option) {
			case 'combo':
				return $this->getComboOptions();
			break;
			case 'info':
				return $this->getInfoNotes();
			break;
			default:
				return "";
			break;
		}
	}
	
	private function getComboOptions() {
		return array (
			"-" => "------------------------"
			, self::PLUGIN_MEDIAUTILS_DOWNLOAD => "MediaUtils: Contenido actualmente en descarga"
		);
	}	
	
	private function getInfoNotes() {
		return "<br/>Acciones sobre los plugins:"
				."<div id='enumeration'><ul>"
				."	<li><b>MediaUtils: Contenido actualmente en descarga</b> detecta las series actualmente en Descarga.</li>"
				."</ul></div>";
	}

	private function doActionMediaUtils($action) {
		echo "\nMediaUtils: ";
		switch ($action) {
			case self::PLUGIN_MEDIAUTILS_DOWNLOAD:
				echo "Series en descarga\n";
				echo "---------------------------------------------\n";
				$this->doMediaUtilsDownload();
			break;
		}
	}
	
	private function doMediaUtilsDownload() {
		$records = $this->app->listaSeriesBy(array("enDescarga"), array("1"), null);
		$rows = (isset($records))?$records->fetchAll(PDO::FETCH_ASSOC):array();
		foreach($rows as $row) {
			$searchname = str_replace(" ", "+", $row["nombreSerie"]);
			echo $row["nombreSerie"] .": T";
			echo $row["numTemporadas"] ." Ep.";
			echo $row["lastEpisode"] ."";
			echo "\n";
		}
		echo "\n";
	}
	
} // fin de la classe

?>