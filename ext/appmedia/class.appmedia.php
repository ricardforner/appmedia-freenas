<?php

include_once('class.crud.php');

class AppMedia extends crud {

	private $manageBBDD;
	protected $dirSources;
	
	public function __construct() {
		$this->dsn = "sqlite:/mnt/cfinterno/usr/www/bbdd/media_series.sdb";
		$this->dirSources = array(
			"/mnt/share01/SERIES",
			"/mnt/share02/SERIES",
			"/mnt/share03/MINISERIES"
		);
		$this->manageBBDD = false;
	}
	
	public function manageDatabase() {
		return ($this->manageBBDD);
	}
	
	public function listaSeries($paramOrder) {
		$order = isset($paramOrder)?$paramOrder:"rutaFisica";
		return $this->rawSelect("SELECT * FROM tbSerie ORDER BY $order");
	}

	public function getOptionsOrder($orderBy) {
		$ret = '<option value="nombreSerie" '. (!strcmp("nombreSerie",$orderBy)?'selected="selected"':'') .'>Nombre de la Serie</option>';
		$ret.= '<option value="rutaFisica" '. (!strcmp("rutaFisica",$orderBy)?'selected="selected"':'') .'>Ruta - Carpeta compartida</option>';
		return $ret;
	}

	public function doCreateDatabase() {
		$sql = "CREATE TABLE tbSerie (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				nombreSerie VARCHAR(100),
				numTemporadas INTEGER,
				finalizada CHAR(1),
				enDescarga CHAR(1),
				rutaFisica VARCHAR(255),
				notas VARCHAR(255)              
			)";
		$this->rawQuery($sql);
	}
	
	public function doDropDatabase() {
		$sql = "DROP TABLE tbSerie;";
		$this->rawQuery($sql);
	}

	public function doScanMedia() {
		$ignore = array( '.', '..' );
		
		foreach ($this->dirSources as $source) {
			echo $source."\n";
			$dirSource = $this->sdir($source, "");
			foreach ($dirSource as $item) {
				// Carpetas
				echo "\t".$item["element"]."\n";
				// Inicio detalle de las carpetas
				if( !in_array( $item, $ignore ) ){
					$mainPath = $item["path"];
					$mainSource = $item["element"];
					// 01. Busco series
					$listaSeries = $this->sdir($mainPath."/".$mainSource, "*tbn");
					foreach( $listaSeries as $serie) {
						//02. Detalle de las temporadas
						$dirTemporadas = $this->sdir($serie["path"]."/".$serie["element"], "*tbn");
						$numTemporadas = 0;
						$enDescarga = 0;
						if (0<>count($dirTemporadas)) {
							foreach( $dirTemporadas as $temporada) {
								$numTemporadas++;
								if (strpos($temporada["element"], "_tmp") !== false) {
									$enDescarga = 1;
								}
							}
						}
						$this->saveSerie($serie["element"], $serie["path"], $numTemporadas, $enDescarga);
					}
				}
			}
		}
	}

	private function saveSerie($pNombreSerie, $pRutaFisica, $pNumTemporadas, $pEnDescarga) {
		// 01. Existe en la base de datos ?
		$res = $this->dbSelect('tbSerie', 'nombreSerie', $pNombreSerie);
		$resNum = count($res);
		if ($resNum == 0) {
		// 02. Inserta registro
			$dbItem = array(
				'nombreSerie'=>$pNombreSerie,
				'numTemporadas'=>$pNumTemporadas,
				'enDescarga'=>$pEnDescarga,
				'rutaFisica'=>$pRutaFisica
			);
			$this->dbInsert('tbSerie', array($dbItem));
		} elseif ($resNum == 1) {
		// 03. Actualiza registro
			$this->dbUpdate('tbSerie', 'numTemporadas', $pNumTemporadas, 'uuid', $res[0]["uuid"]);
			$this->dbUpdate('tbSerie', 'enDescarga', $pEnDescarga, 'uuid', $res[0]["uuid"]);
		} else {
			echo "Duplicado: " . $pNombreSerie;
		}
	}
	
	private function sdir( $path='.', $mask='*', $nocache=0 ){
		static $dir = array(); // cache result in memory
		if ( !isset($dir[$path]) || $nocache) {
			$dir[$path] = is_dir($path) ? scandir($path) : $path;
		}
		if (is_dir($path)) {
			foreach ($dir[$path] as $i=>$entry) {
				if ($entry!='.' && $entry!='..' && !fnmatch($mask, $entry) ) {
					$sdir[] = array("path"=>$path, "element"=>$entry);
				}
			}
		} else {
			$sdir[] = -1;
		}
		return ($sdir);
	} 

} // fin de la classe

?>
