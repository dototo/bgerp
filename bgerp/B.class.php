<?php


/**
 * Съкратени URL-та към други класове
 *
 * @category  bgerp
 * @package   bgerp
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class bgerp_B extends core_Manager
{
    
    
    /**
     * Заглавие
     */
    var $title = 'Съкратени URL-та към други класове';
    
    
    /**
     * Съкратен екшън за отписване от blast имейлите
     */
    function act_U()
    {
        
        return Request::forward(array('Ctr' => 'blast_Emails', 'Act' => 'Unsubscribe'));
    }

    
    /**
     * Създава пряк път до статиите в блога
     */
    function act_Default()
    {
        
        return Request::forward(array('Index'));
    }
}