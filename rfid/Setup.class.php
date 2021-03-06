<?php



/**
 * class acc_Setup
 *
 * Инсталиране/Деинсталиране на
 * мениджъри свързани с rfid
 *
 *
 * @category  bgerp
 * @package   rfid
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class rfid_Setup extends core_ProtoSetup
{
    
    
    /**
     * Версия
     */
    var $version = '0.1';
    
    
    /**
     * Мениджър - входна точка в пакета
     */
    var $startCtr = 'rfid_Events';
    
    
    /**
     * Екшън - входна точка в пакета
     */
    var $startAct = 'default';
    
    
    /**
     * Описание на модула
     */
    var $info = "RFID отчитане на раб. време";
    
    
    /**
     * Списък с мениджърите, които съдържа пакета
     */
    var $managers = array(
            'rfid_Readers',
            'rfid_Events',
            'rfid_Tags',
            'rfid_Holders',
            'rfid_Ownerships'
        );

        
    /**
     * Роли за достъп до модула
     */
    var $roles = 'rfid';

    
    /**
     * Връзки от менюто, сочещи към модула
     */
    var $menuItems = array(
            array(3.4, 'Мониторинг', 'RFID', 'rfid_Events', 'default', "rfid, ceo,admin"),
        );

        
    /**
     * Де-инсталиране на пакета
     */
    function deinstall()
    {
        // Изтриване на пакета от менюто
        $res .= bgerp_Menu::remove($this);
        
        return $res;
    }
}