<?php
Mage::helper('mediaio');
require(PRODUCTS);

class MD_MediaIO_Adminhtml_Media_UpdateController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
	{
		return true;
	}

    public function indexAction()
    {
       $this->_title($this->__('Update Images'))
            ->loadLayout()
            ->_setActiveMenu('catalog/media')
            ->renderLayout(); 
    }

    public function updateAction()
    {
        $tempConfig = json_decode(file_get_contents(TEMP_CONFIG), true);
        $templateName = $tempConfig['template'];
        $templatePath = TEMPLATES_PRODUCTS_UPDATE . $templateName;
        (new Products())->importData($templatePath, true);
    }
}