<?php



/**
 * Интерфейс за IP RFID рийдър
 *
 *
 * @category  bgerp
 * @package   rfid
 * @author    Dimiter Minekov <mitko@extrapack.com>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @title     Драйвер на RFID четец
 */
class rfid_HolderIntf
{
    
    
    /**
     * Връща запис с IP четеца или база данни
     */
    function getData($date)
    {
        $this->class->getData($date);
    }
    
    
    /**
     * Връща запис с IP четеца или база данни
     */
    function getDocComment()
    {
    	$this->class->getData();
    }
}