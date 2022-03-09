<?php
Mage::helper('mediaio');
include_once(Mage::getBaseDir('lib') . '/PHPExcel/PHPExcel.php');
/**
 * This class handles files imported
 * 
 * @author Matheus Delazeri
 */

class FileHandler
{
    public function __construct($file = NULL, $templatePath = NULL, $isExport = false)
    {
        $this->templatePath = $templatePath;
        $this->helper = Mage::helper('mediaio');
        if(!is_dir(TEMP)){
            mkdir(TEMP, 0777, true);
        }
        if ($file != NULL && $this->templatePath == NULL) {
            $this->fileName = $file['name'];
            $this->filePath = TEMP . basename($this->fileName);
            $this->fileTmpName = $file['tmp_name'];
        } else {
            $this->tempConfig = json_decode(file_get_contents(TEMP_CONFIG), true);
            $this->filePath = $this->tempConfig['file'];
            if($isExport){
                $this->createFile();
                $this->writeHeadersToSheet();
            } else {
                $this->loadFile();
                $this->setHeaders();
            }
        }
    }

    public function getFileTargetPath()
    {
        return $this->filePath;
    }

    public function fileIsNew()
    {
        if (!file_exists($this->filePath)) {
            return true;
        } else {
            return false;
        }
    }

    public function fileExtensionIsValid()
    {
        $fileExtension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
        $validExtensions = explode(VALUES_SEPARATOR, VALID_EXTENSIONS);
        return in_array($fileExtension, $validExtensions) ? true : false;
    }

    public function moveFileToTargetPath()
    {
        try {
            move_uploaded_file($this->fileTmpName, $this->filePath);
            return true;
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function createFile()
    {
        $this->objPHPExcel = new PHPExcel();
        $this->objWorksheet = $this->objPHPExcel->setActiveSheetIndex(0);
        $this->setStructure();
    }

    public function loadFile()
    {
        $objReader = PHPExcel_IOFactory::createReader('CSV');
        $objReader->setReadDataOnly(true);
        $this->objPHPExcel = $objReader->load($this->filePath);
        $this->objWorksheet = $this->objPHPExcel->getActiveSheet();
        $this->setStructure();
    }

    public function getFileLastRow()
    {
        return $this->objWorksheet->getHighestRow();
    }

    public function getFileLastColumn()
    {
        return PHPExcel_Cell::columnIndexFromString($this->objWorksheet->getHighestColumn());
    }

    private function setStructure()
    {
        if($this->templatePath != NULL){
            $this->template = json_decode(file_get_contents($this->templatePath), true);
            $this->extras = isset($this->template['extras']) ? $this->template['extras'] : array();
        }
    }

    public function fileIsSet()
    {
        if (isset($this->tempConfig['file']) && !$this->fileIsNew()) {
            return true;
        }
        return false;
    }

    private function setHeaders()
    {
        $missingHeaders = array();
        $this->headers = array();
        $this->customHeaders = array();
        $this->excelHeaders = $this->getExcelHeaders();
        
        foreach ($this->excelHeaders as $col => $code) {
            if(!isset($this->headers['attributes'][$code])) {
                $this->customHeaders[$code] = $col;
            }
        }
        foreach ($this->template['attributes'] as $code => $label) {
            $headerColumn = $this->getHeaderColumn($code, $label);
            if ($headerColumn < 0) {
                array_push($missingHeaders, $label);
            } else {
                $this->headers[$code] = $headerColumn;
            }
        }
        foreach ($this->extras as $extra => $data) {
            foreach ($data['attributes'] as $code => $label) {
                $headerColumn = $this->getHeaderColumn($code, $label);
                if ($headerColumn < 0) {
                    array_push($missingHeaders, $label);
                } else {
                    $this->headers[$code] = $headerColumn;
                }
            }
        }
        if (sizeof($missingHeaders) > 0) {
            $missingHeaders = implode(",", $missingHeaders);
            $this->dropFiles();
            $this->helper->displayLog(true, "Required columns not found: <b>[$missingHeaders]</b>");
        }
    }

    private function getExcelHeaders()
    {
        $excelHeaders = array();
        $lastCol = $this->getFileLastColumn();
        for ($col = 0; $col <= $lastCol; $col++) {
            array_push($excelHeaders, $this->objWorksheet->getCellByColumnAndRow($col, 1)->getValue());
        }
        return $excelHeaders;
    }

    private function getHeaderColumn($code, $label)
    {
        foreach ($this->excelHeaders as $col => $header) {
            $header = strtolower($header);
            $code = strtolower($code);
            $label = strtolower($label);
            if ($header == $code || $header == $label) {
                return $col;
            }
        }
        return -1;
    }

    private function writeHeadersToSheet() {
        $col = 0;
        foreach ($this->template['attributes'] as $code => $header) {
			$this->objWorksheet->setCellValueByColumnAndRow($col, 1, $header);
			$col++;
		}
		foreach ($this->extras as $extra => $data) {
			foreach ($data['attributes'] as $code => $label) {
				$this->objWorksheet->setCellValueByColumnAndRow($col, 1, $label);
				$col++;
			}
		}
		$this->objWorksheet->setTitle('Data Exported');
        $this->setHeaders();
    }

    public function saveFile()
	{
		try {
			$this->objPHPExcel->setActiveSheetIndex(0);
			$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="Data Exported.xlsx"');
			$objWriter->save('php://output');
		} catch (Exception $e) {
			throw new Exception('Error while saving Excel file.');
		}
	}

    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getExtras()
    {
        return $this->extras;
    }

    public function isExtra($code)
    {
        foreach ($this->extras as $extra => $data) {
            if (isset($data['attributes'][$code])) {
                return true;
            }
        }
        return false;
    }

    public function getExtraFunction($code)
    {
        foreach ($this->extras as $extra => $data) {
            if (isset($data['attributes'][$code])) {
                return $data['function'];
            }
        }
    }

    public function getValueByHeaderAndRow($header, $row)
    {
        if(isset($this->headers[$header])) {
            return $this->objWorksheet->getCellByColumnAndRow($this->headers[$header], $row)->getValue();
        } else if(isset($this->customHeaders[$header])) {
            return $this->objWorksheet->getCellByColumnAndRow($this->customHeaders[$header], $row)->getValue();
        }
    }

    public function setValueByHeaderAndRow($header, $row, $value)
    {
        $this->objWorksheet->setCellValueByColumnAndRow($this->headers[$header], $row, $value);

    }

    public function updateTempConfigFile($row, $rowsOk)
    {
        $this->tempConfig['row'] = $row;
        $this->tempConfig['rows_ok'] += $rowsOk;
        file_put_contents(TEMP_CONFIG, json_encode($this->tempConfig));
    }

    public function getNextRow()
    {
        return $this->tempConfig['row'];
    }

    public function getRowsOk()
    {
        return $this->tempConfig['rows_ok'];
    }

    public function dropFiles()
    {
        $files = glob(TEMP . '*');
        foreach($files as $file){ 
            if(is_file($file)) {
                unlink($file);
            }
        }
    }
}
