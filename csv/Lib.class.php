<?php



/**
 * Клас 'csv_Lib' - Пакет за работа с CSV файлове
 *
 *
 * @category  vendors
 * @package   csv
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @todo:     Да се документира този клас
 */
class csv_Lib
{
        
    /**
     * Импортира CSV файл в указания модел
     */
    static function import($mvc, $file, $fields = array(), $defaults = array(), $format = array())
    {   
        // Дефолт стойностите за форматирането по подразбиране
        setIfNot($format['length'], 0);
        setIfNot($format['delimiter'], ',');
        setIfNot($format['enclosure'], '"');
        setIfNot($format['escape'], '\\');
        setIfNot($format['skip'], '#');
        
        $firstRow = TRUE; 
        $res    = (object) array('created' => 0, 'updated' => 0, 'skipped' =>0);
        $fields = arr::make($fields);

        $fromZero = !$mvc->fetch("1=1");
        
        $path = getFullPath($file);

        expect(($handle = fopen($path, "r")) !== FALSE);

        while (($data = fgetcsv($handle, $format['length'], $format['delimiter'], $format['enclosure'], $format['escape'])) !== FALSE) {

            // Пропускаме празните линии
            if(!count($data) || (count($data) == 1 && trim($data[0]) == '')) continue;

            // Пропускаме редовете със знака указан в $skip
            if($data[0]{0} == $format['skip']) continue;

            // Ако не са указани полетата, вземаме ги от първия ред
            if($firstRow && !count($fields)) {
                foreach($data as $f) {
                    $fields[] = $f;
                }
                
                $firstRow = FALSE;
            } else {
                // Вкарваме данните
                if($defaults) {
                    $rec = (object)$defaults;
                } else {
                    $rec = new stdClass();
                }
                
                foreach($fields as $i => $f) {
                    $rec->{$f} = $data[$i];
                }
                
                if ($mvc->invoke('BeforeImportRec', array(&$rec)) === FALSE) continue ;
				
                // Ако таблицата се попълва от нулата, само се добавят редове
                if($fromZero) {
                    $mvc->save($rec);
                    $res->created++;
                    continue;
                }
                
                $conflictFields = array();

                if(!$mvc->isUnique($rec, $conflictFields, $exRec)) {
                    $rec->id = $exRec->id;
                    $flagUpdate = TRUE;
                } else {
                    $res->created++;
                    $flagUpdate = FALSE;
                }
                
                // По подразбиране записът е добавен от системния потребител
                setIfNot($rec->createdBy, -1);
				
                // Ако нямаме запис с посочените уникални стойности, вкарваме новия
                $mvc->save($rec);
                
                if($flagUpdate) {
                    $res->skipped++;
                    $rec = $mvc->fetch($rec->id);
                    foreach($fields as $i => $f) {
                        if($rec->{$f} != $exRec->{$f}) {
                            $res->updated++;
                            $res->skipped--;
                            break;
                        }
                    }
                }
            }
        }
            
        fclose($handle);

        $res->html = self::cntToVerbal($res, $mvc->className);
        
        return $res;
    }


    /**
     * Функция, която импортира еднократно даден csv файл в даден модел
     */
    static function importOnce($mvc, $file, $fields = array(), $defaults = array(), $format = array(), $delete = FALSE)
    {
        // Пътя до файла с данните
        $filePath = getFullPath($file);
        
        // Името на променливата, в която се записва хеша на CSV файла
        $param = 'csvFile' . preg_replace('/[^a-z0-9]+/', '_', $file);
        
        // Хеша на CSV данните
        $hash = md5_file($filePath);

        list($pack,) = explode('_', $mvc->className);
        
        // Конфигурация на пакета 'lab'
        $conf = core_Packs::getConfig($pack);

        $cntObj = new stdClass();
        
        try {
            $confHash = $conf->{$param};
        } catch (core_exception_Expect $e) {
            $confHash = NULL;
        }
        
        if(($confHash != $hash) || ($delete === 'everytime')) {
 
            // Изтриваме предишното съдържание на модела, ако е сетнат $delete
            if($delete) {
                $mvc->db->query("TRUNCATE TABLE `{$mvc->dbTableName}`");
            }
            
            $cntObj = self::import($mvc, $file, $fields, $defaults, $format);
            
            // Записваме в конфигурацията хеша на последния приложен csv файл
            core_Packs::setConfig($pack, array($param => $hash));
        } else {
            $cntObj = (object) array('created' => 0, 'updated' => 0, 'skipped' =>0, 'html' => "\n<li>Пропуснато импортиране в {$mvc->className}, защото няма промяна в CSV файла</li>");
        }

        return $cntObj;
    }


