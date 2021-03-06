<?php 

/**
 * Декларации за съответствия
 *
 *
 * @category  bgerp
 * @package   dec
 * @author    Gabriela Petrova <gab4eto@gmail.com>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class dec_Declarations extends core_Master
{
    
    /**
     * Интерфейси, поддържани от този мениджър
     */
    var $interfaces = 'doc_DocumentIntf, email_DocumentIntf';
    
    
    /**
     * Флаг, който указва, че документа е партньорски
     */
    public $visibleForPartners = TRUE;
    
    
    /**
     * Заглавие
     */
    var $title = "Декларации за съответствие";
    
    
    /**
     * Заглавие в единствено число
     */
    var $singleTitle = "Декларация за съответствие";
    
    
    /**
     * Заглавие на менюто
     */
    var $pageMenu = "Декларации";
    
    
    /**
     * Плъгини за зареждане
     */
    var $loadList = 'sales_Wrapper, bgerp_plg_Blank, recently_Plugin, doc_ActivatePlg, plg_Printing, cond_plg_DefaultValues, 
    				 plg_RowTools2, doc_DocumentIntf, doc_DocumentPlg, doc_EmailCreatePlg ,doc_plg_TplManager';
    
    
    /**
     * Кой има право да чете?
     */
    var $canRead = 'ceo,dec';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	var $canList = 'ceo,dec';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	var $canSingle = 'ceo,dec';
    
    
    /**
     * Кой може да пише?
     */
    var $canWrite = 'ceo,dec';

    
    /**
     * Кои полета ще виждаме в листовия изглед
     */
    var $listFields = 'id, doc, createdOn, createdBy';
    
    
    /**
     * Кой е тетущият таб от менюто
     */
    var $currentTab = 'Декларации';
     
     
    /**
     * Шаблон за единичния изглед
     */
    var $singleLayoutFile = 'dec/tpl/SingleLayoutDeclarations.shtml';
    
    
    /**
     * В кой плейсхолдер да се сложи шаблона
     */
    var $templateFld = 'content';

    
    /**
     * Абревиатура
     */
    var $abbr = "Dec";
    
   	
   	/**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    var $searchFields = 'doc, declaratorName, id';
    
    
    /**
     * Стратегии за дефолт стойностти
     */
    public static $defaultStrategies = array(
    
    	'statements' => 'lastDocUser|lastDoc|LastDocSameCuntry',
        'materials' => 'lastDocUser|lastDoc|LastDocSameCuntry',
    );
    
    
    /**
     * Описание на модела
     */
    function description()
    {
    	// бланка
    	//$this->FLD('typeId', 'key(mvc=dec_DeclarationTypes,select=name)', "caption=Бланка");
    	
    	// номера на документа
    	$this->FLD('doc', 'key(mvc=doc_Containers)', 'caption=Към документ, input=none');
    	
    	// дата на декларацията
    	$this->FLD('date', 'date', 'caption=Дата');
    	
    	// декларатор
    	$this->FLD('declaratorName', 'varchar', 'caption=Представлявана от->Име, recently, mandatory');
    	
    	// позицията на декларатора
    	$this->FLD('declaratorPosition', 'varchar', 'caption=Представлявана от->Позиция, recently, mandatory');
        
    	// продукти, идват от фактурата
    	$this->FLD('productId', 'set', 'caption=Продукти->Продукти, maxColumns=2');
    
    	// на какви твърдения отговарят
		$this->FLD('statements', 'keylist(mvc=dec_Statements,select=title)', 'caption=Твърдения->Отговарят на, mandatory');
		
		// от какви материали е
		$this->FLD('materials', 'keylist(mvc=dec_Materials,select=title)', 'caption=Материали->Изработени от, mandatory');
        
		// допълнителен текст
		$this->FLD('note', 'richtext(bucket=Notes,rows=6)', 'caption=Бележки->Допълнения');

	}

    
     /**
     * След потготовка на формата за добавяне / редактиране.
     * 
     * @param core_Mvc $mvc
     * @param stdClass $data
     */
    static function on_AfterPrepareEditForm($mvc, $data)
    {

        // Записваме оригиналното ид, ако имаме такова
    	if($data->form->rec->originId){
    		$data->form->setDefault('doc', $data->form->rec->originId);
    		
    		// и е по  документ фактура намираме кой е той
    		$doc = doc_Containers::getDocument($data->form->rec->originId);
    		$class = $doc->className;
    		$dId = $doc->that;
    		$rec = $class::fetch($dId);
    		
    		// съдържа обобщена информация за сделките в нишката
    		$firstDoc = doc_Threads::getFirstDocument($data->form->rec->threadId);
    		if($firstDoc->haveInterface('bgerp_DealAggregatorIntf')){
    			$deal = $firstDoc->getAggregateDealInfo();
    		} elseif($firstDoc->haveInterface('bgerp_DealIntf')){
    			$deal = $firstDoc->getDealInfo();
    		}
    		expect($deal);

	       	// Продуктите
	       	$invoicedProducts = $deal->get('invoicedProducts');
	       	if (count($invoicedProducts)) {
	       		
		       	foreach($invoicedProducts as $iProduct){
		        	$productName [$iProduct->productId] = cat_Products::getTitleById($iProduct->productId);
				}
				
				$data->form->setSuggestions('productId', $productName);
	       	}
    	}
    	    	
    	// декларатор е текущия потребител
    	if (!$data->form->rec->declaratorName) {
    		
    		$personId = core_Users::getCurrent('id');
    		$personName = crm_Persons::fetchField($personId, 'name');
    		$data->form->setDefault('declaratorName', $personName);
		}
    	
    	// ако не е указана дата взимаме днешната
    	if (!$data->form->rec->date) {
    		
    		$data->form->setDefault('date', dt::now(FALSE));
    	}
    }
    
    
    /**
     * Извиква се след конвертирането на реда ($rec) към вербални стойности ($row)
     */
    function on_AfterRecToVerbal($mvc, $row, $rec)
    {
    	try{
        	$row->doc = doc_Containers::getLinkForSingle($rec->doc);
        } catch(core_exception_Expect $e){
        	$row->doc = tr("Проблем при показването");
        }
    }
   
    
    /**
     * Подготвя иконата за единичния изглед
     */
    static function on_AfterPrepareSingle($mvc, &$tpl, &$data)
    {
    	$row = &$data->row;
        $rec = &$data->rec;
        $recDec = $tpl->rec;

        // Зареждаме бланката в шаблона на документа
       // $row->content = new ET (dec_DeclarationTypes::fetchField($rec->typeId, 'script'));

        // взимаме съдържанието на бланката
        $decContent = doc_TplManager::getTemplate($data->rec->template);

    	// Зареждаме данните за собствената фирма
        $ownCompanyData = crm_Companies::fetchOwnCompany();

        // Адреса на фирмата
        $address = trim($ownCompanyData->place . ' ' . $ownCompanyData->pCode);
        if ($address && !empty($ownCompanyData->address)) {
            $address .= ', ' . $ownCompanyData->address;
        } 
        
        $Varchar = cls::get('type_Varchar');
        $row->MyCompany = crm_Companies::getTitleById($ownCompanyData->companyId);
        $row->MyCountry = $ownCompanyData->country;
        $row->MyAddress = $Varchar->toVerbal($address);

        // Ват номера й
        $uic = drdata_Vats::getUicByVatNo($ownCompanyData->vatNo);
        if ($uic != $ownCompanyData->vatNo){
        	$row->MyCompanyVatNo = ' ' . $ownCompanyData->vatNo;

    	} 
    	
    	if ($uic) {
    		$row->uicId = ' ' . $uic;
    	}
    	
    	// информация за управителя/декларатора
    	if ($recDec->declaratorName) {
    		$row->manager = $recDec->declaratorName;
    		
    		if ($declaratorData = crm_Persons::fetch("#name = '{$recDec->declaratorName}'")) {
	    		$dTpl =  $decContent->getBlock("manager");
	    		$dTpl->replace($declaratorData->egn, 'managerЕGN');
	    		$dTpl->append2master();
    		}
    		
    		$cTpl = $decContent->getBlock("declaratorInfo");
    		$cTpl->replace($recDec->declaratorName, 'declaratorName');
	    	$cTpl->replace($recDec->declaratorPosition, 'declaratorPosition');
	    	$cTpl->append2master();
    	}
    	
    	if($rec->date == NULL){
    		$row->date = $rec->createdOn;
    	} else {
    		if (core_Lg::getCurrent() == 'bg') {
    			$row->date = dt::mysql2verbal($rec->date, "d.m.Y") . tr("|г.|*");
    		} else {
    			$row->date = dt::mysql2verbal($rec->date, "d.m.Y");
    		}
    	}
    	    	
    	// вземаме избраните продукти
    	if ($recDec->productId) { 
    		
    		$products = arr::make($recDec->productId);
    		
    		foreach ($products as $product) {
    			$classProduct[$product] = explode("|", $product);
    		}

    		$row->products = "<ol>";
		       	foreach($classProduct as $iProduct=>$name){
		       		$pId = (isset($name[1])) ? $name[1] : $name[0];
		        	$productName = cat_Products::getTitleById($pId);
		        	$row->products .= "<li>".$productName."</li>";
			}
			$row->products .= "</ol>";
    	}

    	// ако декларацията е към документ
    	if ($rec->originId) {
			// и е по  документ фактура намираме кой е той
    		$doc = doc_Containers::getDocument($rec->originId);
    		
    		$class = $doc->className;
    		$dId = $doc->that;
    		$recOrigin = $class::fetch($dId);
    		    		
    		// Попълваме данните от контрагента. Идват от фактурата
    		$addressContragent = trim($recOrigin->contragentPlace . ' ' . $recOrigin->contragentPCode);
	        if ($addressContragent && !empty($recOrigin->contragentAddress)) {
	            $addressContragent .= ', ' . $recOrigin->contragentAddress;
	        }
	        $row->contragentCompany = cls::get($recOrigin->contragentClassId)->getTitleById($recOrigin->contragentId);
	        $row->contragentCountry = drdata_Countries::fetchField($recOrigin->contragentCountryId, 'commonNameBg');
	        $row->contragentAddress = $Varchar->toVerbal($addressContragent);
	        
	        $uicContragent = drdata_Vats::getUicByVatNo($rec->contragentVatNo);
	        if ($uic != $recOrigin->contragentVatNo) {
	        	$row->contragentCompanyVatNo = $Varchar->toVerbal($rec->contragentVatNo);
	    	} 
	    	$row->contragentUicId = $uicContragent;
    		
       		$invoiceNo = str_pad($recOrigin->number, '10', '0', STR_PAD_LEFT) . " / " . dt::mysql2verbal($recOrigin->date, "d.m.Y");
       		$row->invoiceNo = $invoiceNo;
         }

        // вземаме материалите
    	if ($recDec->materials) {
    		$materials = type_Keylist::toArray($recDec->materials);

            $row->material= "<ol>";
    		foreach ($materials as $material) {  
    			$m = dec_Materials::fetch($material);
    			
    			$text = $m->text;
    			$row->material .= "<li>".$text."</li>";
    		}  			
    		$row->statements .= "</ol>";
    	}

    	// вземаме твърденията
    	if ($recDec->statements) {
    		
    		$statements = type_Keylist::toArray($recDec->statements);
    		
            $row->statements = "<ol>";
    		foreach($statements as $statement){
    		    $s = dec_Statements::fetch($statement);
    		    $text = $s->text;
    		   $row->statements .= "<li>".$text."</li>";
    		}
    		$row->statements .= "</ol>";
    	}
    	
    	// ако има допълнителни бележки
    	if($recDec->note) {
    		$cTpl = $decContent->getBlock("note");
    		$Richtext = cls::get('type_Richtext');
    		$recDec->note = $Richtext->toVerbal($recDec->note);
    		$cTpl->replace($recDec->note, 'note');
    	    $cTpl->append2master();
    	}
    }

    
    /**
     * След проверка на ролите
     */
	public static function on_AfterGetRequiredRoles($mvc, &$requiredRoles, $action, $rec = NULL, $userId = NULL)
    {
    	switch ($action) { 
    		
            case 'activate':
                if (empty($rec->id)) {
                    // не се допуска активиране на незаписани декларации
                    $requiredRoles = 'no_one';
                } 
                break;
            case 'add':
            	if(empty($rec->originId)){
            		$requiredRoles = 'no_one';
            	} else {
            		$origin = doc_Containers::getDocument($rec->originId);
            		
            		if(!$origin->isInstanceOf('sales_Invoices')){
            			$requiredRoles = 'no_one';
            		} else {
            			$originRec = $origin->rec();
            			if($originRec->state != 'active' || $originRec->type != 'invoice'){
            				$requiredRoles = 'no_one';
            			}
            		}
            	}
    	}
    }
    
    
    /**
     * Добавя след таблицата
     *
     * @param core_Mvc $mvc
     * @param StdClass $res
     * @param StdClass $data
     */
    static function on_AfterRenderListTable($mvc, &$tpl, $data)
    {
    	$mvc->currentTab = "Декларации->Списък";
    	$mvc->menuPage = "Търговия:Продажби";
    }
    
    
    /****************************************************************************************
     *                                                                                      *
     *  ИМПЛЕМЕНТАЦИЯ НА @link doc_DocumentIntf                                             *
     *                                                                                      *
     ****************************************************************************************/
   
    
    /**
     * Интерфейсен метод на doc_DocumentInterface
     */
    function getDocumentRow($id)
    {

        $rec = $this->fetch($id);
        $row = new stdClass();
        
        $row->title    = $this->singleTitle . " №{$id}";
        $row->authorId = $rec->createdBy;
        $row->author   = $this->getVerbal($rec, 'createdBy');
        $row->recTitle = $row->title;
        $row->state    = $rec->state;
  
        return $row;
    }
    
    
	/**
     * Проверка дали нов документ може да бъде добавен в
     * посочената папка като начало на нишка
     *
     * @param $folderId int ид на папката
     */
    public static function canAddToFolder($folderId)
    {
        return FALSE;
    }
    
    
	/**
     * Извиква се след подготовката на toolbar-а за табличния изглед
     */
    static function on_AfterPrepareSingleToolbar($mvc, &$data)
    {

    	if(!empty($data->toolbar->buttons['btnAdd'])){
    		$data->toolbar->removeBtn('btnAdd');
    	}
    }
    
    
    /**
     * Връща тялото на имейла генериран от документа
     * 
     * @see email_DocumentIntf
     * @param int $id - ид на документа
     * @param boolean $forward
     * @return string - тялото на имейла
     */
    public function getDefaultEmailBody($id, $forward = FALSE)
    {
        $handle = $this->getHandle($id);
        $tpl = new ET(tr("Моля запознайте се с нашата декларация за съответствие") . ': #[#handle#]');
        $tpl->append($handle, 'handle');
        
        return $tpl->getContent();
    }

    
    /**
     * Извиква се след SetUp-а на таблицата за модела
     */
    function loadSetupData()
    {
    	$res = '';
    	$this->setTemplates($res);
    	 
    	return $res;
    }
    
    
    /**
     * Зарежда шаблоните на продажбата в doc_TplManager
     */
    protected function setTemplates(&$res)
    {
    	$tplArr = array();
    	$tplArr[] = array('name' => 'Декларация за съответствие',
    					  'content' => 'dec/tpl/AgreementDeclaration.shtml', 'lang' => 'bg',
    					  'toggleFields' => array('masterFld' => NULL));
    	$tplArr[] = array('name' => 'Declaration of compliance',
    					  'content' => 'dec/tpl/DeclarationOfCompliance.shtml', 'lang' => 'en',
    					  'toggleFields' => array('masterFld' => NULL));
    	$tplArr[] = array('name' => 'Приложение №1',
    					  'content' => 'dec/tpl/Application1.shtml', 'lang' => 'bg',
    					  'toggleFields' => array('masterFld' => NULL));
    	$tplArr[] = array('name' => 'Приложение №5',
    				  	  'content' => 'dec/tpl/Application5.shtml', 'lang' => 'bg',
    					  'toggleFields' => array('masterFld' => NULL));
    	$res .= doc_TplManager::addOnce($this, $tplArr);
    }

}
