<?php




/**
 * Клас  'type_Date' - Тип за дати
 *
 *
 * @category  ef
 * @package   type
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @link
 */
class type_Date extends core_Type {
    
    
    /**
     * MySQL тип на полето в базата данни
     */
    var $dbFieldType = 'date';
    
    
    /**
     * Клас за <td> елемент, който показва данни от този тип
     */
    var $tdClass = 'centerCol';
    
    
    /**
     * Формат на времевата част
     */
    var $timePart = '';
    
    
    /**
     * Преобразуване от вътрешно представяне към вербална стойност
     */
    function toVerbal($value, $useFormat = TRUE)
    {
    	$conf = core_Packs::getConfig('core');
    	
        if(empty($value)) return NULL;
        
        if($this->params['format'] && !Mode::is('printing') && (Mode::is('text', 'html') || !Mode::is('text')) && $useFormat) {
            $format = $this->params['format'];
        } elseif(Mode::is('screenMode', 'narrow')) {
            $format = $conf->EF_DATE_NARROW_FORMAT . $this->timePart;
        } else {
            $format = $conf->EF_DATE_FORMAT . $this->timePart;
        }
        
        $date = dt::mysql2verbal($value, $format);
        
        return $date;
    }
    
    
    /**
     * Конвертира от вербална стойност
     */
    function fromVerbal($value)
    { 
        if(is_array($value) && isset($value['d'])) {
            $value = $value['d'];
        }

        $value = trim($value);
        
        if(empty($value)) return NULL;
        
        $value = dt::verbal2mysql($value, !empty($this->timePart));
        
        if($value) {
            
            return $value;
        } else {
            $this->error = "Не е в допустимите формати, като например|*: '<B>" . dt::mysql2verbal(NULL, 'd-m-Y', NULL, FALSE) . "</B>'";
            
            return FALSE;
        }
    }
    
    
    /**
     * Рендира HTML инпут поле
     */
    function renderInput_($name, $value = "", &$attr = array())
    {
        $attr['name'] = $name;
               
        if($value && !$this->error) {
            $value = dt::mysql2verbal($value, 'd.m.Y', NULL, FALSE);
        } else {
            $value = $attr['value'];
        }

        setIfNot($attr['type'], 'text');
        setIfNot($this->params['width'], 8);

        $attr['style'] .= ';width:' . $this->params['width'] . 'em;';
        
        return $this->createInput($name, $value, $attr);
    }
}
