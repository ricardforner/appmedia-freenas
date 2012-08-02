#!/usr/local/bin/php
<?php
/**
 * Services_AppMedia_Movie_Edit
 *
 * Pagina que contiene la ficha de edicion (de peliculas) del modulo AppMedia
 *
 * @author Ricard Forner
 * @version 0.1.2
 * @package appmedia
 */

require_once("auth.inc");
require_once("guiconfig.inc");
require_once("ext/appmedia/class.appmedia.php");

$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "Contenido Multimedia" ."|". "Peliculas", isset($uuid) ? gettext("Edit") : gettext("Add"));

$app = new AppMedia();
$pItem = array();

if (isset($uuid) && (FALSE !== ($cnid = $app->doGetPelicula("uuid", $uuid)))) {
	$pItem['uuid'] = $cnid[0]['uuid'];
	$pItem['nombrePelicula'] = $cnid[0]['nombrePelicula'];
	$pItem['anyo'] = $cnid[0]['anyo'];
	$pItem['rutaFisica'] = $cnid[0]['rutaFisica'];
	$pItem['notas'] = $cnid[0]['notas'];
} else {
	$pItem['uuid'] = -1;
	$pItem['nombrePelicula'] = "";
	$pItem['anyo'] = "";
	$pItem['rutaFisica'] = "";
	$pItem['notas'] = "";
}

if ($_POST) {
	unset($input_errors);
	$pItem = $_POST;

	if ($_POST['Cancel']) {
		header("Location: services_appmedia_movie.php");
		exit;
	}
	
	$reqdfields = explode(" ", "nombrePelicula rutaFisica");
	$reqdfieldsn = array("Nombre de la Pel&iacute;cula", "Ruta (Carpeta compartida)");	
	$reqdfieldst = explode(" ", "string string");

	$valdfields = explode(" ", "nombrePelicula anyo rutaFisica");
	$valdfieldsn = array("Nombre de la Pel&iacute;cula", "A&ntilde;o", "Ruta (Carpeta compartida)");	
	$valdfieldst = explode(" ", "string numeric string");
	
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
	do_input_validation_type($_POST, $valdfields, $valdfieldsn, $valdfieldst, &$input_errors);
			
	if (($_POST['nombrePelicula'] && !is_string($_POST['nombrePelicula']))) {
		$input_errors[] = gettext("El campo 'Nombre de la Pel&iacute;cula' contiene caracteres inv&aacute;lidos.");
	}
	
	if (!$input_errors) {
		$item = array();
		$item['uuid'] = $_POST['uuid'];
		$item['nombrePelicula'] = $_POST['nombrePelicula'];
		$item['anyo'] = $_POST['anyo'];
		$item['rutaFisica'] = $_POST['rutaFisica'];
		$item['notas'] = $_POST['notas'];

		if (isset($uuid) && (FALSE !== $cnid) && ($uuid!=-1)) {
			$mode = AppMedia::ACTION_MODIFY_UPDATE;
		} else {
			$mode = AppMedia::ACTION_MODIFY_ADD;
		}
		$app->doActionPelicula($item, $mode);
		
		header("Location: services_appmedia_movie.php");
		exit;
	}
}
?>
<?php include("fbegin.inc"); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabcont">
			<form action="services_appmedia_movie_edit.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				
<?php html_inputbox("nombrePelicula", "Nombre de la Pel&iacute;cula", $pItem['nombrePelicula'], "", true, 40);?>
<?php html_inputbox("anyo", "A&ntilde;o", $pItem['anyo'], "", false, 4);?>
<?php html_filechooser("rutaFisica", "Ruta (Carpeta compartida)", $pItem['rutaFisica'], "Entra el directorio raiz de la pel&iacute;cula.", $g['media_path'], true, 60);?>				
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
