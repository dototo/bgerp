<?php



/**
 * Клас 'bgerp_Index' -
 *
 *
 * @category  bgerp
 * @package   bgerp
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class bgerp_Index extends core_Manager
{
    
    
    /**
     * Дефолт екшън след логване
     */
    function act_Default()
    {
        if(!cms_Content::fetch("#state = 'active'")) {
            
            requireRole('user');
            
            if(haveRole('powerUser')){
                
                return new Redirect(array('bgerp_Portal', 'Show'));
            } else {
                
                return new Redirect(array('colab_Profiles', 'Single'));
            }
        } else {
            
            return Request::forward(array('Ctr' => 'cms_Content', 'Act' => 'Show'));
        }
    }
    
    
    /**
     * Връща линк към подадения обект
     * 
     * @param integer $objId
     * 
     * @return core_ET
     */
    public static function getLinkForObject($objId)
    {
        
        return ht::createLink(get_called_class(), array());
    }
}