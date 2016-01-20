<?php


/**
 * Клас 'planning_drivers_ProductionTaskProducts'
 *
 * Детайли на задачите за производство
 *
 * @category  bgerp
 * @package   planning
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class planning_drivers_ProductionTaskProducts extends tasks_TaskDetails
{
    

    /**
     * Заглавие в единствено число
     */
    public $singleTitle = 'Артикул';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'RowNumb=Пулт,type,productId,packagingId,planedQuantity=Количества->Планувано,realQuantity=Количества->Изпълнено,indTime=Изпълнение,totalTime';
    
    
    /**
     * Полето в което автоматично се показват иконките за редакция и изтриване на реда от таблицата
     */
    public $rowToolsField = 'RowNumb';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_RowTools, plg_RowNumbering, plg_AlignDecimals2, plg_SaveAndNew, plg_Modified, plg_Created';
    
    
    /**
     * Кой има право да оттегля?
     */
    public $canReject = 'planning,ceo';
    
    
    /**
     * Кой има право да възстановява?
     */
    public $canRestore = 'planning,ceo';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'planning,ceo';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'planning,ceo';
    
    
    /**
     * Кой може да го изтрие?
     */
    public $canDelete = 'planning,ceo';
    
    
    /**
     * Кой може да го изтрие?
     */
    public $canList = 'no_one';
    
   
    /**
     * Име на поле от модела, външен ключ към мастър записа
     */
    public $masterKey = 'taskId';
    
    
    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
    	$this->FLD("taskId", 'key(mvc=planning_Tasks)', 'input=hidden,silent,mandatory,caption=Задача');
    	$this->FLD("type", 'enum(input=Вложим,product=Производим,waste=Отпадък)', 'caption=Вид,remember,silent,input=none');
    	$this->FLD("productId", 'key(mvc=cat_Products,select=name,allowEmpty)', 'silent,mandatory,caption=Артикул,removeAndRefreshForm=packagingId');
    	$this->FLD("packagingId", 'key(mvc=cat_UoM,select=name)', 'mandatory,caption=Опаковка,smartCenter');
    	$this->FLD("planedQuantity", 'double', 'mandatory,caption=Планувано к-во');
    	$this->FLD("quantityInPack", 'int', 'mandatory,input=none');
    	$this->FLD("realQuantity", 'double', 'caption=Количество->Изпълнено,input=none,notNull');
    	$this->FLD("indTime", 'time', 'mandatory,caption=Време за изпълнение,smartCenter');
    	$this->FNC('totalTime', 'time', 'caption=Времена->Общо,smartCenter');
    	
    	$this->setDbUnique('taskId,productId');
    }
    
    
    /**
     * Общото време
     *
     * @param core_Mvc $mvc
     * @param stdClass $rec
     */
    public static function on_CalcTotalTime(core_Mvc $mvc, $rec)
    {
    	if (empty($rec->indTime) || empty($rec->realQuantity)) {
    		return;
    	}
    
    	$rec->totalTime = $rec->indTime * $rec->realQuantity;
    }
    
    
    /**
     * Преди показване на форма за добавяне/промяна.
     *
     * @param core_Manager $mvc
     * @param stdClass $data
     */
    public static function on_AfterPrepareEditForm($mvc, &$data)
    {
    	$form = &$data->form;
    	$rec = &$form->rec;
    	
    	$form->setDefault('type', 'input');
    	
    	if(isset($rec->type)){
    		switch($rec->type){
    			case 'input':
    				$meta = 'canConvert';
    				break;
    			case 'product':
    				$meta = 'canManifacture';
    				break;
    			case 'waste':
    				$meta = 'canStore';
    				break;
    		}
    		
    		$products = cat_Products::getByProperty($meta);
    		if($rec->productId){
    			$products[$rec->productId] = cat_Products::getTitleById($rec->productId, FALSE);
    		}
    		$form->setOptions('productId', $products);
    	}
    	
    	if(isset($rec->productId)){
    		$packs = cat_Products::getPacks($rec->productId);
    		$form->setOptions('packagingId', $packs);
    	} else {
    		$form->setReadOnly('packagingId');
    	}
    }
    
    
    /**
     * Извиква се след въвеждането на данните от Request във формата ($form->rec)
     *
     * @param core_Mvc $mvc
     * @param core_Form $form
     */
    public static function on_AfterInputEditForm($mvc, &$form)
    {
    	$rec = &$form->rec;
    	
    	if($form->isSubmitted()){
    		$pInfo = cat_Products::getProductInfo($rec->productId);
    		$rec->quantityInPack = ($pInfo->packagings[$rec->packagingId]) ? $pInfo->packagings[$rec->packagingId]->quantity : 1;
    	}
    }
    
    
    /**
     * Подготвя детайла
     */
    public function prepareDetail_($data)
    {
    	$data->TabCaption = 'Артикули';
    	$data->Tab = 'top';
    
    	parent::prepareDetail_($data);
    }
    
    
    /**
     * След преобразуване на записа в четим за хора вид.
     */
    protected static function on_AfterPrepareListRows($mvc, &$data)
    {
    	if(!count($data->recs)) return;
    	
    	foreach ($data->rows as $id => $row){
    		$rec = $data->recs[$id];
    		$class = ($rec->type == 'input') ? 'row-added' : (($rec->type == 'product') ? 'state-active' : 'row-removed');
    		
    		$row->ROW_ATTR['class'] = $class;
    		$row->productId = cat_Products::getShortHyperlink($rec->productId);
    	}
    }
    
    
    /**
     * Изпълнява се след подготовката на ролите, които могат да изпълняват това действие
     */
    public static function on_AfterGetRequiredRoles($mvc, &$requiredRoles, $action, $rec = NULL, $userId = NULL)
    {
    	if(($action == 'add' || $action == 'reject' || $action == 'restore' || $action == 'edit' || $action == 'delete') && isset($rec->taskId)){
    		$state = $mvc->Master->fetchField($rec->taskId, 'state');
    		if($state != 'draft'){
    			$requiredRoles = 'no_one';
    		}
    	}
    }
    
    
    /**
     * Ъпдейтва реалното количество
     * 
     * @param int $taskProductId - ид на ред за ъпдейт
     * @return void
     */
    public static function updateRealQuantity($taskProductId)
    {
    	$rec = self::fetch($taskProductId);
    	$rec->realQuantity = 0;
    	
    	$query = planning_drivers_ProductionTaskDetails::getQuery();
    	$query->where("#taskId = {$rec->taskId}");
    	$query->where("#type = '{$rec->type}'");
    	$query->show('quantity');
    	
    	while($dRec = $query->fetch()){
    		$rec->realQuantity += $dRec->quantity;
    	}
    	
    	self::save($rec, 'realQuantity');
    }
    
    
    /**
     * Намира всички допустими артикули от дадения тип за една задача
     * 
     * @param int $taskId
     * @param input|product|waste $type
     * @return array
     */
    public static function getOptionsByType($taskId, $type)
    {
    	$options = array();
    	expect(in_array($type, array('input', 'product', 'waste')));
    	
    	$query = self::getQuery();
    	$query->where("#taskId = {$taskId}");
    	$query->where("#type = '{$type}'");
    	while($rec = $query->fetch()){
    		$options[$rec->id] = cat_Products::getTitleById($rec->productId, FALSE);
    	}
    	
    	return $options;
    }
    
    
    /**
     * Изпълнява се след създаване на нов запис
     */
    public static function on_AfterCreate($mvc, $rec)
    {
    	// При добавянето на артикул за влагане/отпадък ако за него има чернова задача за произвеждането му
    	// искаме текущата задача да зависи от изпълнението на другата задача.Т.е да активираме задачата
    	// за влагането на артикула само след завършването на задачата за произвеждането му
    	
    	// Ако добавяме артикул за произвеждане не правим нищо
    	if($rec->type == 'product') return;
    	
    	// Коя е задачата
    	$taskRec = planning_Tasks::fetch($rec->taskId);
    	
    	// Ако задачата няма източник няма от къде да зареждаме
    	if(!isset($taskRec->originId)) return;
    	
    	// Търсим дали има друга чернова задача за произвеждането на артикула, който влагаме/отпадък
    	$tQuery = planning_drivers_ProductionTaskProducts::getQuery();
    	$tQuery->where("#type = 'product' AND #productId = {$rec->productId}");
    	$tQuery->EXT('state', 'planning_Tasks', 'externalName=state,externalKey=taskId');
    	$tQuery->EXT('originId', 'planning_Tasks', 'externalName=originId,externalKey=taskId');
    	
    	$tQuery->where("#originId = {$taskRec->originId}");
    	$tQuery->where("#state = 'draft'");
    	$tQuery->where("#taskId != '{$taskRec->id}'");
    	$tQuery->show('taskId');
    	
    	// За всяка от намерените задачи
    	while($tRec = $tQuery->fetch()){
    		try{
    			// Добавяме текущата задача да зависи от нея.
    			tasks_TaskConditions::add($taskRec, $tRec->taskId);
    		} catch(core_exception_Expect $e){
    			
    		}
    	}
    }
    
    
    /**
     * Извиква се след подготовката на toolbar-а за табличния изглед
     */
    protected static function on_AfterPrepareListToolbar($mvc, &$data)
    {
    	// Документа не може да се създава  в нова нишка, ако е възоснова на друг
    	if(!empty($data->toolbar->buttons['btnAdd'])){
    		$data->toolbar->removeBtn('btnAdd');
    		
    		if(cat_Products::getByProperty('canManifacture', NULL, 1)){
    			if($mvc->haveRightFor('add', (object)array('taskId' => $data->masterId, 'type' => 'product'))){
    				$data->toolbar->addBtn('Производими', array($mvc, 'add', 'taskId' => $data->masterId, 'type' => 'product', 'ret_url' => TRUE), FALSE, 'ef_icon = img/16/package.png,title=Добавяне на произведен артикул');
    			}
    		}
    		
    		if(cat_Products::getByProperty('canConvert', NULL, 1)){
    			if($mvc->haveRightFor('add', (object)array('taskId' => $data->masterId, 'type' => 'input'))){
    				$data->toolbar->addBtn('Вложими', array($mvc, 'add', 'taskId' => $data->masterId, 'type' => 'input', 'ret_url' => TRUE), FALSE, 'ef_icon = img/16/package.png,title=Добавяне на произведен артикул');
    			}
    		}
    		
    		if(cat_Products::getByProperty('canStore', NULL, 1)){
    			if($mvc->haveRightFor('add', (object)array('taskId' => $data->masterId, 'type' => 'waste'))){
    				$data->toolbar->addBtn('Отпадъци', array($mvc, 'add', 'taskId' => $data->masterId, 'type' => 'waste', 'ret_url' => TRUE), FALSE, 'ef_icon = img/16/package.png,title=Добавяне на отпаден артикул');
    			}
    		}
    	}
    	 
    	$data->toolbar->removeBtn('binBtn');
    }
    
    
    /**
     * Преди подготовка на заглавието на формата
     */
    protected static function on_BeforePrepareEditTitle($mvc, &$res, $data)
    {
    	$rec = &$data->form->rec;
    	$data->singleTitle = ($rec->type == 'input') ? 'артикул за влагане' : (($rec->type == 'waste') ? 'отпадъчен артикул' : 'артикул за произвеждане');
    }
}