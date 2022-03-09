<?php
Mage::helper('mediaio');

class MD_MediaIO_Adminhtml_TemplatesController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return true;
    }
    
    public function setAction()
    {
        $template_file = $_POST['template'];
        $this->path = $_POST['path'];
        $template = json_decode(file_get_contents($this->path . $template_file), true);
?>
        <p class="description-text" style="display: none;">Select template:<br> <!-- DEFAULT -->
            <select name="template" id="template">
                <?php
                foreach ($this->getTemplates() as $file => $name) {
                    $selected = $file == $template_file ? 'selected' : '';
                    echo "<option value='$file' $selected>$name</option>";
                } ?>
            </select>
        </p>
        <table class="attr-table">
            <tr>
                <p class="description-text"><b>File columns:</b></p>
                <?php
                foreach (array_unique($template['attributes']) as $attrLabel) {
                    echo "<td class='products-attr'>$attrLabel</td>";
                }
                if (sizeof($template['extras']) > 0) {
                    foreach ($template['extras'] as $extra) {
                        if (isset($extra['headers'])) {
                            foreach ($extra['headers'] as $attrLabel) {
                                echo "<td class='products-attr'>$attrLabel</td>";
                            }
                        } else if (isset($template['attributes'])) {
                            foreach ($extra['attributes'] as $code => $label) {
                                echo "<td class='products-attr'>$label</td>";
                            }
                        }
                    }
                } ?>
            </tr>
        </table>
        </div>
<?php
    }
    private function getTemplates()
    {
        $templates = array();
        $templates_files = array_diff(scandir($this->path), array('.', '..'));
        foreach ($templates_files as $file) {
            $template = json_decode(file_get_contents($this->path . $file), true);
            $templates[$file] = $template['name'];
        }
        return $templates;
    }
}
