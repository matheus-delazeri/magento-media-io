<?php
Mage::helper('mediaio');
require(PRODUCTS);

class MD_MediaIO_Adminhtml_Media_ExportController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
	{
		return true;
	}

    public function indexAction()
    {
       $this->_title($this->__('Export Images'))
            ->loadLayout()
            ->_setActiveMenu('catalog/media')
            ->renderLayout(); 
    }

    public function exportAction()
	{
		$templateName = $_POST['template'];
		$chosenCategory = $_POST['category'];
		$templatePath = TEMPLATES_PRODUCTS_EXPORT . $templateName;
        (new Products())->exportData($templatePath, $chosenCategory);
	}	
}