<?php



/**
 * Документ за Разходни касови ордери
 *
 *
 * @category  bgerp
 * @package   cash
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class cash_Rko extends core_Master
{
    
    
    /**
     * Какви интерфейси поддържа този мениджър
     */
    var $interfaces = 'doc_DocumentIntf, acc_TransactionSourceIntf, sales_PaymentIntf, bgerp_DealIntf, email_DocumentIntf, doc_ContragentDataIntf';
    
    
    /**
     * Заглавие на мениджъра
     */
    var $title = "Разходни касови ордери";
    
    
    /**
     * Неща, подлежащи на начално зареждане
     */
    var $loadList = 'plg_RowTools, cash_Wrapper, plg_Sorting, doc_plg_BusinessDoc,acc_plg_Contable,
                     doc_DocumentPlg, plg_Printing, doc_SequencerPlg, acc_plg_DocumentSummary,
                     plg_Search,doc_plg_MultiPrint, bgerp_plg_Blank, doc_EmailCreatePlg, cond_plg_DefaultValues';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    var $listFields = "number, valior, reason, folderId, currencyId=Валута, amount, state, createdOn, createdBy";
    
    
    /**
     * Полето в което автоматично се показват иконките за редакция и изтриване на реда от таблицата
     */
    var $rowToolsField = 'tools';
    
    
    /**
     * Хипервръзка на даденото поле и поставяне на икона за индивидуален изглед пред него
     */
    var $rowToolsSingleField = 'reason';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	var $canList = 'ceo,cash';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	var $canSingle = 'ceo,cash';
    
	
    /**
     * Заглавие на единичен документ
     */
    var $singleTitle = 'Разходен касов ордер';
    
    
    /**
     * Икона на единичния изглед
     */
    var $singleIcon = 'img/16/money_delete.png';
    
    
    /**
     * Абревиатура
     */
    var $abbr = "Rko";
    
    
    /**
     * Кой има право да чете?
     */
    var $canRead = 'cash, ceo';
    
    
    /**
     * Кой може да пише?
     */
    var $canWrite = 'cash, ceo';
    
    
    /**
     * Кой може да го контира?
     */
    var $canConto = 'cash, ceo';
    
    
    /**
     * Кой може да оттегля
     */
    var $canRevert = 'cash, ceo';
    
    
    /**
     * Кой може да го отхвърли?
     */
    var $canReject = 'cash, ceo';
    
    
    /**
     * Файл с шаблон за единичен изглед на статия
     */
    var $singleLayoutFile = 'cash/tpl/Rko.shtml';
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    var $searchFields = 'number, valior, contragentName';
    
    
    /**
     * Параметри за принтиране
     */
    var $printParams = array( array('Оригинал'), array('Копие')); 
    
    
    /**
     * Групиране на документите
     */
    var $newBtnGroup = "4.2|Финанси";
    
    
    /**
     * Стратегии за дефолт стойностти
     */
    public static $defaultStrategies = array(
    	'currencyId'     => 'lastDocUser|lastDoc',
    	'beneficiary'    => 'lastDocUser|lastDoc',
    );
    
    
    /**
     * Описание на модела
     */
    function description()
    {
    	$this->FLD('operationSysId', 'customKey(mvc=acc_Operations,key=systemId, select=name)', 'caption=Операция,width=100%,mandatory');
    	$this->FLD('amount', 'double(decimals=2,max=2000000000,min=0)', 'caption=Сума,mandatory,width=30%,summary=amount');
    	$this->FLD('reason', 'richtext(rows=2)', 'caption=Основание,width=100%,mandatory');
    	$this->FLD('valior', 'date(format=d.m.Y)', 'caption=Вальор,mandatory,width=30%');
    	$this->FLD('number', 'int', 'caption=Номер,width=50%,width=30%');
    	$this->FLD('peroCase', 'key(mvc=cash_Cases, select=name)', 'caption=Каса');
    	$this->FLD('contragentName', 'varchar(255)', 'caption=Контрагент->Получател,mandatory,width=100%');
    	$this->FLD('contragentId', 'int', 'input=hidden,notNull');
    	$this->FLD('contragentClassId', 'key(mvc=core_Classes,select=name)', 'input=hidden,notNull');
    	$this->FLD('contragentAdress', 'varchar(255)', 'input=hidden');
        $this->FLD('contragentPlace', 'varchar(255)', 'input=hidden');
        $this->FLD('contragentPcode', 'varchar(255)', 'input=hidden');
        $this->FLD('contragentCountry', 'varchar(255)', 'input=hidden');
    	$this->FLD('beneficiary', 'varchar(255)', 'caption=Контрагент->Получил,mandatory');
    	$this->FLD('creditAccount', 'acc_type_Account()', 'input=none');
    	$this->FLD('debitAccount', 'acc_type_Account()', 'input=none');
    	$this->FLD('currencyId', 'key(mvc=currency_Currencies, select=code)', 'caption=Валута->Код,width=6em');
    	$this->FLD('rate', 'double', 'caption=Валута->Курс,width=6em');
    	$this->FNC('tempRate', 'double', 'caption=Валута->Курс,width=6em');
    	$this->FLD('notes', 'richtext(bucket=Notes, rows=6)', 'caption=Допълнително->Бележки');
    	$this->FLD('state', 
            'enum(draft=Чернова, active=Контиран, rejected=Сторнирана)', 
            'caption=Статус, input=none'
        );
    	 
        // Поставяне на уникален индекс
    	$this->setDbUnique('number');
    }
    
    
    /**
	 *  Подготовка на филтър формата
	 */
	static function on_AfterPrepareListFilter($mvc, $data)
	{
		// Добавяме към формата за търсене търсене по Каса
		cash_Cases::prepareCaseFilter($data, array('peroCase'));
	}
	
	
    /**
     *  Обработка на формата за редакция и добавяне
     */
    static function on_AfterPrepareEditForm($mvc, $res, $data)
    {
    	$folderId = $data->form->rec->folderId;
    	$form = &$data->form;
    	
    	$contragentId = doc_Folders::fetchCoverId($form->rec->folderId);
        $contragentClassId = doc_Folders::fetchField($form->rec->folderId, 'coverClass');
    	$form->setDefault('contragentId', $contragentId);
        $form->setDefault('contragentClassId', $contragentClassId);
        
        $options = acc_Operations::getPossibleOperations(get_called_class());
        $options = acc_Operations::filter($options, $contragentClassId);
        
    	// Използваме помощната функция за намиране името на контрагента
    	if(empty($form->rec->id) && $origin = $mvc->getOrigin($form->rec)) {
    		 $form->setDefault('reason', "Към документ #{$origin->getHandle()}");
    		 if($origin->haveInterface('bgerp_DealAggregatorIntf')){
    		 	$dealInfo = $origin->getAggregateDealInfo();
    		 	$amount = ($dealInfo->shipped->amount - $dealInfo->paid->amount) / $dealInfo->shipped->rate;
    		 	if($amount <= 0) {
    		 		$amount = 0;
    		 	}
    		 	
    		 	$defaultOperation = $mvc->getDefaultOperation($dealInfo);
    		 	if($defaultOperation == 'case2supplierAdvance'){
    		 		$amount = $dealInfo->agreed->downpayment / $dealInfo->agreed->rate;
    		 	}
    		 	
    		 	// Ако операциите на документа не са позволени от интерфейса, те се махат
    		 	foreach ($options as $index => $op){
    		 		if(!in_array($index, $dealInfo->allowedPaymentOperations)){
    		 			unset($options[$index]);
    		 		}
    		 	}
    		 	
    		 	if($caseId = $dealInfo->agreed->payment->caseId){
    		 		$cashRec = cash_Cases::fetch($caseId);
	    		 	
    		 		// Ако потребителя има права, логва се тихо
    		 		cash_Cases::selectSilent($caseId);
    		 	}
    		 	
    		 	$form->rec->currencyId = currency_Currencies::getIdByCode($dealInfo->shipped->currency);
    		 	$form->rec->tempRate = $dealInfo->shipped->rate;
    		 	
    		 	if($dealInfo->dealType != bgerp_iface_DealResponse::TYPE_SALE){
    		 		$form->rec->amount = currency_Currencies::round($amount, $dealInfo->shipped->currency);
    		 	}
    		 }
    	}
    	
    	// Поставяме стойности по подразбиране
    	$form->setDefault('valior', dt::today());
    	
    	if($contragentClassId == crm_Companies::getClassId()){
    		$form->setSuggestions('beneficiary', crm_Companies::getPersonOptions($contragentId, FALSE));
    	}
        
        $form->setOptions('operationSysId', $options);
    	if(isset($defaultOperation) && array_key_exists($defaultOperation, $options)){
    		$form->rec->operationSysId = $defaultOperation;	
        }
        $form->setReadOnly('peroCase', cash_Cases::getCurrent());
        $form->setReadOnly('contragentName', cls::get($contragentClassId)->getTitleById($contragentId));
        
        $form->addAttr('currencyId', array('onchange' => "document.forms['{$data->form->formAttr['id']}'].elements['tempRate'].value ='';"));
    }
    
    
    /**
     * Помощна ф-я връщаща дефолт операцията за документа
     */
    private function getDefaultOperation(bgerp_iface_DealResponse $dealInfo)
    {
    	$paid = $dealInfo->paid;
    	$agreed = $dealInfo->agreed;
    	
    	// Ако е продажба пораждащия документ
    	if($dealInfo->dealType == bgerp_iface_DealResponse::TYPE_SALE){
    		if(isset($agreed->downpayment)){
    			$defaultOperation = (trim($paid->downpayment) < trim($agreed->downpayment)) ? 'caseAdvance2customer' : 'case2customer';
    		} else {
    			$defaultOperation = 'case2customer';
    		}
    	} else {
    		if(isset($agreed->downpayment)){
    			$defaultOperation = (trim($paid->downpayment) < trim($agreed->downpayment)) ? 'case2supplierAdvance' : 'case2supplier';
    		} else {
    			$defaultOperation = 'case2supplier';
    		}
    	}
    	
    	return $defaultOperation;	
    }
    
    
    /**
     * Проверка и валидиране на формата
     */
    function on_AfterInputEditForm($mvc, $form)
    {
        if ($form->isSubmitted()){
        	
        	$rec = &$form->rec;
        	
        	// Коя е дебитната и кредитната сметка
	        $operation = acc_Operations::fetchBySysId($rec->operationSysId);
    		$rec->debitAccount = $operation->debitAccount;
    		$rec->creditAccount = $operation->creditAccount;
    		
	    	$rec->contragentClassId = doc_Folders::fetchField($rec->folderId, 'coverClass');
	        $rec->contragentId = doc_Folders::fetchCoverId($rec->folderId);
	    	$contragentData = doc_Folders::getContragentData($rec->folderId);
	    	$rec->contragentCountry = $contragentData->country;
	    	$rec->contragentPcode = $contragentData->pCode;
	    	$rec->contragentPlace = $contragentData->place;
	    	$rec->contragentAdress = $contragentData->adress;
	    	
	    	$currencyCode = currency_Currencies::getCodeById($rec->currencyId);
	    	
        	if(!$rec->rate){
		    	if($rec->tempRate){
		    		$rec->rate = $rec->tempRate;
		    	} else {
		    		// Изчисляваме курса към основната валута ако не е дефиниран
		    		$rec->rate = round(currency_CurrencyRates::getRate($rec->valior, $currencyCode, NULL), 4);
		    	}
		    } else {
		    	if($msg = currency_CurrencyRates::hasDeviation($rec->rate, $rec->valior, $currencyCode, NULL)){
		    		$form->setWarning('rate', $msg);
		    	}
		    }
    	}
    	
    	acc_Periods::checkDocumentDate($form, 'valior');
    }
    
    
    /**
     *  Обработки по вербалното представяне на данните
     */
    static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
    	$row->number = static::getHandle($rec->id);
    	if($fields['-list']){
    		$row->folderId = doc_Folders::recToVerbal(doc_Folders::fetch($rec->folderId))->title;
    	}	
    	
    	if($fields['-single']){
    		
    		// Адреса на контрагента
    		$row->contragentAddress = trim(
                sprintf("<br>%s<br>%s %s<br> %s", 
                 	$row->contragentCountry,
                    $row->contragentPcode,
                    $row->contragentPlace,
                    $row->contragentAdress
                )
            );
    	   
    		if($rec->rate != 1) {
            	$double = cls::get('type_Double');
            	$double->params['decimals'] = 0;
		   		$rec->equals = round($rec->amount * $rec->rate, 2);
		   		$row->equals = $double->toVerbal($rec->equals);
		   		$row->baseCurrency = acc_Periods::getBaseCurrencyCode($rec->valior);
		    } 
		    
            if(!$rec->equals) {
	    		
	    		//не показваме курса ако валутата на документа съвпада с тази на периода
	    		unset($row->rate);
	    		unset($row->baseCurrency);
	    	}
           
	    	$spellNumber = cls::get('core_SpellNumber');
		    $amountVerbal = $spellNumber->asCurrency($rec->amount, 'bg', FALSE);
		    $row->amountVerbal = $amountVerbal;
		    	
    		// Вземаме данните за нашата фирма
        	$ownCompanyData = crm_Companies::fetchOwnCompany();
        	$Companies = cls::get('crm_Companies');
        	$row->organisation = $Companies->getTitleById($ownCompanyData->companyId);
        	$row->organisationAddress = $Companies->getFullAdress($ownCompanyData->companyId);
            
    		// Извличаме имената на създателя на документа (касиера)
    		$cashierRec = core_Users::fetch($rec->createdBy);
    		$cashierRow = core_Users::recToVerbal($cashierRec);
	    	$row->cashier = $cashierRow->names;
	    	
        }
       
        // Показваме заглавието само ако не сме в режим принтиране
    	if(!Mode::is('printing')){
    		$row->header = $mvc->singleTitle . "&nbsp;&nbsp;<b>{$row->ident}</b>" . " ({$row->state})" ;
    	}
    }
    
    
    /**
     * Вкарваме css файл за единичния изглед
     */
	static function on_AfterRenderSingle($mvc, &$tpl, $data)
    {
    	$tpl->push('cash/tpl/styles.css', 'CSS');
    }
    
    
   	/**
   	 *  Имплементиране на интерфейсен метод (@see acc_TransactionSourceIntf)
   	 *  Създава транзакция която се записва в Журнала, при контирането
   	 */
    public static function getTransaction($id)
    {
       	// Извличаме записа
        expect($rec = self::fetchRec($id));
        
        // Подготвяме информацията която ще записваме в Журнала
        $result = (object)array(
            'reason'  => $rec->reason,   // основанието за ордера
            'valior'  => $rec->valior,   // датата на ордера
            'entries' => array(
                array(
                    'amount' => $rec->rate * $rec->amount,	// равностойноста на сумата в основната валута
                    
                    'debit' => array(
                        $rec->debitAccount, // дебитната сметка
                            array($rec->contragentClassId, $rec->contragentId),  // перо контрагент
                            array('currency_Currencies', $rec->currencyId),      // перо валута
                        'quantity' => $rec->amount,
                    ),
                    
                    'credit' => array(
                        $rec->creditAccount, // кредитна сметка
                            array('cash_Cases', $rec->peroCase), // перо каса
                            array('currency_Currencies', $rec->currencyId), // перо валута
                        'quantity' => $rec->amount,
                    ),
                ),
            )
        );
        
        // Ако дебитната сметка не поддържа втора номенклатура, премахваме
        // от масива второто перо на кредитната сметка
    	$dAcc = acc_Accounts::getRecBySystemId($rec->debitAccount);
    	
        if(!$dAcc->groupId2){
        	unset($result->entries[0]['debit'][2]);
        }
        
        return $result;
    }
    
	
	/**
     * @param int $id
     * @return stdClass
     * @see acc_TransactionSourceIntf::getTransaction
     */
    public function finalizeTransaction($id)
    {
        $rec = self::fetchRec($id);
        $rec->state = 'active';
        
    	if ($this->save($rec)) {
            // Нотифицираме origin-документа, че някой от веригата му се е променил
            if ($origin = $this->getOrigin($rec)) {
                $ref = new core_ObjectReference($this, $rec);
                $origin->getInstance()->invoke('DescendantChanged', array($origin, $ref));
            }
        }
    }
    
    
   	/*
     * Реализация на интерфейса doc_DocumentIntf
     */
    
    
 	/**
     * Имплементиране на интерфейсен метод (@see doc_DocumentIntf)
     */
    function getDocumentRow($id)
    {
    	$rec = $this->fetch($id);
        $row = new stdClass();
        $row->title = $this->singleTitle . " №{$id}";
        $row->authorId = $rec->createdBy;
        $row->author = $this->getVerbal($rec, 'createdBy');
        $row->state = $rec->state;
		$row->recTitle = $rec->reason;
		
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
        // Можем да добавяме или ако корицата е контрагент или сме в папката на текущата каса
       $cover = doc_Folders::getCover($folderId);
       
       return $cover->haveInterface('doc_ContragentDataIntf') || 
           ($cover->className == 'cash_Cases' && 
            $cover->that == cash_Cases::getCurrent('id', FALSE) );
    }
    
    
    /**
     * Имплементиране на интерфейсен метод (@see doc_DocumentIntf)
     */
    public static function getHandle($id)
    {
    	$rec = static::fetch($id);
    	$self = cls::get(get_called_class());
    	
    	return $self->abbr . $rec->number;
    }
    
    
	/**
     * Проверка дали нов документ може да бъде добавен в
     * посочената нишка
     * 
     * @param int $threadId key(mvc=doc_Threads)
     * @return boolean
     */
	public static function canAddToThread($threadId)
    {
    	$threadRec = doc_Threads::fetch($threadId);
    	$coverClass = doc_Folders::fetchCoverClassName($threadRec->folderId);
    	
    	$firstDoc = doc_Threads::getFirstDocument($threadId);
    	$docState = $firstDoc->fetchField('state');
    	
    	$res = cls::haveInterface('doc_ContragentDataIntf', $coverClass);
    	if($res){
    		if(($firstDoc->haveInterface('bgerp_DealAggregatorIntf') && $docState != 'active')){
    			$res = FALSE;
    		}
    	}
		
    	return $res;
    }
    
    
    /**
     * Имплементиране на интерфейсен метод (@see doc_DocumentIntf)
     */
    public static function fetchByHandle($parsedHandle)
    {
        return static::fetch("#number = '{$parsedHandle['id']}'");
    } 
    
    
	/**
     * В кои корици може да се вкарва документа
     * @return array - интерфейси, които трябва да имат кориците
     */
    public static function getAllowedFolders()
    {
    	return array('doc_ContragentDataIntf');
    }
    
    
	/**
     * Имплементация на @link bgerp_DealIntf::getDealInfo()
     */
    public function getDealInfo($id)
    {
        $rec = self::fetchRec($id);
    
        /* @var $result bgerp_iface_DealResponse */
        $result = new bgerp_iface_DealResponse();
    	
        // При продажба платеното се намалява, ако е покупка се увеличава
        $origin = static::getOrigin($rec);
        $sign = ($origin->className == 'purchase_Purchases') ? 1 : -1;
    
        $result->paid->amount          = $sign * $rec->amount * $rec->rate;
        $result->paid->currency        = currency_Currencies::getCodeById($rec->currencyId);
        $result->paid->rate 	       = $rec->rate;
        $result->paid->payment->caseId = $rec->peroCase;
    	$result->paid->operationSysId  = $rec->operationSysId;
        
        if($rec->operationSysId == 'case2supplierAdvance' || $rec->operationSysId == 'caseAdvance2customer'){
    		$result->paid->downpayment = $result->paid->amount;
    		$result->paid->downpayments[$rec->currencyId] = array('amount' => $sign * $rec->amount, 
    															  'amountBase' => $result->paid->amount);
        }
    	
        return $result;
    }
    
    
	/**
     * Информация за платежен документ
     * 
     * @param int|stdClass $id ключ (int) или запис (stdClass) на модел 
     * @return stdClass Обект със следните полета:
     *
     *   o amount       - обща сума на платежния документ във валутата, зададена от `currencyCode`
     *   o currencyCode - key(mvc=currency_Currencies, key=code): ISO код на валутата
     *   o currencyRate - double - валутен курс към основната (към датата на док.) валута
     *   o valior       - date - вальор на документа
     */
    public static function getPaymentInfo($id)
    {
        $rec = self::fetchRec($id);
        
        return (object)array(
            'amount'       => -$rec->amount,
            'currencyCode' => currency_Currencies::getCodeById($rec->currencyId),
        	'currencyRate' => $rec->rate,
            'valior'       => $rec->valior,
        );
    }
    
    
	/**
     * Интерфейсен метод на doc_ContragentDataIntf
     * Връща тялото на имейл по подразбиране
     */
    static function getDefaultEmailBody($id)
    {
        $handle = static::getHandle($id);
        $tpl = new ET(tr("Моля запознайте се с нашия разходен касов ордер") . ': #[#handle#]');
        $tpl->append($handle, 'handle');
        return $tpl->getContent();
    }
    
    
	/**
     * Извиква се след изчисляването на необходимите роли за това действие
     */
    function on_AfterGetRequiredRoles($mvc, &$res, $action, $rec = NULL, $userId = NULL)
    {
        // Ако резултата е 'no_one' пропускане
    	if($res == 'no_one') return;
    	
    	// Документа не може да се контира, ако ориджина му е в състояние 'closed'
    	if($action == 'conto' && isset($rec)){
	    	$origin = $mvc->getOrigin($rec);
	    	if($origin && $origin->haveInterface('bgerp_DealAggregatorIntf')){
	    		$originState = $origin->fetchField('state');
		    	if($originState === 'closed'){
		        	$res = 'no_one';
		        }
	    	}
        }
    }
    
    
	/**
     * Подготовка на бутоните на формата за добавяне/редактиране
     */
    function on_AfterPrepareEditToolbar($mvc, &$res, $data)
    {
    	// Документа неможе да се създава  в нова нишка, ако е възоснова на друг
    	if(!empty($data->form->toolbar->buttons['btnNewThread'])){
    		$data->form->toolbar->removeBtn('btnNewThread');
    	}
    }
}