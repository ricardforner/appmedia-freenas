#!/usr/local/bin/php
<?php
/**
 * Services_AppMedia_Movie
 *
 * Pagina que contiene el acceso al modulo de peliculas de AppMedia
 *
 * @author Ricard Forner
 * @version 0.1.2
 * @package appmedia
 */

require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "Contenido Multimedia" ."|". "Peliculas");

include 'class.appmedia.php';

if ($_POST) {
	if (isset($_POST['doOrder'])) {
		$orderBy = $_POST['order'];
	}
}

$app = new AppMedia();

unset($errormsg);
try {
	$records = $app->listaPeliculas($orderBy);
} catch (PDOException $e) {
	$errormsg[] = "La 'Base de datos' no est&aacute; creada. Puede crearla en el men&uacute; de Herramientas.";
}

if ($_GET['act'] === "del") {
	$item = array();
	$item['uuid'] = $_GET['uuid'];

	$mode = AppMedia::ACTION_MODIFY_DELETE;
	$app->doActionPelicula($item, $mode);
	header("Location: services_appmedia_movie.php");
	exit;
}
?>
<?php include("fbegin.inc");?>
<?php if($errormsg) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="services_appmedia.php"><span>Series</span></a></li>
				<li class="tabact"><a href="services_appmedia_movie.php" title="<?=gettext("Reload page");?>"><span>Pel&iacute;culas</span></a></li>
				<li class="tabinact"><a href="services_appmedia_tools.php"><span>Herramientas</span></a></li>
				<li class="tabinact"><a href="services_appmedia_config.php"><span>Configuraci&oacute;n</span></a></li>
			</ul>
		</td>
	</tr>
	

	<tr>
		<td class="tabcont">
		<form action="services_appmedia_movie.php" method="post">

		<select id="order" class="formfld" name="order">
			<?=$app->getOptionsOrder($orderBy, AppMedia::TYPE_MOVIE)?>
		</select>
		<input name="doOrder" type="submit" class="formbtn" value="Ordenar" />

		<br/><br/>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td width="35%" class="listhdrlr">Nombre de la Pel&iacute;cula</td>
                                                <td width="5%" class="listhdrr">A&ntilde;o</td>
                                                <td width="40%" class="listhdrr">Ruta (Carpeta compartida)</td>
                                                <td width="10%" class="listhdrr">Notas</td>
                                                <td width="10%" class="list"></td>
                                        </tr>

<?php										
$rows = (isset($records))?$records->fetchAll(PDO::FETCH_ASSOC):array();
foreach($rows as $row) {
?>
										<tr>
											<td class="listlr"><?=htmlspecialchars($row["nombrePelicula"])?>&nbsp;</td>
											<td class="listr"><?=$row["anyo"]?>&nbsp;</td>
											<td class="listr"><?=htmlspecialchars($row["rutaFisica"])?>&nbsp;</td>
											<td class="listr"><?=$row["notas"]?>&nbsp;</td>
											<td valign="middle" nowrap="nowrap" class="list">
												<a href="services_appmedia_movie_edit.php?uuid=<?=$row['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit");?>" border="0" alt="<?=gettext("Edit");?>" /></a>&nbsp;
												<a href="services_appmedia_movie.php?act=del&amp;uuid=<?=$row['uuid'];?>" onclick="return confirm('&iquest;Est&aacute;s seguro de borrar el registro?')"><img src="x.gif" title="<?=gettext("Delete");?>" border="0" alt="<?=gettext("Delete");?>" /></a>
											</td>
											</tr>
<?php
}
?>										
                                        <tr>
												<td class="list" colspan="4"></td>
												<td class="list">
													<a href="services_appmedia_movie_edit.php"><img src="plus.gif" title="<?=gettext("Add");?>" border="0" alt="<?=gettext("Add");?>" /></a>
												</td>
										</tr>		
		<?php include("formend.inc");?>
		</form>		
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
