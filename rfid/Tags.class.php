<?php

/**
 *  class Tags
 *  
 *	Менажира номерата, които биха били прочетени от rfid четците.
 *	Прави връзката между различните начини на прочитане от различните четци.
 *
 */

class rfid_Tags extends Core_Manager {
    
    var $title = 'Карти';
    
    var $loadList = 'plg_Created,plg_RowTools,rfid_Wrapper';
    
    
    /**
     *  Описание на модела (таблицата)
     */
    function description()
    {
        
        $this->FLD('rfid_55d', 'varchar(16)','caption=Rfid номер->WEG32 08h>55d<br>Завод ВТ');
        $this->FLD('rfid_10d', 'varchar(16)','caption=Rfid номер->1:1 08h>10d<br>Завод Леденик');
        
        $this->setDbUnique('rfid_55d');
        $this->setDbUnique('rfid_10d');
    }
    
    
    /**
     *  Попълва непопълнения от 2-та номера преди да се запише в базата 
     */
    function on_BeforeSave($mvc, &$id, $rec)
    {
        if (!empty($rec->rfid_55d)) {
            $rec->rfid_10d = $this->convert55dTo10d($rec->rfid_55d);
            $rec->rfid_55d = (int) $rec->rfid_55d;
        } elseif (!empty($rec->rfid_10d)) {
            $rec->rfid_55d = $this->convert10dTo55d($rec->rfid_10d);
            $rec->rfid_10d = (int) $rec->rfid_10d;
        }
    }
    
    
    /**
     *
     * Конвертира тип показване 55d към 10d
     * @param string $num
     */
    function convert55dTo10d($num)
    {
        $numLast5d = sprintf("%04s",dechex(substr($num,-5)));
        $numFirst5d = dechex(substr($num,0,strlen($num)-5));
        
        return hexdec($numFirst5d . $numLast5d);
    }
    
    
    /**
     *
     * Конвертира тип показване 55d към 10d
     * @param int $num
     */
    function convert10dTo55d($num)
    {
        $numHex = dechex($num);
        $numLast5d = sprintf("%05d",hexdec(substr($numHex,-4)));
        $numFirst5d = hexdec(substr($numHex,0,strlen($numHex)-4));
        
        return ($numFirst5d . $numLast5d);
    }
}