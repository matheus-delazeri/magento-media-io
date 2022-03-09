<?php

class MD_MediaIO_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function __construct()
    {
        define('MODEL', __DIR__ . '/../Model/');

        define('TEMPLATES_PRODUCTS_EXPORT', MODEL . 'Products/Templates/Export/');
        define('TEMPLATES_PRODUCTS_UPDATE', MODEL . 'Products/Templates/Update/');

        define('MEDIA', MODEL . 'Products/Media.php');
        define('FILE_HANDLER', MODEL . 'FileHandler.php');

        define('PRODUCTS', MODEL . 'Products/Products.php');

        define('TEMP', Mage::getBaseDir() . '/var/tmp/mediaio/');
        define('TEMP_CONFIG', TEMP . 'config.json');

        define('VALID_EXTENSIONS', 'csv');
        define('VALUES_SEPARATOR', ';');
        define('ROWS_PER_TIME', 100);
    }

    public function displayStoreCategories()
    {
?>
        <p class="description-text">Filter by category:</p>
        <select name='category'>
            <option value='all'>All categories</option>

            <?php
            foreach ($this->getCategories() as $id => $category) {
                echo "<option value='$id'>$category</option>";
            }
            ?>
        </select>
        <br><br>

        <?php
    }

    private function getCategories()
    {
        $categoriesNames = array();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addAttributeToSelect('*');
        foreach ($categories as $category) {
            $name = $category['name'];
            if ($name == '') {
                continue;
            }
            $categoriesNames[$category->getId()] = $name;
        }
        return $categoriesNames;
    }

    public function cleanCache()
    {
        Mage::app()->cleanCache();
        Mage::dispatchEvent('adminhtml_cache_flush_system');
        $this->displayLog(false, "Flushing cache...");
    }

    public function displayLog($isFinal, $msg, $elementId = "details", $append = true)
    {
        if ($append) {
        ?>
            <script>
                parent.document.getElementById("<?php echo $elementId; ?>").innerHTML += "<p>[<?php echo date('H:i:s') ?>] <?php echo $msg; ?></p>";
            </script>
        <?php
        } else {
        ?>
            <script>
                parent.document.getElementById("<?php echo $elementId; ?>").innerHTML = "<p>[<?php echo date('H:i:s') ?>] <?php echo $msg; ?></p>";
            </script>
        <?php
        }
        if ($isFinal) {
        ?>
            <script>
                parent.document.getElementById("loader").innerHTML = "";
            </script>
<?php
            echo str_pad('', 4096) . "\n";
            flush();
            ob_flush();
            exit(0);
        }
        echo str_pad('', 4096) . "\n";
        flush();
        ob_flush();
    }

    public function isLastBatch($lastRow, $row)
    {
        return $lastRow >= ($row + ROWS_PER_TIME) ? false : true;
    }

}
