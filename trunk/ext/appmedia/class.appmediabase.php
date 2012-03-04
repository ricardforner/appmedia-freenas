<?php
/**
 * Clase AppMediaBase
 *
 * Esta clase facilita la capa de negocio y abstraccion a la base de datos
 *
 * @author Ricard Forner
 * @version 0.1.3
 * @package appmedia
 */

require_once('class.crud.php');

class AppMediaBase extends crud {

	const ACTION_MODIFY_UPDATE	= 'upd';
	const ACTION_MODIFY_DELETE	= 'del';
	const ACTION_MODIFY_ADD		= 'add';
	const TYPE_SERIE			= 'serie';
	const TYPE_MOVIE			= 'movie';
	
	public function manageDatabase() {
		return ($this->getConfigManage());
	}
	
	public function listaSeries($paramOrder) {
		$order = isset($paramOrder)?$paramOrder:"nombreSerie";
		return $this->rawSelect("SELECT * FROM tbSerie ORDER BY $order");
	}

	public function listaPeliculas($paramOrder) {
		$order = isset($paramOrder)?$paramOrder:"nombrePelicula";
		return $this->rawSelect("SELECT * FROM tbPelicula ORDER BY $order");
	}

	public function getOptionsOrder($orderBy, $type=self::TYPE_SERIE) {
		switch ($type) {
			// Opciones de ordenacion Series
			case self::TYPE_SERIE:
				$ret = '<option value="nombreSerie" '. (!strcmp("nombreSerie",$orderBy)?'selected="selected"':'') .'>Nombre de la Serie</option>';
				$ret.= '<option value="rutaFisica" '. (!strcmp("rutaFisica",$orderBy)?'selected="selected"':'') .'>Ruta - Carpeta compartida</option>';
				$ret.= '<option value="enDescarga" '. (!strcmp("enDescarga",$orderBy)?'selected="selected"':'') .'>&Uacute;ltima temporada completada</option>';
			break;
			// Opciones de ordenacion Peliculas
			case self::TYPE_MOVIE:
				$ret = '<option value="nombrePelicula" '. (!strcmp("nombrePelicula",$orderBy)?'selected="selected"':'') .'>Nombre de la Pel&iacute;cula</option>';
				$ret.= '<option value="rutaFisica" '. (!strcmp("rutaFisica",$orderBy)?'selected="selected"':'') .'>Ruta - Carpeta compartida</option>';
			break;
		}
		return $ret;
	}

