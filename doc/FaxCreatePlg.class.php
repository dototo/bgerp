<?php



/**
 * Клас 'doc_FaxCreatePlg'
 *
 * Плъгин за добавяне на бутона Факс
 *
 * @category  bgerp
 * @package   doc
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class doc_FaxCreatePlg extends core_Plugin
{
    
    
    /**
     * Добавя бутон за създаване на имейл
     * 
     * @param core_Manager $mvc  - 
     * @param integer      $res  - 
     * @param stdClass     $data - Обект със данните
     */
    function on_AfterPrepareSingleToolbar($mvc, $res, &$data)
    {
        
        if (($data->rec->state != 'draft') && ($data->rec->state != 'rejected') 
            && ($mvc->haveRightFor('fax'))) {
                
            //Инстанция на doc_Faxes
            cls::get('doc_Faxes');

            //Ако имамем въведен домейн за факсове, тогава се създава бутона
            if (BGERP_FAX_DOMEIN) {
                $retUrl = array($mvc, 'single', $data->rec->id);
            
                // Бутон за отпечатване
                $data->toolbar->addBtn('Факс', array(
                        'doc_Faxes',
                        'add',
                        'originId' => $data->rec->containerId,
                        'ret_url'=>$retUrl
                    ),
                    'class=btn-fax');    
            }    
            
        }
    }
}