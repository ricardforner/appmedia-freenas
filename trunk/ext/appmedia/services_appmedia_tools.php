#!/usr/local/bin/php
<?php
/**
 * Services_AppMedia_Tools
 *
 * Pagina que contiene la ficha de herramientas del modulo AppMedia
 *
 * @author Ricard Forner
 * @version 0.1.3
 * @package appmedia
 */

require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "Contenido Multimedia" ."|". "Herramientas");

include 'class.appmedia.php';
$app = new AppMedia();

if (isset($_GET['action'])) {
	$action = $_GET['action'];
}

if ($_POST) {
	unset($input_errors);
	unset($errormsg);
	unset($do_action);

	if (!$app->manageDatabase() && "create" === $_POST['action']) {
		$errormsg[] = "Funci&oacute;n de 'Crear base de datos' desactivada.";
	}
	else if (!$app->manageDatabase() && "drop" === $_POST['action']) {
		$errormsg[] = "Funci&oacute;n de 'Borrar base de datos' desactivada.";
	}
	else if ("info" === $_POST['action']) {
		$errormsg[] = "Funci&oacute;n de 'Buscar informaci&oacute;n en la web' no implementada.";
	}
	
	if ((!$input_errors) || (!$errormsg)) {
		$do_action = true;
		$action = $_POST['action'];
	}	
}

if (!isset($do_action)) {
	$do_action = false;
}
?>
<?php include("fbegin.inc");?>
<?php if($errormsg) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_appmedia.php"><span>Series</span></a></li>
				<li class="tabinact"><a href="services_appmedia_movie.php"><span>Pel&iacute;culas</span></a></li>
				<li class="tabact"><a href="services_appmedia_tools.php" title="<?=gettext("Reload page");?>"><span>Herramientas</span></a></li>
				<li class="tabinact"><a href="services_appmedia_config.php"><span>Configuraci&oacute;n</span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
		<?php if ($input_errors) print_input_errors($input_errors);?>
		<form action="services_appmedia_tools.php" method="post" name="iform" id="iform">
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php html_titleline("Acciones disponibles");?>
			<?php html_combobox("action", gettext("Command"), $action,
				array(
					"create" => "Crear base de datos",
					"drop" => "Borrar base de datos",
					"info" => "Buscar informaci&oacute;n en la web",
					"scan" => "Escanear directorios"
				), "", true);?>			
			</table>
			<div id="submit">
				<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Execute");?>" />
			</div>

			<?php if(($do_action) && (!$errormsg)) {
				echo(sprintf("<div id='cmdoutput'>%s</div>", gettext("Command output:")));
				echo('<pre class="cmdoutput">');
				switch ($action) {
					case "scan":
						echo("Escaneando directorios...". "<br />");
						$app->doScanMedia();
						mwexec("logger -t appmedia-extension Escaneado de directorios de contenido multimedia");
					break;
					case "create":
						echo("Creando base de datos...". "<br />");
						$app->doCreateDatabase();
						mwexec("logger -t appmedia-extension Base de datos creada");
					break;
					case "drop":
						echo("Borrando base de datos...". "<br />");
						$app->doDropDatabase();
						mwexec("logger -t appmedia-extension Base de datos borrada");
					break;
				}
				echo (0 == $result) ? gettext("Done.") : gettext("Failed.");
				echo('</pre>');
			}?>
			<div id="remarks">
				<?php html_remark("note", gettext("Note"), "Detalles sobre las acciones disponibles:
				<div id='enumeration'><ul>
					<li><b>Crear base de datos</b> crea el fichero de base de datos. S&oacute;lo debe ejecutarse si no est&aacute; creada.</li>
					<li><b>Borrar base de datos</b> borra el fichero de base de datos si existe.</li>
					<li><b>Buscar informaci&oacute;n en la web</b> <i>no est&aacute; implementada en la versi&oacute;n 0.1.x</i>.</li>
					<li><b>Escanear directorios</b> actualiza los registros si estos son encontrados en la base de datos, en caso contrario, crea una nueva entrada.</li>
				</ul></div>");?>
			</div>
		<?php include("formend.inc");?>
		</form>		
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
