<?php



/**
 * Документ за наследяване от касовите ордери
 *
 *
 * @category  bgerp
 * @package   cash
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
abstract class cash_Document extends core_Master
{
    
	
    /**
     * Флаг, който указва, че документа е партньорски
     */
    public $visibleForPartners = TRUE;
    
    
    /**
     * Дали сумата е във валута (различна от основната)
     *
     * @see acc_plg_DocumentSummary
     */
    public $amountIsInNotInBaseCurrency = TRUE;
    
    
    /**
     * Неща, подлежащи на начално зареждане
     */
    public $loadList = 'plg_RowTools, cash_Wrapper, plg_Sorting, acc_plg_Contable,
                     doc_DocumentPlg, plg_Printing, doc_SequencerPlg,acc_plg_DocumentSummary,
                     plg_Search,doc_plg_MultiPrint, bgerp_plg_Blank, doc_plg_HidePrices,
                     bgerp_DealIntf, doc_EmailCreatePlg, cond_plg_DefaultValues';
    
    
    /**
     * Полета свързани с цени
     */
    public $priceFields = 'amount';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = "tools=Пулт, valior, title=Документ, reason, folderId, currencyId=Валута, amount,state, createdOn, createdBy";
    
    
    /**
     * Полето в което автоматично се показват иконките за редакция и изтриване на реда от таблицата
     */
    public $rowToolsField = 'tools';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo, cash';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'ceo, cash';
    
    
    /**
     * Хипервръзка на даденото поле и поставяне на икона за индивидуален изглед пред него
     */
    public $rowToolsSingleField = 'title';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'cash, ceo';
    
    
    /**
     * Кой може да пише?
     */
    public $canWrite = 'cash, ceo';
    
    
    /**
     * Кой може да го контира?
     */
    public $canConto = 'cash, ceo';
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    public $searchFields = 'number, valior, contragentName, reason, id';
    
    
    /**
     * Параметри за принтиране
     */
    public $printParams = array( array('Оригинал'), array('Копие'));
    
    
    /**
     * Стратегии за дефолт стойностти
     */
    public static $defaultStrategies = array(
    	'depositor'      => 'lastDocUser|lastDoc',
    );
    

    /**
     * Основна сч. сметка
     */
    public static $baseAccountSysId = '501';
    
    
    /**
     * Добавяне на дефолтни полета
     * 
     * @param core_Mvc $mvc
     * @return void
     */
    protected function getFields(core_Mvc &$mvc)
    {
    	$mvc->FLD('operationSysId', 'varchar', 'caption=Операция,mandatory');
    	$mvc->FLD('amountDeal', 'double(decimals=2,max=2000000000,min=0)', 'caption=Платени,mandatory,summary=amount');
    	$mvc->FLD('dealCurrencyId', 'key(mvc=currency_Currencies, select=code)', 'input=hidden');
    	$mvc->FLD('reason', 'richtext(rows=2)', 'caption=Основание,mandatory');
    	$mvc->FLD('valior', 'date(format=d.m.Y)', 'caption=Вальор,mandatory');
    	$mvc->FLD('number', 'int', 'caption=Номер');
    	$mvc->FLD('peroCase', 'key(mvc=cash_Cases, select=name)', 'caption=Каса');
    	$mvc->FLD('contragentName', 'varchar(255)', 'caption=Контрагент->Вносител,mandatory');
    	$mvc->FLD('contragentId', 'int', 'input=hidden,notNull');
    	$mvc->FLD('contragentClassId', 'key(mvc=core_Classes,select=name)', 'input=hidden,notNull');
    	$mvc->FLD('contragentAdress', 'varchar(255)', 'input=hidden');
    	$mvc->FLD('contragentPlace', 'varchar(255)', 'input=hidden');
    	$mvc->FLD('contragentPcode', 'varchar(255)', 'input=hidden');
    	$mvc->FLD('contragentCountry', 'varchar(255)', 'input=hidden');
    	$mvc->FLD('creditAccount', 'customKey(mvc=acc_Accounts,key=systemId,select=systemId)', 'input=none');
    	$mvc->FLD('debitAccount', 'customKey(mvc=acc_Accounts,key=systemId,select=systemId)', 'input=none');
    	$mvc->FLD('currencyId', 'key(mvc=currency_Currencies, select=code)', 'caption=Валута (и сума) на плащането->Валута,silent,removeAndRefreshForm=rate|amount');
    	$mvc->FLD('amount', 'double(decimals=2,max=2000000000,min=0)', 'caption=Сума,summary=amount,input=hidden');
    	$mvc->FLD('rate', 'double(decimals=5)', 'caption=Валута (и сума) на плащането->Курс,input=none');
    	$mvc->FLD('notes', 'richtext(bucket=Notes,rows=6)', 'caption=Допълнително->Бележки');
    	$mvc->FLD('state', 'enum(draft=Чернова, active=Контиран, rejected=Сторниран, closed=Контиран)',	'caption=Статус, input=none');
    	$mvc->FLD('isReverse', 'enum(no,yes)', 'input=none,notNull,value=no');
    	 
    	// Поставяне на уникален индекс
    	$mvc->setDbUnique('number');
    }
    
    
    /**
     * Проверка и валидиране на формата
     */
    protected static function on_AfterInputEditForm($mvc, $form)
    {
    	$rec = &$form->rec;
    	
    	if ($form->isSubmitted()){
    		if(!isset($rec->amount) && $rec->currencyId != $rec->dealCurrencyId){
    			$form->setField('amount', 'input');
    			$form->setError("amount", 'Когато плащането е във валута - различна от тази на сделката, сумата трябва да е попълнена');
    			return;
    		}
    		
    		$origin = $mvc->getOrigin($form->rec);
    		$dealInfo = $origin->getAggregateDealInfo();
    		
    		$operation = $dealInfo->allowedPaymentOperations[$rec->operationSysId];
    		$debitAcc = empty($operation['reverse']) ? $operation['debit'] : $operation['credit'];
    		$creditAcc = empty($operation['reverse']) ? $operation['credit'] : $operation['debit'];
    		
    		$rec->debitAccount = $debitAcc;
    		$rec->creditAccount = $creditAcc;
    		$rec->isReverse = empty($operation['reverse']) ? 'no' : 'yes';
    		
    		$contragentData = doc_Folders::getContragentData($rec->folderId);
    		$rec->contragentCountry = $contragentData->country;
    		$rec->contragentPcode = $contragentData->pCode;
    		$rec->contragentPlace = $contragentData->place;
    		$rec->contragentAdress = $contragentData->address;
    		
    		$currencyCode = currency_Currencies::getCodeById($rec->currencyId);
    		$rec->rate = round(currency_CurrencyRates::getRate($rec->valior, $currencyCode, NULL), 4);
    		
    		if($rec->currencyId == $rec->dealCurrencyId){
    			$rec->amount = $rec->amountDeal;
    		}
    		
    		$dealCurrencyCode = currency_Currencies::getCodeById($rec->dealCurrencyId);
    		if($msg = currency_CurrencyRates::checkAmounts($rec->amount, $rec->amountDeal, $rec->valior, $currencyCode, $dealCurrencyCode)){
    			$form->setError('amountDeal', $msg);
    		}
    	}
    	
    	acc_Periods::checkDocumentDate($form, 'valior');
    }
    
    
    /**
     *  Подготовка на филтър формата
     */
    protected static function on_AfterPrepareListFilter($mvc, $data)
    {
    	// Добавяме към формата за търсене търсене по Каса
    	cash_Cases::prepareCaseFilter($data, array('peroCase'));
    }
    

    /**
     * Вкарваме css файл за единичния изглед
     */
    protected static function on_AfterRenderSingle($mvc, &$tpl, $data)
    {
    	$tpl->push('cash/tpl/styles.css', 'CSS');
    }
    
    
    /**
     * Имплементиране на интерфейсен метод (@see doc_DocumentIntf)
     */
    public function getDocumentRow($id)
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
     * Връща разбираемо за човека заглавие, отговарящо на записа
     */
    public static function getRecTitle($rec, $escaped = TRUE)
    {
    	$self = cls::get(get_called_class());
    
    	return $self->singleTitle . " №$rec->id";
    }


    /**
     * Извиква се след подготовката на toolbar-а за табличния изглед
     */
    protected static function on_AfterPrepareListToolbar($mvc, &$data)
    {
    	if(!empty($data->toolbar->buttons['btnAdd'])){
    		$data->toolbar->removeBtn('btnAdd');
    	}
    }


    /**
     * Подготовка на бутоните на формата за добавяне/редактиране
     */
    protected static function on_AfterPrepareEditToolbar($mvc, &$res, $data)
    {
    	// Документа не може да се създава  в нова нишка, ако е възоснова на друг
    	if(!empty($data->form->toolbar->buttons['btnNewThread'])){
    		$data->form->toolbar->removeBtn('btnNewThread');
    	}
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
     * Проверка дали нов документ може да бъде добавен в
     * посочената нишка
     *
     * @param int $threadId key(mvc=doc_Threads)
     * @return boolean
     */
    public static function canAddToThread($threadId)
    {
    	$firstDoc = doc_Threads::getFirstDocument($threadId);
    	$docState = $firstDoc->fetchField('state');
    
    	if(!empty($firstDoc) && $firstDoc->haveInterface('bgerp_DealAggregatorIntf') && $docState == 'active'){
    		
    		// Ако няма позволени операции за документа не може да се създава
    		$operations = $firstDoc->getPaymentOperations();
    		$options = static::getOperations($operations);
    
    		return count($options) ? TRUE : FALSE;
    	}
    
    	return FALSE;
    }

    
    /**
     * Интерфейсен метод на doc_ContragentDataIntf
     * Връща тялото на имейл по подразбиране
     */
    public static function getDefaultEmailBody($id)
    {
    	$self = cls::get(get_called_class());
    	
    	$handle = static::getHandle($id);
    	$title = mb_strtolower($self->singleTitle);
    	$tpl = new ET(tr("Моля запознайте се с нашия {$title}") . ': #[#handle#]');
    	$tpl->append($handle, 'handle');
    	
    	return $tpl->getContent();
    }
    

    /**
     * Имплементация на @link bgerp_DealIntf::getDealInfo()
     *
     * @param int|object $id
     * @return bgerp_iface_DealAggregator
     * @see bgerp_DealIntf::getDealInfo()
     */
    public function pushDealInfo($id, &$aggregator)
    {
    	$rec = self::fetchRec($id);
    	$aggregator->setIfNot('caseId', $rec->peroCase);
    }
    

    /**
     *  Обработки по вербалното представяне на данните
     */
    protected static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
    	$row->title = $mvc->getLink($rec->id, 0);
    	
    	if($fields['-single']){
    
    		$contragent = new core_ObjectReference($rec->contragentClassId, $rec->contragentId);
    		$row->contragentAddress = $contragent->getFullAdress();
    		
    		if($rec->dealCurrencyId != $rec->currencyId){
    			$baseCurrencyId = acc_Periods::getBaseCurrencyId($rec->valior);
    			 
    			if($rec->dealCurrencyId == $baseCurrencyId){
    				$rate = $rec->amountDeal / $rec->amount;
    				$rateFromCurrencyId = $rec->dealCurrencyId;
    				$rateToCurrencyId = $rec->currencyId;
    			} else {
    				$rate = $rec->amount / $rec->amountDeal;
    				$rateFromCurrencyId = $rec->currencyId;
    				$rateToCurrencyId = $rec->dealCurrencyId;
    			}
    			$row->rate = cls::get('type_Double', array('params' => array('decimals' => 5)))->toVerbal($rate);
    			$row->rateFromCurrencyId = currency_Currencies::getCodeById($rateFromCurrencyId);
    			$row->rateToCurrencyId = currency_Currencies::getCodeById($rateToCurrencyId);
    		} else {
    			unset($row->dealCurrencyId);
    			unset($row->amountDeal);
    			unset($row->rate);
    		}
    		 
    		$spellNumber = cls::get('core_SpellNumber');
    		$amountVerbal = $spellNumber->asCurrency($rec->amount, 'bg', FALSE);
    		$row->amountVerbal = $amountVerbal;
    		 
    		// Вземаме данните за нашата фирма
    		$ownCompanyData = crm_Companies::fetchOwnCompany();
    		$Companies = cls::get('crm_Companies');
    		$row->organisation = cls::get('type_Varchar')->toVerbal($ownCompanyData->company);
    		$row->organisationAddress = $Companies->getFullAdress($ownCompanyData->companyId);
    
    		// Извличаме имената на създателя на документа (касиера)
    		$cashierRec = core_Users::fetch($rec->createdBy);
    		$cashierRow = core_Users::recToVerbal($cashierRec);
    		$row->cashier = $cashierRow->names;
    
    		$row->peroCase = cash_Cases::getHyperlink($rec->peroCase);
    	}
    }
}