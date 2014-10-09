<?php



/**
 * Клас 'plg_PrevAndNext' - Добавя бутони за предишен и следващ във форма за редактиране
 * и при разглеждането на няколко записа
 *
 *
 * @category  ef
 * @package   plg
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @link
 */
class plg_PrevAndNext extends core_Plugin
{
    
    function on_AfterDescription($mvc)
    {
        $mvc->doWithSelected = arr::make($mvc->doWithSelected, TRUE);
        $mvc->doWithSelected['edit'] = 'Редактиране';
        if(cls::isSubclass($mvc, 'core_Master')) {
            $mvc->doWithSelected['browse'] = 'Преглед'; 
        }
    }

    /**
     * Промяна на бутоните
     *
     * @param core_Mvc $mvc
     * @param stdClass $data
     */
    function on_AfterPrepareRetUrl($mvc, $data)
    {   
        $selKey = static::getModeKey($mvc);

        if(Mode::is($selKey)) {
            $Cmd = Request::get('Cmd');
            
            if (isset($Cmd['save_n_prev'])) {
                $data->retUrl = array($mvc, 'edit', 'id' => $data->buttons->prevId, 'PrevAndNext' => 'on', 'ret_url' => getRetUrl());
            } elseif (isset($Cmd['save_n_next'])) {
                $data->retUrl = array($mvc, 'edit', 'id' => $data->buttons->nextId, 'PrevAndNext' => 'on', 'ret_url' => getRetUrl());
            }
        }
    }
    
    
    /**
     * Позволява преглед на няколко избрани записа на техните сингли
     */
    function on_BeforeAction(core_Manager $mvc, &$res, $action)
    {
        if ($action == 'browse') {
        	
        	$mvc->requireRightFor('browse');
        	
	        $selKey = static::getModeKey($mvc);
			$id = Request::get('id', 'int');
	        
	        if($sel = Request::get('Selected')) {
				$data = new stdClass();
	        	
	            // Превръщаме в масив, списъка с избраниуте id-та
	            $selArr = arr::make($sel);
	
	            // Записваме масива в сесията, под уникален за модела ключ
	            Mode::setPermanent($selKey, $selArr);
	            
	            // Зареждаме id-то на първия запис за редактиране
	            expect(ctype_digit($id = $selArr[0]));
	            
	        } elseif(Request::get('PrevAndNext')) {
				
	            // Изтриваме в сесията, ако има избрано множество записи 
	            Mode::setPermanent($selKey, NULL);
	            
	        }
        	
            if(!is_object($data)) {
                $data = new stdClass();
            }
	        expect($data->rec = $mvc->fetch($id));
	            
	        // Трябва да има $rec за това $id
		      if(!($data->rec)) { 
		            
		        // Имаме ли въобще права за единичен изглед?
		        $mvc->requireRightFor('single');
		    }
		        
	        $mvc->requireRightFor('single', $data->rec);
				
	        $data->buttons = new stdClass();
        	$data->buttons->prevId = $this->getNeighbour($mvc, $data->rec, -1);
        	$data->buttons->nextId = $this->getNeighbour($mvc, $data->rec, +1);
        		
	        // Подготвяме данните за единичния изглед
		    $mvc->prepareSingle($data);
		        
		    // Рендираме изгледа
		    $tpl = $mvc->renderSingle($data);
		        
		    // Опаковаме изгледа
		    $tpl = $mvc->renderWrapping($tpl, $data);
		        
		    $res = $tpl;
		        
        	return FALSE;
   		}
    }
    
