<?php
/**
* @author 	iStocker <skype:DeathsInTheSky> http://kekomundo.com/foro/index.php?action=profile;u=12804
* @package 	AutoUpdate
* @version 	1.0
**/

class Update
{
	public 		$_currentVersion;
	public 		$_actualVersion;
	public		$_updateUrl;
	public		$_updateFile;
	public		$_installDir;
	public 		$_downloadDir;
	public 		$_deleteZip;

	/**
	* @param 	string 	$cVersion 	Versión del script
	* @param 	string 	$uUrl 		URL del servidor
	* @param 	string 	$iDir 		Directorio raíz de la instalación
	* @param 	string 	$uFile 		Fichero del servidor que tiene la versión actual
	* @param 	string 	$uCarpet 	Directorio donde se alojará el archivo descargado (ZIP) según el directorio raíz de instalación
	* @param 	bool 	$dZip 		Borrar el fichero ZIP al final de la instalación
	**/
	public function __construct($cVersion, $uUrl, $iDir, $uFile = 'updateFile.php', $uCarpet = 'upload', $dZip = true)
	{
		$this->_currentVersion = $cVersion;
		$this->_installDir = $iDir;
		$this->_updateFile = $uFile;
		$this->_deleteZip = $dZip;

		$this->setDownloadDir($this->_installDir . DIRECTORY_SEPARATOR . $uCarpet . DIRECTORY_SEPARATOR);
		$this->setUpdateUrl($uUrl);
	}

	/**
	* @param 	string 	$dDir 	Directorio donde se alojará el archivo descargado (ZIP) según el directorio raíz de instalación
	*
	* @return 	void 			Comprueba que exista el directorio, caso contrario lo crea.
	**/
	public function setDownloadDir($dDir)
	{
		if(!file_exists($dDir))
			mkdir($dDir, 0777, true);

		$this->_downloadDir = $dDir; 
	}

	/**
	* @param 	string[url] 	URL del servidor de descarga.
	*
	* @return 	void 			Comprueba la legitimidad de la url.
	**/
	public function setUpdateUrl($updateUrl)
	{
		if(!filter_var($updateUrl, FILTER_VALIDATE_URL))
			throw new Exception('URL no válida ' . $updateUrl);

		$this->_updateUrl = $updateUrl;
	}

	/**
	* @return 	boolean 		Comprueba las versiones.
	**/
	public function checkVersion()
	{
		$this->_actualVersion = file_get_contents($this->_updateUrl . $this->_updateFile);

		if(!$this->_actualVersion)
			throw new Exception('El archivo que intentas consultar no existe en la URL ' . $this->_updateUrl . $$this->_updateFile);

		return version_compare($this->_currentVersion, $this->_actualVersion, '<');
	}

	/**
	* @return 	boolean 		Comprueba si ha sido descargado el fichero anteriormente, caso contrario lo descarga y lo almacena.
	**/
	public function updateDownload()
	{
		if(file_exists($this->_downloadDir . $this->_actualVersion . '.zip'))
			return true;

		$tmpRead = file_get_contents($this->_updateUrl . $this->_actualVersion . '.zip');

		if(!$tmpRead)
			throw new Exception('El fichero ZIP no se encuentra en el servidor. Intenta de nuevo más tarde.');

		$tmpDir = new SplFileInfo($this->_downloadDir . $this->_actualVersion . '.zip');
		$tmpObj = $tmpDir->openFile('w'); 

		if(!$tmpDir->isWritable())
			throw new Exception('Imposible crear fichero descargado por errores de escritura');

		$tmpObj->fwrite($tmpRead);

		unset($tmpRead);
		unset($tmpDir);
		unset($tmpObj);

		return true;
	}

	/**
	* @return 	boolean 		Descomprime el archivo y lo elimina en caso de así haber sido configurado.
	**/
	public function unZip()
	{
		$tmpZip = new ZipArchive;

		if(!$r = $tmpZip->open($this->_downloadDir  . $this->_actualVersion . '.zip'))
			throw new Exception('Imposible descomprimir archivo ZIP');

		$tmpZip->extractTo($this->_installDir);
		$tmpZip->close();

		if($this->_deleteZip)
			$this->deleteZip();

		return true;
	}

	/**
	* @return 	boolean 		Comprueba si el archivo existe, caso contrario lo elimina.
	**/
	public function deleteZip()
	{
		if(!file_exists($this->_downloadDir  . $this->_actualVersion . '.zip'))
			return true;

		return unlink($this->_downloadDir  . $this->_actualVersion . '.zip');
	}
}
?>