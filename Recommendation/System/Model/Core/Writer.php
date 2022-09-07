<?php
namespace Recommendation\System\Model\Core;

use \Magento\Framework\Filesystem\Driver\File;
use \Magento\Framework\Filesystem;
use \Magento\Framework\Filesystem\Io\File as FileIO;
use \Magento\Framework\Filesystem\DirectoryList;
use \Magento\Store\Model\StoreManagerInterface;
/**
 * Classe que gera arquivos json para subir para 
 * a API do sistema de recomendação
 */
class Writer
{
    protected $_file;
    protected $_fileSystem;
    protected $_fileIO;
    protected $_directoryList;
    protected $_storeManager;

    protected $_tableName;
    protected $_data;
    protected $_qtyExported;
    /**
     * Armazena o caminho completo do arquivo
     */
    protected $_filePath;
    /**
     * Armazena a URL completa do arquivo
     */
    protected $_fileUrl;
    /**
     * Armazena apenas o caminho do diretório de exportação
     * e o arquivo interno da exportação
     */
    protected $_filePathExport;
    const DIRECTORY_EXPORT = 'export_api_recomendation';

    public function __construct(
        Filesystem $fileSystem,
        File $file,
        FileIO $fileIO,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManager
    ) {
        $this->_file = $file;
        $this->_fileSystem = $fileSystem;
        $this->_fileIO = $fileIO;
        $this->_directoryList = $directoryList;
        $this->_storeManager = $storeManager;

        /**
         * Seta os caminhos do arquivos de exportação
         */
        $this->_filePathExport = $this->_getFilePathExport();
    }

    /**
     * Função Construtor
     */
    public function init($tableName, $data, $qtyExported){
        $this->_tableName = $tableName;
        $this->_data = $data;
        $this->_qtyExported = $qtyExported;
    }

    /**
     * Salva os dados gerados na exportação
     */
    public function saveData(){
        $filesystemMedia = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        //Obtem o caminho do arquivo de exportação
        $this->_filePath = $this->_getAbsolutePath($filesystemMedia);
        //Retorna se o arquivo existe ou n
        $fileExists = $this->_file->isExists($this->_filePath);
        //Caso começou uma exportação para a tabela
        if($this->_qtyExported == 0 && $fileExists){
            //Deleta o arquivo para fazer uma nova exportação
            $this->_file->deleteFile($this->_filePath);
        }
        $isSaved = $this->_saveFile($this->_filePath, $this->_data);
        if($isSaved)
            return true;
        return false;
    }

    protected function _saveFile($filePath, $data){
        try{
            //Faz a leitura do conteúdo já existente no arquivo
            $dataArray = [];
            $fileExists = $this->_file->isExists($filePath);
            if($fileExists){
                $fp = fopen($filePath, 'r');
                $dataArray = json_decode(fgets($fp));
                fclose($fp);
            }
            //Adiciona o conteudo da nova exportação
            $dataArray[] = $data;
            //Converte para json
            $dataJsonFile = json_encode($dataArray);
            //Salva os dados da exportação
            $fp = fopen($filePath, 'w');
            fwrite($fp, $dataJsonFile);
            fclose($fp);
            return true;
        }
        catch(\Exception $e){
            return false;
        }
    }

    protected function _deleteFile($fileName){
        $res = $this->_file->deleteFile($fileName);
        return $res;
    }

    protected function _getAbsolutePath(&$filesystem){
        $mediaRootDir = $filesystem->getAbsolutePath();
        $pathDirectory = $mediaRootDir . $this->_filePathExport;

        //Verifica se o diretório de exportação foi criado
        if(!is_dir($pathDirectory)){
            //Cria diretório de exportação
            $this->_fileIO->mkdir($pathDirectory, 0755);
        }
        
        return $pathDirectory . '/' . $this->_getNameFileExport();
    }
    /**
     * Obtem apenas o caminho do diretório de exportação
     * e o arquivo interno da exportação
     */
    protected function _getFilePathExport(){
        $dateNow = strval(date('Y-m-d'));
        $pathExport = self::DIRECTORY_EXPORT . '/' . $dateNow;

        return $pathExport;
    }

    /**
     * Obtem o nome do arquivo da exportação
     */
    protected function _getNameFileExport(){
        return $this->_tableName .'.json';
    }

    /**
     * Obtem o link do arquivo pela URL
     */
    public function getFileUrl(){
        //Obtem a URL da pasta media
        $urlMedia = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
        return $urlMedia . $this->_filePathExport . '/' . $this->_getNameFileExport();
    }

}
