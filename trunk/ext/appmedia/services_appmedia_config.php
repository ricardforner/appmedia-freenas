#!/usr/local/bin/php
<?php
/**
 * Services_AppMedia_Config
 *
 * Pagina que contiene la ficha de configuracion del modulo AppMedia
 *
 * @author Ricard Forner
 * @version 0.1.2
 * @package appmedia
 */

require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "Contenido Multimedia" ."|". "Configuracion");

include 'class.appmedia.php';
$app = new AppMedia();
$pItem = array();
$uuid = 1;

try {
	if (isset($uuid) && (FALSE !== ($cnid = $app->doGetConfig("uuid", $uuid)))) {
		$pItem['uuid'] = $uuid;
		$pItem['manageBBDD'] = (1==$cnid[0]['manage']);
		$pItem['scanFolderPelicula'] = explode(';', $cnid[0]['folderPelicula']);
		$pItem['scanFolderSerie'] = explode(';', $cnid[0]['folderSerie']);
	} else {
		$pItem['uuid'] = $uuid;
		$pItem['manageBBDD'] = false;
		$pItem['scanFolderPelicula'] = null;
		$pItem['scanFolderSerie'] = null;
	}
} catch (PDOException $e) {
	$errormsg[] = "La tabla de configuraci&oacute;n no existe en la base de datos. Puede crearla mediante la opci&oacute;n 'Crear base de datos' en el men&uacute; de Herramientas.";
}


if ($_POST) {
	unset($input_errors);
	$pItem = $_POST;
	if (!$input_errors) {
		$item = array();
		$item['uuid'] = $_POST['uuid'];
		$item['manage'] = isset($_POST['manageBBDD'])?1:0;
		$item['folderPelicula'] = $_POST['scanFolderPelicula'];
		$item['folderSerie'] = $_POST['scanFolderSerie'];

		$app->saveConfig($item);

		header("Location: services_appmedia_config.php");
        exit;
	}
}

?>
<?php include("fbegin.inc");?>
<?php if($errormsg) print_input_errors($errormsg);?>
<script type="text/javascript">
<!--
	function enable_change(enable_change) {
		var endis = !(enable_change);
			document.iform.manageBBDD.disabled = endis;
			document.iform.scanFolderPelicula.disabled = endis;
			document.iform.scanFolderPeliculaaddbtn.disabled = endis;
			document.iform.scanFolderPeliculachangebtn.disabled = endis;
			document.iform.scanFolderPeliculadeletebtn.disabled = endis;
			document.iform.scanFolderPeliculadata.disabled = endis;
			document.iform.scanFolderPeliculabrowsebtn.disabled = endis;
			document.iform.scanFolderSerie.disabled = endis;
			document.iform.scanFolderSerieaddbtn.disabled = endis;
			document.iform.scanFolderSeriechangebtn.disabled = endis;
			document.iform.scanFolderSeriedeletebtn.disabled = endis;
			document.iform.scanFolderSeriedata.disabled = endis;
			document.iform.scanFolderSeriebrowsebtn.disabled = endis;
			document.iform.Submit.disabled = endis;
	}
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_appmedia.php"><span>Series</span></a></li>
				<li class="tabinact"><a href="services_appmedia_movie.php"><span>Pel&iacute;culas</span></a></li>
				<li class="tabinact"><a href="services_appmedia_tools.php"><span>Herramientas</span></a></li>
				<li class="tabact"><a href="services_appmedia_config.php" title="<?=gettext("Reload page");?>"><span>Configuraci&oacute;n</span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
		<?php if ($input_errors) print_input_errors($input_errors);?>
		<form action="services_appmedia_config.php" method="post" name="iform" id="iform">
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline("Par&aacute;metros de configuraci&oacute;n");?>
			<?php html_checkbox("manageBBDD", "Gesti&oacute;n de la base de datos", $pItem['manageBBDD'] ? true : false, "Marcar la casilla si desea activar los comandos de gesti&oacute;n de la base de datos.<br/><br/> Si deja la casilla sin marcar, las acciones <i>Crear base de datos</i> y <i>Borrar base de datos</i> del men&uacute; <b>herramientas</b> estar&aacute;n inactivas.", "", false);?>
			<?php html_folderbox("scanFolderSerie", "Directorios de contenido (Series)", $pItem['scanFolderSerie'], "Ubicaci&oacute;n de los directorios de series a escanear.", $g['media_path'], false);?>
			<?php html_folderbox("scanFolderPelicula", "Directorios de contenido (Pel&iacute;culas)", $pItem['scanFolderPelicula'], "Ubicaci&oacute;n de los directorios de pel&iacute;culas a escanear.", $g['media_path'], false);?>
			</table>
			<div id="submit">
				<input name="Submit" id="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onclick="onsubmit_scanFolderPelicula(); onsubmit_scanFolderSerie(); enable_change(true)" />
				<input name="uuid" type="hidden" value="<?=$pItem['uuid'];?>" />
			</div>
			<div id="remarks">
				<?php html_remark("note", gettext("Note"), "Los par&aacute;metros de configuraci&oacute;n son almacenados en la base de datos.");?>
			</div>
		<?php include("formend.inc");?>
		</form>		
		</td>
	</tr>
</table>
<script type="text/javascript">
<!--
	enable_change(<?=($errormsg)?'false':'true'?>);
//-->
</script>
<?php include("fend.inc");?>
