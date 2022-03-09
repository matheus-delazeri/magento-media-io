<?php
Mage::helper('mediaio');

class Media
{
    public function __construct($product)
    {
        $this->helper = Mage::helper('mediaio');
        $this->product = $product;
        $this->product->load('media_gallery');
        $this->mediaPath = $this->getMediaPath();
    }

    public function setImage($imagePath)
    {
        if ($imagePath == NULL) return $this->product;
        $imgName = $this->getImageName($imagePath);
        $productImages = $this->getAllImagesWithUrl($this->product);
        if (isset($productImages[$imgName])) {
            $imgUrl = $productImages[$imgName];
            $this->product->setImage($imgUrl);
        } else {
            $imgData = $this->getImageData($imagePath);
            if ($this->isImage($imgData['dir'])) {
                $this->product->addImageToMediaGallery($imgData['dir'], array('image'), $imgData['is_url'], false);
            } else {
                $this->helper->displayLog(false, "<b>[" . $this->product['sku'] . "]</b> - Image skipped: [$imgName]");
                unlink($imgData['dir']);
            }
        }
        $this->product->getResource()->save($this->product);
        return $this->product;
    }

    public function setSmallImage($smallImagePath)
    {
        if ($smallImagePath == NULL) return $this->product;
        $imgName = $this->getImageName($smallImagePath);
        $productImages = $this->getAllImagesWithUrl($this->product);
        if (isset($productImages[$imgName])) {
            $imgUrl = $productImages[$imgName];
            $this->product->setSmallImage($imgUrl);
        } else {
            $imgData = $this->getImageData($smallImagePath);
            if ($this->isImage($imgData['dir'])) {
                $this->product->addImageToMediaGallery($imgData['dir'], array('small_image'), $imgData['is_url'], false);
            } else {
                $this->helper->displayLog(false, "<b>[" . $this->product['sku'] . "]</b> - Image skipped: [$imgName]");
                unlink($imgData['dir']);
            }
        }
        $this->product->getResource()->save($this->product);
        return $this->product;
    }

    public function setThumbnail($thumbnailPath)
    {
        if ($thumbnailPath == NULL) return $this->product;
        $imgName = $this->getImageName($thumbnailPath);
        $productImages = $this->getAllImagesWithUrl($this->product);
        
        if (isset($productImages[$imgName])) {
            $imgUrl = $productImages[$imgName];
            $this->product->setThumbnail($imgUrl);
        } else {
            $imgData = $this->getImageData($thumbnailPath);
            if ($this->isImage($imgData['dir'])) {
                $this->product->addImageToMediaGallery($imgData['dir'], array('thumbnail'), $imgData['is_url'], false);
            } else {
                $this->helper->displayLog(false, "<b>[" . $this->product['sku'] . "]</b> - Image skipped: [$imgName]");
                unlink($imgData['dir']);
            }
        }
        $this->product->getResource()->save($this->product);
        return $this->product;
    }

    public function setMediaGallery($mediaGalleryStr)
    {
        if ($mediaGalleryStr == NULL) return $this->product;
        $mediaGallery = explode(VALUES_SEPARATOR, $mediaGalleryStr);
        $productImages = $this->getAllImagesWithUrl($this->product);
        foreach ($mediaGallery as $imgPath) {
            $imgName = $this->getImageName($imgPath);
            if (!isset($productImages[$imgName])) {
                $imgData = $this->getImageData($imgPath);
                if ($this->isImage($imgData['dir'])) {
                    $this->product->addImageToMediaGallery($imgData['dir'], array(), $imgData['is_url'], false);
                } else {
                    $this->helper->displayLog(false, "<b>[" . $this->product['sku'] . "]</b> - Image skipped: [$imgName]");
                    unlink($imgData['dir']);
                }
            }
        }
        return $this->product;
    }

    private function getImageName($imgPath)
    {
        return str_replace('_', '-', basename($imgPath));
    }

    private function getAllImagesWithUrl()
    {
        $productImages = array();
		foreach ($this->product->getMediaGalleryImages() as $img) {
			$productImages[$this->getImageNameWithoutMagBits(basename($img['url']))] = $img['file'];
		}
		foreach ($this->getMediaArray($this->product) as $type => $imgUrl) {
			if ($imgUrl != 'no_selection') {
				$productImages[$this->getImageNameWithoutMagBits(basename($imgUrl))] = $imgUrl;
			}
		}
        return $productImages;
    }

    private function getMediaArray()
	{
		return array(
			'image' => $this->product->hasImage() ? $this->product->getImage() : false,
			'small_image' => $this->product->hasSmallImage() ? $this->product->getSmallImage() : false,
			'thumbnail' => $this->product->hasThumbnail() ? $this->product->getThumbnail() : false
		);
	}

    private function isImage($imgDir)
    {
        return getimagesize($imgDir) ? true : false;
    }

    private function getImageNameWithoutMagBits($imgName)
    {
        $ext = "." . pathinfo($imgName, PATHINFO_EXTENSION);
        $imgName = str_replace($ext, " " . $ext, $imgName);
        $withoutExt = str_replace($ext, "", $imgName);
        $magCount = strrchr($withoutExt, "_");

        if (is_numeric(str_replace("_", "", str_replace(" ", "", $magCount))))
            $result = str_replace($magCount, "", $imgName);
        else
            $result = str_replace(" ", "", $imgName);

        if (is_numeric(str_replace("_", "", trim(str_replace($ext, "", strrchr($result, "_")))))) {
            return $this->getImageNameWithoutMagBits($result);
        }

        return $result;
    }

    private function getImageData($imgPath)
    {
        $isUrl = false;
        $mediaDir = Mage::getBaseDir('media') . '/import/';
        $imgName = str_replace('_', '-', basename(trim($imgPath)));
        $imgDir = $mediaDir . $imgName;
        $this->createMediaImportIfNotExists();
        if (!file_exists($imgDir) && !file_exists($mediaDir . $imgName)) {
            try {
                file_put_contents($imgDir, file_get_contents($imgPath));
                $isUrl = true;
            } catch (Exception $e) {
                echo $e;
            }
        } 
        
        return array(
            'dir' => $imgDir,
            'is_url' => $isUrl
        );
    }

    private function createMediaImportIfNotExists()
    {
        $mediaImportDir = Mage::getBaseDir('media') . '/import';
        if (!file_exists($mediaImportDir)) {
            mkdir($mediaImportDir, 0777, true);
        }
    }

    public function getImage()
    {
        $image = $this->product->getSmallImage();
        return $this->imageIsSet($image) ? $this->mediaPath . $image : '';
    }

    public function getSmallImage()
    {
        $smallImage = $this->product->getSmallImage();
        return $this->imageIsSet($smallImage) ? $this->mediaPath . $smallImage : '';
    }

    public function getThumbnail()
    {
        $thumbnail = $this->product->getSmallImage();
        return $this->imageIsSet($thumbnail) ? $this->mediaPath . $thumbnail : '';
    }

    public function getMediaGallery()
    {
        $mediaGallery = '';
        foreach ($this->product->getMediaGalleryImages() as $image) {
            $imgName = $image->getFile();
            $imageUrl = $this->mediaPath . $imgName;
            $mediaGallery .= $imageUrl . VALUES_SEPARATOR;
        }
        $mediaGallery = substr($mediaGallery, 0, -1);
        return !empty($mediaGallery) ? $mediaGallery : '';
    }

    private function getMediaPath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
    }

    private function imageIsSet($image)
    {
        if ($image == '' || $image == 'no_selection') {
            return false;
        }
        return true;
    }
}