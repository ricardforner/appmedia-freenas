#!/usr/local/bin/php
<?php
/**
 * Services_AppMedia_Edit
 *
 * Pagina que contiene la ficha de edicion del modulo AppMedia
 *
 * @author Ricard Forner
 * @version 0.1.0
 * @package appmedia
 */

require("auth.inc");
require("guiconfig.inc");

$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "Contenido Multimedia", isset($uuid) ? gettext("Edit") : gettext("Add"));

include 'class.appmedia.php';

$app = new AppMedia();
$pItem = array();

if (isset($uuid) && (FALSE !== ($cnid = $app->doGetSerie("uuid", $uuid)))) {
	$pItem['uuid'] = $cnid[0]['uuid'];
	$pItem['nombreSerie'] = $cnid[0]['nombreSerie'];
	$pItem['numTemporadas'] = $cnid[0]['numTemporadas'];
	$pItem['enDescarga'] = ($cnid[0]['enDescarga']==1);
	$pItem['rutaFisica'] = $cnid[0]['rutaFisica'];
	$pItem['lastEpisode'] = $cnid[0]['lastEpisode'];
	$pItem['notas'] = $cnid[0]['notas'];
} else {
	$pItem['uuid'] = -1;
	$pItem['nombreSerie'] = "";
	$pItem['numTemporadas'] = "";
	$pItem['enDescarga'] = false;
	$pItem['rutaFisica'] = "";
	$pItem['lastEpisode'] = "";
	$pItem['notas'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pItem = $_POST;

	if ($_POST['Cancel']) {
		header("Location: services_appmedia.php");
		exit;
	}
	
	$reqdfields = explode(" ", "nombreSerie numTemporadas rutaFisica");
	$reqdfieldsn = array("Nombre de la Serie", "Temporadas", "Ruta (Carpeta compartida)");	
	$reqdfieldst = explode(" ", "string numeric string");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
	do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, &$input_errors);
			
	if (($_POST['nombreSerie'] && !is_string($_POST['nombreSerie']))) {
		$input_errors[] = gettext("El campo 'Nombre de la Serie' contiene caracteres inv&aacute;lidos.");
	}
	
	if (!$input_errors) {
		$item = array();
		$item['uuid'] = $_POST['uuid'];
		$item['nombreSerie'] = $_POST['nombreSerie'];
		$item['numTemporadas'] = $_POST['numTemporadas'];
		$item['enDescarga'] = $_POST['enDescarga'] ? '1' : '0';
		$item['rutaFisica'] = $_POST['rutaFisica'];
		$item['lastEpisode'] = $_POST['lastEpisode'];
		$item['notas'] = $_POST['notas'];

		if (isset($uuid) && (FALSE !== $cnid) && ($uuid!=-1)) {
			$mode = AppMedia::ACTION_MODIFY_UPDATE;
		} else {
			$mode = AppMedia::ACTION_MODIFY_ADD;
		}
		$app->doActionSerie($item, $mode);
		
		header("Location: services_appmedia.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_appmedia_edit.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				
<?php html_inputbox("nombreSerie", "Nombre de la Serie", $pItem['nombreSerie'], "", true, 40);?>
<?php html_inputbox("numTemporadas", "Temporadas", $pItem['numTemporadas'], "N&uacute;mero de temporadas descargadas (almacenadas).", false, 2);?>
<?php html_checkbox("enDescarga", "En proceso de descarga", $pItem['enDescarga'] ? true : false, "Marcar la casilla si la temporada est&aacute; en proceso de descarga. <br/><br/>El proceso de escaneado marca la casilla autom&aacute;ticamente si el nombre de la temporada contiene el sufijo _tmp (Ej: T3_tmp)", "", false);?>
<?php html_inputbox("lastEpisode", "&Uacute;ltimo episodio (en descarga)", $pItem['lastEpisode'], "Informar del &uacute;ltimo episodio s&oacute;lo si la temporada no est&aacute; completada.", false, 2);?>				
<?php html_filechooser("rutaFisica", "Ruta (Carpeta compartida)", $pItem['rutaFisica'], "Entra el directorio raiz de la serie.", $g['media_path'], false, 60);?>				
<?php html_textarea("notas", "Notas", $pItem['notas'], "Informaci&oacute;n adicional.", false);?>				
				
				</table>
						<div id="submit">
							<input name="Submit" type="submit" class="formbtn" value="<?=(isset($uuid) && (FALSE !== $cnid)) ? gettext("Save") : gettext("Add")?>" />
							<input name="Cancel" type="submit" class="formbtn" value="<?=gettext("Cancel");?>" />
							<input name="uuid" type="hidden" value="<?=$pItem['uuid'];?>" />
						</div>
						<?php include("formend.inc");?>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