    /**
     * Импортира съдържанието на посочения CSV файл, когато той е променян
     * Преди импортирането изпразва таблицата, 
     */
    static function importOnceFromZero($mvc, $file, $fields = array(), $defaults = array(), $format = array())
    {
        return self::importOnce($mvc, $file, $fields, $defaults, $format, TRUE);
    }


    /**
     * Връща html вербално представяне на резултата от ::import(...)
     */
    static function cntToVerbal($cntObj, $place = NULL)
    {
        $res = '';
        
        if($place) {
            $place = " в {$place}";
        }

        if($cntObj->created) {
            $res .= "\n<li style='color:green;'>Създадени са {$cntObj->created} записа{$place}</li>";
        }
            
        if($cntObj->updated) {
            $res .= "\n<li style='color:#600;'>Обновени са {$cntObj->updated} записа{$place}</li>";
        }
            
        if($cntObj->skipped) {
            $res .= "\n<li>Пропуснати са {$cntObj->skipped} записа{$place}</li>";
        }

        return $res;
    }
    
    
    /**
     * Създава csv
     * 
     * @param array $recs
     * @param core_FieldSet $fieldSet
     * @param string $listFields
     * @param array $params
     * 
     * @return string
     */
    static function createCsv($recs, core_FieldSet $fieldSet, $listFields = NULL, $params = array())
    {
        $params = arr::make($params, TRUE);
        
        // Редиректваме, ако сме надвишили бройката
        setIfNot($exportCnt, $params['maxExportCnt'], core_Setup::get('EF_MAX_EXPORT_CNT', TRUE));
        if(count($recs) > $exportCnt) {
            $retUrl = getRetUrl();
            if (empty($retUrl)) {
                if ($fieldSet instanceof core_Manager) {
                    if ($fieldSet->haveRightFor('list')) {
                        $retUrl = array($fieldSet, 'list');
                    }
                }
            }
            	
            if (empty($retUrl)) {
                $retUrl = array('Index');
            }
            	
            redirect($retUrl, FALSE, "|Броят на заявените записи за експорт надвишава максимално разрешения|* - " . $exportCnt, 'error');
        }
        
        if (isset($listFields)) {
            $listFields = arr::make($listFields, TRUE);
        } else {
            $fieldsArr = $fieldSet->selectFields("");
            $listFields = array();
            foreach ($fieldsArr as $name => $fld) {
                $listFields[$fld->name] = $fld->caption;
            }
        }
        
        $delimiter = str_replace(array('&comma;', 'semicolon', 'colon', '&vert;', '&Tab;', 'comma', 'vertical'), array(',', ';', ':', '|', "\t", ',', '|'), csv_Setup::get('DELIMITER'));

        if(strlen($delimiter) > 1) {
            $delimiter = html_entity_decode($delimiter, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }

        setIfNot($csvDelimiter, $params['delimiter'], $delimiter);
        setIfNot($decPoint, $params['decPoint'], html_entity_decode(core_Setup::get('EF_NUMBER_DEC_POINT', TRUE), ENT_COMPAT | ENT_HTML401, 'UTF-8'));
        setIfNot($dateFormat, $params['dateFormat'], core_Setup::get('EF_DATE_FORMAT', TRUE));
        setIfNot($datetimeFormat, $params['datetimeFormat'], 'd.m.y H:i');
        setIfNot($thousandsSep, $params['thousandsSep'], '');
        setIfNot($enclosure, $params['enclosure'], '"');
        setIfNot($decimals, $params['decimals'], 2);
        
        // Вземаме колоните, ако са зададени
        if ($params['columns'] != 'none') {
            foreach ($listFields as $fld => $caption) {
                if (!$caption) {
                    $listFields[$fld] = $fld;
                }
            }
            
            $csv = self::getCsvLine($listFields, $csvDelimiter, $enclosure);
        }
        
        // Подготвяме редовете
        foreach($recs as $rec) {
            
            $rCsvArr = array();
            foreach ($listFields as $name => $caption) {
                
                if ($fieldSet->fields[$name]) {
                    $type = $fieldSet->fields[$name]->type;
                } else {
                    $type = new stdClass();
                }
                
                Mode::push('text', 'plain');
                if ($type instanceof type_Key) {
                    $value = $type->toVerbal($rec->{$name});
                } elseif ($type instanceof type_Keylist) {
                    $value = $type->toVerbal($rec->{$name});
                } elseif ($type instanceof type_Set) {
                    $value = $type->toVerbal($rec->{$name});
                } elseif ($type instanceof type_Double) {
                    $type->params['decPoint'] = $decPoint;
                    $type->params['thousandsSep'] = $thousandsSep;
                    $type->params['decimals'] = $decimals;
                    $value = $type->toVerbal($rec->{$name});
                } elseif ($type instanceof type_Datetime) {
                    $value = dt::mysql2verbal($rec->{$name}, $datetimeFormat);
                    $value = strip_tags($value);
                } elseif ($type instanceof type_Date) {
                    $value = dt::mysql2verbal($rec->{$name}, $dateFormat);
                    $value = strip_tags($value);
                } elseif ($type instanceof type_Richtext && !empty($params['text'])) {
                    Mode::push('text', $params['text']);
                    $value = $type->toVerbal($rec->{$name});
                    Mode::pop('text');
                } elseif ($type instanceof fileman_FileType) {
                    $value = toUrl(array('F', 'D', $rec->{$name}), 'absolute');
                } elseif ($type instanceof type_Enum) {
                    $value = $type->toVerbal($rec->{$name});
                } elseif ($type instanceof fileman_FileSize) {
                    $value = $type->toVerbal($rec->{$name});
                } else {
                    $value = $rec->{$name};
                }
                Mode::pop('text');
                
                $rCsvArr[] = $value;
            }
            
            $csv .= ($csv) ? "\n" : '';
            
            $csv .= self::getCsvLine($rCsvArr, $csvDelimiter, $enclosure);
        }
        
        return $csv;
    }
    
    
    /**
     * Масива го преобразува в ред за CSV
     * 
     * @param array $valsArr
     * @param string $delimiter
     * @param string $enclosure
     * 
     * @return string
     */
    public static function getCsvLine($valsArr, $delimiter, $enclosure, $trim = TRUE)
    {
        $csvLine = NULL;
        foreach ($valsArr as $v) {
            if ($trim) {
                $v = trim($v);
            }
            $v = self::prepareCsvVal($v, $delimiter, $enclosure);
            $csvLine = (isset($csvLine)) ? $csvLine . $delimiter : '';
            $csvLine .= $v;
        }
        
        return $csvLine;
    }
    
    
    /**
     * Подоготвя стойност за CSV
     * 
     * @param string $val
     * @param string $delimiter
     * @param string $enclosure
     * 
     * @return string
     */
    protected static function prepareCsvVal($val, $delimiter, $enclosure)
    {
        $enclosure = preg_quote($enclosure, '/');
        $delimiter = preg_quote($delimiter, '/');
        
        if (preg_match("/\r|\n|{$delimiter}|{$enclosure}/", $val)) {
            $val = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $val) . $enclosure;
        }
        
        return $val;
    }
    
