<?php
require 'src/class.update.php';

try
{
	if(!file_exists('config.json')) 
		throw new Exception('El archivo de configuración (config.json) no existe.');

	$config = json_decode(file_get_contents('config.json'), true);
	$update = new Update($config['current'], $config['url-server'], realpath(dirname(__FILE__)), $config['file-server']);

	if($update->checkVersion())
	{
		echo 'Hay una nueva versión disponible, para actualizar sigue <a href="?update=true">este enlace</a>. <br>';

		if(isset($_GET['update']) && $_GET['update'])
		{
			echo 'Iniciando actualización. <br>';

			if($update->updateDownload())
				echo 'El archivo ZIP ha sido descargado existosamente en la carpeta asignada (<b>' . $update->_downloadDir . $update->_actualVersion . '.zip</b>) o ya había sido descargado.<br>';

			if($update->unZip())
				echo 'Actualización exitosamente realizada';
		}
	}
	else
	{
		echo 'Tu versión está actualizada.';
	}
}
catch (Exception $e)
{
	die("<b>ERROR »</b> ‘<i>{$e->getMessage()}</i>’");
}
?>