#!/usr/local/bin/php
<?php
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(gettext("Extensions"), gettext("Service") ."|". "Contenido Multimedia");

include 'class.appmedia.php';

if ($_POST) {
	if (isset($_POST['doOrder'])) {
		$orderBy = $_POST['order'];
	}
}

$app = new AppMedia();

unset($errormsg);
try {
	$records = $app->listaSeries($orderBy);
} catch (PDOException $e) {
	$errormsg[] = "La 'Base de datos' no est&aacute; creada. Puede crearla en el men&uacute; de Herramientas.";
}


?>
<?php include("fbegin.inc");?>
<?php if($errormsg) print_input_errors($errormsg);?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="services_appmedia.php" title="<?=gettext("Reload page");?>"><span>Series</span></a></li>
				<li class="tabinact"><a href="services_appmedia_tools.php"><span>Herramientas</span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
		<form action="services_appmedia.php" method="post">

		<select id="order" class="formfld" name="order">
			<?=$app->getOptionsOrder($orderBy)?>
		</select>

		<input name="doOrder" type="submit" class="formbtn" value="Ordenar" />
		<br /><br />

                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                                <td width="35%" class="listhdrlr">Nombre de la Serie</td>
                                                <td width="5%" class="listhdrr">Temporadas</td>
                                                <td width="5%" class="listhdrr">Finalizada</td>
												<td width="10%" class="listhdrr">En Descarga</td>
                                                <td width="35%" class="listhdrr">Ruta (Carpeta compartida)</td>
                                                <td width="10%" class="list"></td>
                                        </tr>

<?php										
$rows = (isset($records))?$records->fetchAll(PDO::FETCH_ASSOC):array();
foreach($rows as $row) {
?>
										<tr>
											<td class="listlr"><?=htmlspecialchars($row["nombreSerie"])?>&nbsp;</td>
											<td class="listr"><?=$row["numTemporadas"]?>&nbsp;</td>
											<td class="listbg"><?=$row["finalizada"]?>&nbsp;</td>
											<td class="listbg"><?=((1==$row["enDescarga"])?gettext("Yes"):gettext("No"))?>&nbsp;</td>
											<td class="listr"><?=htmlspecialchars($row["rutaFisica"])?>&nbsp;</td>
											<td valign="middle" nowrap="nowrap" class="list">
												<a href="services_appmedia_edit.php?uuid=<?=$row['uuid'];?>"><img src="e.gif" title="<?=gettext("Edit");?>" border="0" alt="<?=gettext("Edit");?>" /></a>&nbsp;
												<a href="services_appmedia_edit.php?act=del&amp;uuid=<?=$row['uuid'];?>" onclick="return confirm('Esta seguro de borrar el registro')"><img src="x.gif" title="<?=gettext("Delete");?>" border="0" alt="<?=gettext("Delete");?>" /></a>
											</td>
											</tr>
<?php
}
?>										
                                        <tr>
												<td class="list" colspan="5"></td>
												<td class="list">
													<a href="services_appmedia_edit.php"><img src="plus.gif" title="<?=gettext("Add");?>" border="0" alt="<?=gettext("Add");?>" /></a>
												</td>
										</tr>		
		<?php include("formend.inc");?>
		</form>		
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
