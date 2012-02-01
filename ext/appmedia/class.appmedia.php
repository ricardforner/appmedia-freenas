<?php

include_once('class.crud.php');

class AppMedia extends crud {

	private $manageBBDD;
	protected $dirSources;
	
	public function __construct() {
		$this->dsn = "sqlite:/mnt/cfinterno/usr/www/bbdd/media_series.sdb";
		$this->dirSources = array(
			"/mnt/share01/SERIES"
			,"/mnt/share02/SERIES"
			,"/mnt/share03/MINISERIES"
//			,"/mnt/nmt/Video"
		);
		$this->manageBBDD = true;
	}
	
	public function manageDatabase() {
		return ($this->manageBBDD);
	}
	
	public function listaSeries($paramOrder) {
		$order = isset($paramOrder)?$paramOrder:"nombreSerie";
		return $this->rawSelect("SELECT * FROM tbSerie ORDER BY $order");
	}

	public function getOptionsOrder($orderBy) {
		$ret = '<option value="nombreSerie" '. (!strcmp("nombreSerie",$orderBy)?'selected="selected"':'') .'>Nombre de la Serie</option>';
		$ret.= '<option value="rutaFisica" '. (!strcmp("rutaFisica",$orderBy)?'selected="selected"':'') .'>Ruta - Carpeta compartida</option>';
		return $ret;
	}

	public function doCreateDatabase() {
		// Tabla de series
		$sql = "CREATE TABLE tbSerie (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				nombreSerie VARCHAR(100),
				numTemporadas INTEGER,
				enDescarga CHAR(1),
				rutaFisica VARCHAR(255),
				lastEpisode VARCHAR2(14),
				notas VARCHAR(255)              
			)";
		$this->rawQuery($sql);
		//Tabla complementaria de series
		$sql = "CREATE TABLE tbSerieComp (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				idSerie INTEGER,
				fuente VARCHAR(20),
				url VARCHAR(255)
			)";
		$this->rawQuery($sql);
	}
	
	public function doDropDatabase() {
		$sql = "DROP TABLE tbSerieComp;";
		$this->rawQuery($sql);
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
						$lastEpisode = null;
						if (count(dirTemporadas)>0) {
							foreach($dirTemporadas as $temporada) {
								$numTemporadas++;
								if ($temporada["isFile"]==1) {
									break;
								}
								if (strpos($temporada["element"], "_tmp") !== false) {
									$enDescarga = 1;
									$numEpisodes = $this->sdir($temporada["path"]."/".$temporada["element"], "");
									$lastEpisode = count($numEpisodes);
								}
							}
						}
						$this->saveSerie($serie["element"], $serie["path"], $numTemporadas, $enDescarga, $lastEpisode);
					}
				}
			}
		}
	}

	private function saveSerie($pNombreSerie, $pRutaFisica, $pNumTemporadas, $pEnDescarga, $pLastEpisode=null) {
		// 01. Existe en la base de datos ?
		$res = $this->dbSelect('tbSerie', 'nombreSerie', $pNombreSerie);
		$resNum = count($res);
		if ($resNum == 0) {
		// 02. Inserta registro
			$dbItem = array(
				'nombreSerie'=>$pNombreSerie,
				'numTemporadas'=>$pNumTemporadas,
				'enDescarga'=>$pEnDescarga,
				'rutaFisica'=>$pRutaFisica,
				'lastEpisode'=>$pLastEpisode
			);
			$this->dbInsert('tbSerie', array($dbItem));
		} elseif ($resNum == 1) {
		// 03. Actualiza registro
			$dbItem = array(
				'numTemporadas'=>$pNumTemporadas,
				'enDescarga'=>$pEnDescarga,
				'lastEpisode'=>$pLastEpisode
			);
			$this->dbUpdate('tbSerie', null, $dbItem, 'uuid', $res[0]["uuid"]);
			//Control duplicado en varias carpetas
			echo (!strcmp($pRutaFisica, $res[0]["rutaFisica"])) ? "":"\t\tVerificar posibles duplicados: $pNombreSerie ($pRutaFisica, ".$res[0]["rutaFisica"]."))\n";
		} else {
			echo "Registro duplicado: ".$pNombreSerie."\n";
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
					$sdir[] = array("path"=>$path, "element"=>$entry, "isFile"=>(int)is_file($path."/".$entry) );
				}
			}
		} else {
			$sdir[] = -1;
		}
		return ($sdir);
	} 

} // fin de la classe

?>