    /**
     * Връща масив с данните от CSV стринга
     * 
     * @param string $csvData - csv данни
     * @param char $delimiter - разделител
     * @param char $enclosure - ограждане
     * @param string $firstRow - първи ред данни или имена на колони
     * 
     * @return array $rows - масив с парсирани редовете на csv-то
     */
    public static function getCsvRows($csvData, $delimiter = NULL, $enclosure = NULL, $firstRow = 'columnNames')
    {
        $strPath = fileman::addStrToFile($csvData, 'file.csv');
        
        $csvData = i18n_Charset::convertToUtf8($csvData);
        
        $rowsArr = self::getCsvRowsFromFile($strPath, array('delimiter' => $delimiter, 'enclosure' => $enclosure, 'firstRow' => $firstRow));
        
        fileman::deleteTempPath($strPath);
        
        return $rowsArr['data'];
    }
    
    
    /**
     * Връща имената на колоните от CSV файла
     * 
     * @param unknown $csvData
     * @param string $delimiter
     * @param string $enclosure
     * @param boolean $firstEmpty
     * @param boolean $checkErr
     * 
     * @return array
     */
    public static function getCsvColNames($csvData, $delimiter = NULL, $enclosure = NULL, $firstEmpty = FALSE, $checkErr = FALSE)
    {
        $strPath = fileman::addStrToFile($csvData, 'file.csv');
        
        $csvData = i18n_Charset::convertToUtf8($csvData);
        
        $rowsArr = self::getCsvRowsFromFile($strPath, array('delimiter' => $delimiter, 'enclosure' => $enclosure, 'firstRow' => 'columnNames'));
        
        fileman::deleteTempPath($strPath);
        
        if ($checkErr && $rowsArr['error']) {
            
            return array();
        }
        
        $resArr = (array) $rowsArr['firstRow'];
        
        if ($firstEmpty) {
            $resArr = arr::combine(array(NULL => ''), $resArr);
        }
        
        return $resArr;
    }
    
    
    /**
     * Връща редовете от CSV файла
     * 
     * @param string $path
     * @param array $params
     * 
     * @return array
     */
    public static function getCsvRowsFromFile($path, $params = array())
    {
        expect(($handle = fopen($path, "r")) !== FALSE);
        setIfNot($params['length'], 0);
        setIfNot($params['delimiter'], ',');
        setIfNot($params['enclosure'], '"');
        setIfNot($params['escape'], '\\');
        setIfNot($params['firstRow'], 'columnNames');
        setIfNot($params['check'], TRUE);
        setIfNot($params['skip'], '#');
        
        $resArr = array();
        $resArr['firstRow'] = array();
        $resArr['error'] = FALSE;
        $resArr['data'] = array();
        
        $isFirst = TRUE;
        $oldCnt = NULL;
        
        while (($data = fgetcsv($handle, $params['length'], $params['delimiter'], $params['enclosure'], $params['escape'])) !== FALSE) {
            
            // Пропускаме празните линии
            if(!count($data) || (count($data) == 1 && trim($data[0]) == '')) continue;

            // Пропускаме редовете със знака указан в $skip
            if($data[0]{0} == $params['skip']) continue;
            
            if ($params['check']) {
                
                $cnt = count($data);
                
                if (!$resArr['error'] && isset($oldCnt) && ($cnt != $oldCnt)) {
                    $resArr['error'] = TRUE;
                }
                
                $oldCnt = $cnt;
            }
            
            array_unshift($data, "");
            unset($data[0]);
            
            if (($params['firstRow'] == 'columnNames') && $isFirst) {
                $isFirst = FALSE;
                $resArr['firstRow'] = $data;
            } else {
                $resArr['data'][] = $data;
            }
        }
        
        return $resArr;
    }
}