    /**
     * Връща id на съседния запис в зависимост next/prev
     *
     * @param stdClass $data
     * @param string $dir
     */
    private function getNeighbour($mvc, $rec, $dir)
    { 
        $id = $rec->id;
        if(!$id) return;

        $selKey = static::getModeKey($mvc);
        $selArr = Mode::get($selKey);
		
        if(!count($selArr)) return;
        $selId = array_search($id, $selArr);
        if($selId === FALSE) return;

        $selNeighbourId = $selId + $dir;

        return $selArr[$selNeighbourId];
    }
 
    
    /**
     * Преди подготовката на формата
     *
     * @param core_Mvc $mvc
     * @param stdClass $res
     * @param stdClass $data
     */
    function on_BeforePrepareEditForm($mvc, $data)
    {
        if($sel = Request::get('Selected')) {

            // Превръщаме в масив, списъка с избраниуте id-та
            $selArr = arr::make($sel);
             
            // Зареждаме id-то на първия запис за редактиране
            expect(ctype_digit($id = $selArr[0]));
            
            Request::push(array('id' => $id));            
        } 
    }




    
    /**
     * Подготовка на формата
     *
     * @param core_Mvc $mvc
     * @param stdClass $res
     * @param stdClass $data
     */
    function on_AfterPrepareEditForm($mvc, $data)
    {
        $selKey = static::getModeKey($mvc);
        
        $Cmd = Request::get('Cmd');

        if($sel = Request::get('Selected')) {

            // Превръщаме в масив, списъка с избраниуте id-та
            $selArr = arr::make($sel);
			
            // Записваме масива в сесията, под уникален за модела ключ
            Mode::setPermanent($selKey, $selArr);
            
            // Зареждаме id-то на първия запис за редактиране
            expect(ctype_digit($id = $selArr[0]));
            
            // Извличаме записа
            expect($data->form->rec = $mvc->fetch($id));
            
            $mvc->requireRightFor('edit', $data->form->rec);

        } elseif( !($data->form->cmd == 'save_n_next' || $data->form->cmd == 'save_n_prev' || Request::get('PrevAndNext'))) {

            // Изтриваме в сесията, ако има избрано множество записи 
            Mode::setPermanent($selKey, NULL);
        }
		
        $data->buttons = new stdClass();
        $data->buttons->prevId = $this->getNeighbour($mvc, $data->form->rec, -1);
        $data->buttons->nextId = $this->getNeighbour($mvc, $data->form->rec, +1);
    }
    
    
    /**
     * Добавяне на бутони за 'Предишен' и 'Следващ'
     *
     * @param unknown_type $mvc
     * @param unknown_type $res
     * @param unknown_type $data
     */
    function on_AfterPrepareEditToolbar($mvc, &$res, $data)
    {
        $selKey = static::getModeKey($mvc);
        
        if(Mode::is($selKey)  ) {
            if (isset($data->buttons->nextId)) {
                $data->form->toolbar->addSbBtn('»»»', 'save_n_next', 'class=noicon fright,order=30');
            } else {
                $data->form->toolbar->addSbBtn('»»»', 'save_n_next', 'class=btn-disabled noicon fright,disabled,order=30');
            }
            
            if (isset($data->buttons->prevId)) {
                $data->form->toolbar->addSbBtn('«««', 'save_n_prev', 'class=noicon fright,order=30');
            } else {
                $data->form->toolbar->addSbBtn('«««', 'save_n_prev', 'class=btn-disabled noicon fright,disabled,order=30');
            }

            $data->form->setHidden('ret_url', Request::get('ret_url'));
        }
    }


	/**
     * След подготовка на тулбара на единичен изглед.
     * 
     * @param core_Mvc $mvc
     * @param stdClass $data
     */
    static function on_AfterPrepareSingleToolbar($mvc, &$data)
    {
     	$selKey = static::getModeKey($mvc);
        
        if(Mode::is($selKey) ) {
            if (isset($data->buttons->nextId)) {
                $data->toolbar->addBtn('»»»', array($mvc, 'browse', $data->buttons->nextId), 'class=noicon fright');
            } else {
                $data->toolbar->addBtn('»»»', array(), 'class=btn-disabled noicon fright,disabled');
            }
            
            if (isset($data->buttons->prevId)) {
                $data->toolbar->addBtn('«««', array($mvc, 'browse', $data->buttons->prevId), 'class=noicon fright', array('style' => 'margin-left:5px;'));
            } else {
                $data->toolbar->addBtn('«««', array(), 'class=btn-disabled noicon fright,disabled', array('style' => 'margin-left:5px;'));
            }
        }
    }
    
    
    /**
     * Връща ключа за кеша, който се определя от сесията и модела
     */
    static function getModeKey($mvc) 
    {
        return $mvc->className . '_PrevAndNext';
    }
    
    
	/**
     * Изпълнява се след подготовката на ролите, които могат да изпълняват това действие.
     *   
     * @param core_Mvc $mvc
     * @param string $requiredRoles
     * @param string $action
     * @param stdClass|NULL $rec
     * @param int|NULL $userId
     */
    function on_AfterGetRequiredRoles($mvc, &$requiredRoles, $action, $rec = NULL, $userId = NULL)
    {
        if ($action == 'browse' && $requiredRoles != 'no_one') {
            if(!$mvc->haveRightFor('single', $rec, $userId)) {
                 $requiredRoles = 'no_one';
            } else {
                 $requiredRoles = $mvc->getRequiredRoles('single', $rec);
            }
        }
    }
}