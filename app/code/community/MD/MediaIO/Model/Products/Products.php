<?php
Mage::helper('mediaio');
include_once(FILE_HANDLER);
include_once(MEDIA);

class Products
{
    public function __construct()
    {
        $this->helper = Mage::helper('mediaio');
    }

    public function importData($template, $isUpdate = false)
    {
        $this->setDataConfiguration($template, false, $isUpdate);
        $productsOk = 0;
        $this->row = $this->fileHandler->getNextRow();
        $lastRow = $this->fileHandler->getFileLastRow();
        if (!$this->helper->isLastBatch($lastRow, $this->row)) {
            $isLastBatch = false;
            $lastRow = ROWS_PER_TIME + $this->row;
        } else {
            $isLastBatch = true;
        }
        for ($this->row; $this->row <= $lastRow; $this->row++) {
            $sku = $this->fileHandler->getValueByHeaderAndRow('sku', $this->row);
            if ($sku == '') {
                $this->helper->displayLog(false, "<b>Row [$this->row]</b> - empty SKU, process ended.");
                $isLastBatch = true;
                break;
            }
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if (!$this->productExists($product) && $this->isUpdate) {
                $this->helper->displayLog(false, "<b>Row [$this->row]</b> - Product: <b>[$sku]</b> not fount, row skipped.");
                continue;
            } 

            $product = $this->buildDataForImport($product);
            try {
                $product->save();
                $productsOk++;
            } catch (Exception $e) {
                $this->helper->displayLog(false, "<b>Row [$this->row]</b> Error saving product <b>[$sku]</b>");
            }
        }
        $this->fileHandler->updateTempConfigFile($this->row, $productsOk);
        $totalProductsOk = $this->fileHandler->getRowsOk();
        $this->helper->displayLog(false, "Products OK <b>[$totalProductsOk]</b>", "progress", false);
        if ($isLastBatch) {
            $this->helper->cleanCache();
            $this->fileHandler->dropFiles();
            $this->helper->displayLog(true, "Products updated!");
        }
    }

    private function setDataConfiguration($template, $isExport, $isUpdate)
    {
        $this->template = $template;
        $this->isUpdate = $isUpdate;
        $this->fileHandler = new FileHandler(NULL, $this->template, $isExport);
        $this->customHeaders = $this->fileHandler->getCustomHeaders();
        $this->headers = $this->fileHandler->getHeaders();
        $this->extras = $this->fileHandler->getExtras();
    }

    private function productExists($product)
    {
        if ($product) {
            return true;
        }
        return false;
    }

    private function buildDataForImport($product)
    {
        foreach ($this->headers as $code => $col) {
            $value = $this->fileHandler->getValueByHeaderAndRow($code, $this->row);
            if (!$this->fileHandler->isExtra($code, $this->extras)) {
                $product->setData($code, $value);
            } else {
                $extraFunction = $this->fileHandler->getExtraFunction($code);
                $product = call_user_func(array($this, $extraFunction), $product, $code, $value);
            }
        }
        return $product;
    }

    public function exportData($template, $chosenCategory)
    {
        $this->setDataConfiguration($template, true, false);
        $this->chosenCategory = $chosenCategory;
        $allProducts = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*');
        $this->buildDataForExport($allProducts);
        $this->fileHandler->saveFile();
    }

    private function buildDataForExport($allProducts)
    {
        $row = 2;
        foreach ($allProducts as $product) {
            if ($this->isInChosenCategory($product)) {
                foreach ($this->headers as $code => $col) {
                    if (!$this->fileHandler->isExtra($code, $this->extras)) {
                        $this->fileHandler->setValueByHeaderAndRow($code, $row, $product[$code]);
                    } else {
                        $extraFunction = $this->fileHandler->getExtraFunction($code);
                        $value = call_user_func(array($this, $extraFunction), $product, $code);
                        $this->fileHandler->setValueByHeaderAndRow($code, $row, $value);
                    }
                }
                $row++;
            }
        }
    }

    private function isInChosenCategory($product)
    {
        if ($this->chosenCategory == 'all') {
            return true;
        }
        $product = Mage::getModel('catalog/product')->load($product->getId());
        $categories = $product->getCategoryCollection();
        foreach ($categories as $category) {
            if ($category->getId() == $this->chosenCategory) {
                return true;
            }
        }
        return false;
    }

    /** Extra functions */

    private function getImage($product)
    {
        $objMedia = new Media($product);
        return $objMedia->getImage();
    }

    private function getSmallImage($product)
    {
        $objMedia = new Media($product);
        return $objMedia->getSmallImage();
    }

    private function getThumbnail($product)
    {
        $objMedia = new Media($product);
        return $objMedia->getThumbnail();
    }

    private function getMediaGallery($product)
    {
        $objMedia = new Media($product);
        return $objMedia->getMediaGallery();
    }

    private function setImage($product, $attrCode, $imagePath) 
    {
        $objMedia = new Media($product);
        $product = $objMedia->setImage($imagePath);
        return $product;
    }

    private function setSmallImage($product, $attrCode, $smallImagePath) 
    {
        $objMedia = new Media($product);
        $product = $objMedia->setSmallImage($smallImagePath);
        return $product;
    }

    private function setThumbnail($product, $attrCode, $thumbnailPath) 
    {
        $objMedia = new Media($product);
        $product = $objMedia->setThumbnail($thumbnailPath);
        return $product;
    }
    private function setMediaGallery($product, $attrCode, $mediaGalleryStr) 
    {
        $objMedia = new Media($product);
        $product = $objMedia->setMediaGallery($mediaGalleryStr);
        return $product;
    }

}