	public function doCreateDatabase() {
		// Tabla de configuracion
		$sql = "CREATE TABLE IF NOT EXISTS tbConfig (
				uuid INTEGER PRIMARY KEY,
				manage BOOLEAN,
				folderPelicula TEXT,
				folderSerie TEXT
			)";
		$this->rawQuery($sql);
		// Tabla de series
		$sql = "CREATE TABLE IF NOT EXISTS tbSerie (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				nombreSerie VARCHAR(100),
				numTemporadas INTEGER,
				enDescarga CHAR(1),
				rutaFisica VARCHAR(255),
				lastEpisode VARCHAR2(14),
				notas VARCHAR(255)              
			)";
		$this->rawQuery($sql);
		// Tabla complementaria de series
		$sql = "CREATE TABLE IF NOT EXISTS tbSerieComp (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				idSerie INTEGER,
				fuente VARCHAR(20),
				url VARCHAR(255)
			)";
		$this->rawQuery($sql);
		// Tabla de peliculas
		$sql = "CREATE TABLE IF NOT EXISTS tbPelicula (
				uuid INTEGER PRIMARY KEY AUTOINCREMENT,
				nombrePelicula VARCHAR(100),
				anyo VARCHAR(4),
				rutaFisica VARCHAR(255),
				notas VARCHAR(255)              
			)";
		$this->rawQuery($sql);
	}
	
	public function doDropDatabase() {
		$sql = "DROP TABLE IF EXISTS tbSerieComp;";
		$this->rawQuery($sql);
		$sql = "DROP TABLE IF EXISTS tbSerie;";
		$this->rawQuery($sql);
		$sql = "DROP TABLE IF EXISTS tbPelicula;";
		$this->rawQuery($sql);
		$sql = "DROP TABLE IF EXISTS tbConfig;";
		$this->rawQuery($sql);
	}

	public function doScanMedia() {
		$this->doScanMediaSourceSeries();
		$this->doScanMediaSourcePeliculas();
	}
	
	private function doScanMediaSourceSeries() {
		$ignore = array( '.', '..' );
		
		$sources = $this->getConfigSources(self::TYPE_SERIE);
		foreach ($sources as $source) {
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
					$listaSeries = $this->sdir($mainPath.$mainSource, "*tbn");
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

	private function doScanMediaSourcePeliculas() {
		$ignore = array( '.', '..' );

		$sources = $this->getConfigSources(self::TYPE_MOVIE);
		foreach ($sources as $source) {
			echo $source."\n";
			$dirSource = $this->sdir($source, "");
			foreach ($dirSource as $item) {
				// Carpetas
				echo "\t".$item["element"]."\n";
				// Inicio detalle
				if( !in_array( $item, $ignore ) ){
					$mainPath = $item["path"];
					$mainSource = $item["element"];
					// 01. Busco por si es subcarpeta
					$listaPeliculas = $this->sdir($mainPath.$mainSource, "*tbn");
					foreach( $listaPeliculas as $pelicula) {
						if (-1==$pelicula) {
							$peliculaTitulo = $item["element"];
							$peliculaRuta = $item["path"];
						} else {
							$peliculaTitulo = $pelicula["element"];
							$peliculaRuta = $pelicula["path"] ;
						}
						$this->savePelicula($peliculaTitulo, $peliculaRuta);
					}
				}
			}
		}
	}

	private function saveSerie($pNombreSerie, $pRutaFisica, $pNumTemporadas, $pEnDescarga, $pLastEpisode=null) {
		// 01. Existe en la base de datos ?
		$res = $this->doGetSerie('nombreSerie', $pNombreSerie);
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

	private function savePelicula($pNombrePelicula, $pRutaFisica) {
		// 01. Existe en la base de datos ?
		$res = $this->doGetPelicula('nombrePelicula', $pNombrePelicula);
		$resNum = count($res);
		if ($resNum == 0) {
		// 02. Inserta registro
			$dbItem = array(
				'nombrePelicula'=>$pNombrePelicula,
				'rutaFisica'=>$pRutaFisica
			);
			$this->dbInsert('tbPelicula', array($dbItem));
		} elseif ($resNum == 1) {
		// 03. Actualiza registro
			$dbItem = array(
				'rutaFisica'=>$pRutaFisica
			);
			$this->dbUpdate('tbPelicula', null, $dbItem, 'uuid', $res[0]["uuid"]);
			//Control duplicado en varias carpetas
			echo (!strcmp($pRutaFisica, $res[0]["rutaFisica"])) ? "":"\t\tVerificar posibles duplicados: $pNombrePelicula ($pRutaFisica, ".$res[0]["rutaFisica"]."))\n";
		} else {
			echo "Registro duplicado: ".$pNombrePelicula."\n";
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
	
	public function doGetSerie($fieldname, $id) {
		return $this->dbSelect('tbSerie', $fieldname, $id);
	}

	public function doGetPelicula($fieldname, $id) {
		return $this->dbSelect('tbPelicula', $fieldname, $id);
	}

	public function doActionSerie($param, $mode) {
		switch ($mode) {
			
			// Accion de borrar registro
			case self::ACTION_MODIFY_DELETE:
				$this->dbDelete('tbSerie', 'uuid', $param["uuid"]);
			break;
			
			// Accion de insertar registro
			case self::ACTION_MODIFY_ADD:
				$dbItem = array(
					'nombreSerie'=>$param["nombreSerie"],
					'numTemporadas'=>$param["numTemporadas"],
					'enDescarga'=>$param["enDescarga"],
					'rutaFisica'=>$param["rutaFisica"],
					'lastEpisode'=>$param["lastEpisode"],
					'notas'=>$param["notas"]
				);
				$this->dbInsert('tbSerie', array($dbItem));
			break;

			// Accion de modificar registro
			case self::ACTION_MODIFY_UPDATE:
				$dbItem = array(
					'nombreSerie'=>$param["nombreSerie"],
					'numTemporadas'=>$param["numTemporadas"],
					'enDescarga'=>$param["enDescarga"],
					'rutaFisica'=>$param["rutaFisica"],
					'lastEpisode'=>$param["lastEpisode"],
					'notas'=>$param["notas"]
				);
				if ($param["uuid"]=="-1") {
					$this->dbInsert('tbSerie', array($dbItem));
				} else {
					$this->dbUpdate('tbSerie', null, $dbItem, 'uuid', $param["uuid"]);
				}
			break;

		}
	}

	public function doActionPelicula($param, $mode) {
		switch ($mode) {
			
			// Accion de borrar registro
			case self::ACTION_MODIFY_DELETE:
				$this->dbDelete('tbPelicula', 'uuid', $param["uuid"]);
			break;

			// Accion de insertar registro
			case self::ACTION_MODIFY_ADD:
				$dbItem = array(
					'nombrePelicula'=>$param["nombrePelicula"],
					'anyo'=>$param["anyo"],
					'rutaFisica'=>$param["rutaFisica"],
					'notas'=>$param["notas"]
				);
				$this->dbInsert('tbPelicula', array($dbItem));
			break;

			// Accion de modificar registro
			case self::ACTION_MODIFY_UPDATE:
				$dbItem = array(
					'nombrePelicula'=>$param["nombrePelicula"],
					'anyo'=>$param["anyo"],
					'rutaFisica'=>$param["rutaFisica"],
					'notas'=>$param["notas"]
				);
				if ($param["uuid"]=="-1") {
					$this->dbInsert('tbPelicula', array($dbItem));
				} else {
					$this->dbUpdate('tbPelicula', null, $dbItem, 'uuid', $param["uuid"]);
				}
			break;

		}
	}

	public function doGetConfig($fieldname, $id) {
		return $this->dbSelect('tbConfig', $fieldname, $id);
	}

	public function saveConfig($param) {
		// 00. Pre-proceso parametros
		if (is_array($param['folderSerie'])) {
			$folderSerieCSV = implode(';', $param['folderSerie']);
		}
		if (is_array($param['folderPelicula'])) {
			$folderPeliculaCSV = implode(';', $param['folderPelicula']);
		}
		// 01. Existe en la base de datos ?
		$res = $this->dbSelect('tbConfig', 'uuid', $param['uuid']);
		if (count($res) == 0) {
		// 02. Inserta registro
			$dbItem = array(
				'uuid'=>$param['uuid'],
				'manage'=>$param['manage'],
				'folderSerie'=>$folderSerieCSV,
				'folderPelicula'=>$folderPeliculaCSV
			);
			$this->dbInsert('tbConfig', array($dbItem));
		} else {
		// 03. Actualiza registro
			$dbItem = array(
				'manage'=>$param['manage'],
				'folderSerie'=>$folderSerieCSV,
				'folderPelicula'=>$folderPeliculaCSV
			);
			$this->dbUpdate('tbConfig', null, $dbItem, 'uuid', $param['uuid']);
		}
	}

	private function getConfigSources($type) {
		$res = $this->doGetConfig('uuid', 1);
		if (count($res) == 0) {
			$sources = array();
			return $sources;
		}
		switch ($type) {
			case self::TYPE_SERIE:
				$sources = explode(';', $res[0]['folderSerie']);
			break;
			case self::TYPE_MOVIE:
				$sources = explode(';', $res[0]['folderPelicula']);
			break;
		}
		return $sources;
	}
	
	private function getConfigManage() {
		try {
			$res = $this->doGetConfig('uuid', 1);
		} catch (PDOException $e) {
			// Caso en que no existe la tabla
			return true;
		}
		if (count($res) == 0) {
			$enableManage = true;
		} else {
			$enableManage = $res[0]['manage'];
		}
		return $enableManage;
	}
	
} // fin de la classe

?>
