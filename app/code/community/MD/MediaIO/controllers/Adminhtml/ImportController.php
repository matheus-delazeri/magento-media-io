<?php
Mage::helper('mediaio');
require(FILE_HANDLER);

class MD_MediaIO_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }

    public function validateFileAction()
    {
        $fileHandler = new FileHandler($_FILES['file'], NULL);
        $fileHandler->dropFiles();
        
        if ($fileHandler->fileIsNew() && $fileHandler->fileExtensionIsValid()) {
            $fileHandler->moveFileToTargetPath();
            $fileHandler->loadFile();
            $data = array(
                'file' => $fileHandler->getFileTargetPath(),
                'template' => $_POST['template'],
                'row' => $_POST['row'],
                'last_row' => $fileHandler->getFileLastRow(),
                'rows_ok' => 0
            );
            file_put_contents(TEMP_CONFIG, json_encode($data));
        } else {
            echo 'invalid format'; 
        }
        return;
    }

    public function batchesAction()
    {
        if (!file_exists(TEMP_CONFIG)) {
            echo 'end';
        } else {
            echo 'continue';
        }
        return;
    }

}