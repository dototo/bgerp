<?php



/**
 * Фактури
 *
 *
 * @category  bgerp
 * @package   sales
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class sales_Invoices extends deals_InvoiceMaster
{
    
    
    /**
     * Поддържани интерфейси
     */
    public $interfaces = 'doc_DocumentIntf, email_DocumentIntf, doc_ContragentDataIntf, acc_TransactionSourceIntf=sales_transaction_Invoice, bgerp_DealIntf';
    
    
    /**
     * Абревиатура
     */
    public $abbr = 'Inv';


    /**
     * Флаг, който указва, че документа е партньорски
     */
    public $visibleForPartners = TRUE;
    
    
    /**
     * Заглавие
     */
    public $title = 'Фактури за продажби';
    
    
    /**
     * Единично заглавие
     */
    public $singleTitle = 'Фактура';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_RowTools, sales_Wrapper, plg_Sorting, acc_plg_Contable, plg_ExportCsv, doc_DocumentPlg, bgerp_plg_Export,
					doc_EmailCreatePlg, doc_plg_MultiPrint, crm_plg_UpdateContragentData, recently_Plugin, bgerp_plg_Blank, plg_Printing, cond_plg_DefaultValues,deals_plg_DpInvoice,
                    doc_plg_HidePrices, doc_plg_TplManager, acc_plg_DocumentSummary, plg_Search';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'id, number, date, place, folderId, dealValue, vatAmount, type';
    
    
    /**
     * Кои роли могат да филтрират потребителите по екип в листовия изглед
     */
    public $filterRolesForTeam = 'ceo,salesMaster,manager';
    
    
    /**
     * Детайла, на модела
     */
    public $details = 'sales_InvoiceDetails' ;
    
    
    /**
     * Старо име на класа
     */
    public $oldClassName = 'acc_Invoices';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'ceo,invoicer';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'ceo,invoicer';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo,sales';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'ceo,invoicer';
	
	
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'ceo,invoicer';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canExport = 'ceo,sales';
    
    
    /**
     * Кой може да го контира?
     */
    public $canConto = 'ceo,invoicer';
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    public $searchFields = 'number, folderId, id, contragentName';
    
    
    /**
     * Икона за фактура
     */
    public $singleIcon = 'img/16/invoice.png';
    
    
    /**
     * Групиране на документите
     */
    public $newBtnGroup = "3.3|Търговия";
    
    
    /**
     * Кой е основния детайл
     */
    public $mainDetail = 'sales_InvoiceDetails';
    
    
    /**
     * Дефолт диапазон за номерацията на фактурите от настройките на пакета
     */
    public $defaultNumRange = 1;
    
    
    /**
     * Стратегии за дефолт стойностти
     */
    public static $defaultStrategies = array(
    		'place'               => 'lastDocUser|lastDoc',
    		'responsible'         => 'lastDocUser|lastDoc',
    		'contragentCountryId' => 'clientData|lastDocUser|lastDoc',
    		'contragentVatNo'     => 'clientData|lastDocUser|lastDoc',
    		'uicNo'     		  => 'clientData|lastDocUser|lastDoc',
    		'contragentPCode'     => 'clientData|lastDocUser|lastDoc',
    		'contragentPlace'     => 'clientData|lastDocUser|lastDoc',
    		'contragentAddress'   => 'clientData|lastDocUser|lastDoc',
    		'accountId'           => 'lastDocUser|lastDoc',
    		'paymentType' 		  => 'lastDocUser|lastDoc',
    		'template' 		      => 'lastDocUser|lastDoc|defMethod',
    		'numlimit'			  => 'lastDocUser|lastDoc',
    );
    
    
    /**
     * Кои полета ако не са попълнени във визитката на контрагента да се попълнят след запис
     */
    public static $updateContragentdataField = array(
    				    'vatId'   => 'contragentVatNo',
    				    'uicId'   => 'uicNo',
    					'egn'     => 'uicNo',
    					'pCode'   => 'contragentPCode',
		    		    'place'   => 'contragentPlace',
		    		    'address' => 'contragentAddress',
    );
    
    
    /**
     * Описание на модела
     */
    function description()
    {
    	parent::setInvoiceFields($this);
    	
    	$this->FLD('accountId', 'key(mvc=bank_OwnAccounts,select=bankAccountId, allowEmpty)', 'caption=Плащане->Банкова с-ка,after=paymentMethodId,export=Csv');
    	
    	$this->FLD('numlimit', 'enum(1,2)', 'caption=Номер->Диапазон, export=Csv, after=place,input=hidden,notNull,default=1');
    	
    	$this->FLD('number', 'bigint(21)', 'caption=Номер, export=Csv, after=place,input=none');
    	$this->FLD('state', 'enum(draft=Чернова, active=Контиран, rejected=Сторнирана)', 'caption=Статус, input=none,export=Csv');
        $this->FLD('type', 'enum(invoice=Фактура, credit_note=Кредитно известие, debit_note=Дебитно известие,dc_note=Известие)', 'caption=Вид, input=hidden');
        
        $conf = core_Packs::getConfig('sales');
        if($conf->SALE_INV_HAS_FISC_PRINTERS == 'yes'){
        	$this->FLD('paymentType', 'enum(cash=В брой,bank=По банка)', 'mandatory,caption=Плащане->Начин,before=accountId');
        }
        
        $this->setDbUnique('number');
    }
	
	
    /**
     * Извиква се след SetUp-а на таблицата за модела
     */
    function loadSetupData()
    {
    	$tplArr = array();
    	$tplArr[] = array('name' => 'Фактура нормален изглед', 'content' => 'sales/tpl/InvoiceHeaderNormal.shtml', 'lang' => 'bg');
    	$tplArr[] = array('name' => 'Фактура кратък изглед', 'content' => 'sales/tpl/InvoiceHeaderNormalShort.shtml', 'lang' => 'bg');
        $tplArr[] = array('name' => 'Invoice', 'content' => 'sales/tpl/InvoiceHeaderNormalEN.shtml', 'lang' => 'en' , 'oldName' => 'Фактурa EN');
        $tplArr[] = array('name' => 'Invoice short', 'content' => 'sales/tpl/InvoiceHeaderShortEN.shtml', 'lang' => 'en');
       
    	$res = '';
        $res .= doc_TplManager::addOnce($this, $tplArr);
        
        return $res;
    }
    
    
    /**
     * След подготовка на формата
     */
    public static function on_AfterPrepareEditForm($mvc, &$data)
    {
    	parent::prepareInvoiceForm($mvc, $data);
    	
    	$form = &$data->form;
    	$form->setField('contragentPlace', 'mandatory');
    	$form->setField('contragentAddress', 'mandatory');
    	
    	$conf = core_Packs::getConfig('sales');
    	$options = array();
    	$options[1] = "{$conf->SALE_INV_MIN_NUMBER1} - {$conf->SALE_INV_MAX_NUMBER1}";
    	$options[2] = "{$conf->SALE_INV_MIN_NUMBER2} - {$conf->SALE_INV_MAX_NUMBER2}";
    	$form->setOptions('numlimit', $options);
    	$form->setDefault('numlimit', $mvc->defaultNumRange);
    	
    	if(haveRole('ceo,accMaster')){
    		$form->setField('numlimit', 'input');
    	}
    	
    	if($data->aggregateInfo){
    		if($accId = $data->aggregateInfo->get('bankAccountId')){
    			$form->rec->accountId = bank_OwnAccounts::fetchField("#bankAccountId = {$accId}", 'id');
    		}
    	}
    	 
    	if(empty($data->flag)){
    		if($ownAcc = bank_OwnAccounts::getCurrent('id', FALSE)){
    			$form->setDefault('accountId', $ownAcc);
    		}
    	}
    	
    	if($form->rec->vatRate != 'yes' && $form->rec->vatRate != 'separate'){
    		$form->setField('vatReason', 'mandatory');
    	}
    }
    
    
    /**
     * След изпращане на формата
     */
    public static function on_AfterInputEditForm(core_Mvc $mvc, core_Form $form)
    {
    	parent::inputInvoiceForm($mvc, $form);
	}
    
    
    /**
     * Намира ориджина на фактурата (ако има)
     */
    public static function getOrigin($rec)
    {
    	$origin = NULL;
    	$rec = static::fetchRec($rec);
    	
    	if($rec->originId) {
    		return doc_Containers::getDocument($rec->originId);
    	}
    	
    	if($rec->threadId){
    		return doc_Threads::getFirstDocument($rec->threadId);
	    }
    	
    	return $origin;
    }
    
    
    /**
     * Валидиране на полето 'number' - номер на фактурата
     * 
     * Предупреждение при липса на ф-ра с номер едно по-малко от въведения.
     */
    public function on_ValidateNumber(core_Mvc $mvc, $rec, core_Form $form)
    {
        if (empty($rec->number)) {
            return;
        }
        
        $prevNumber = intval($rec->number)-1;
        if (!$mvc->fetchField("#number = {$prevNumber}")) {
            $form->setWarning('number', 'Липсва фактура с предходния номер!');
        }
    }
    
    
    /**
     * Преди запис в модела
     */
    public static function on_BeforeSave($mvc, $id, $rec)
    {
        parent::beforeInvoiceSave($rec);
        
        if($rec->state == 'active'){
        	if(empty($rec->number)){
        		$rec->number = self::getNexNumber($rec);
        		$rec->searchKeywords .= " " . plg_Search::normalizeText($rec->number);
        	}
        }
    }
    
    
    /**
     * Преди подготовката на обобщението на фактурата
     */
    public function on_BeforePrepareSummary($mvc, &$total)
    {
    	if(count($total->vats) == 1){
    		$conf = core_Packs::getConfig('sales');
    		
    		// Ако сме задали ддс сумата да е твърд процент от сумата без ддс и всички артикули са със една и съща ставка на ддс
    		if($conf->SALE_INV_VAT_DISPLAY == 'yes'){
    			$total->vat = $total->amount * key($total->vats);
    		}
    	}
    }
    
    
    /**
     * Извиква се преди рендирането на 'опаковката'
     */
     public static function on_AfterRenderSingleLayout($mvc, &$tpl, $data)
    {
    	if(!Mode::is('printing')){
    		$original = 'ОРИГИНАЛ';
    		if($data->rec->tplLang != 'bg'){
    			$original = "<i>ORIGINAL</i>/{$original}";
    		}
    		$tpl->replace($original, 'INV_STATUS');
    	}
    	 
    	$tpl->push('sales/tpl/invoiceStyles.css', 'CSS');
    }
    
    
    /**
     * След подготовка на тулбара на единичен изглед.
     */
    public static function on_AfterPrepareSingleToolbar($mvc, &$data)
    {
    	$rec = $data->rec;
    	if($rec->type == 'invoice' && $rec->state == 'active' && $rec->dpOperation != 'accrued'){
    		
    		if(dec_Declarations::haveRightFor('add', (object)array('originId' => $data->rec->containerId, 'threadId' => $data->rec->threadId))){
    			$data->toolbar->addBtn('Декларация', array('dec_Declarations', 'add', 'originId' => $data->rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/declarations.png, row=2, title=Създаване на декларация за съответсвие');
    		}
    	}
    }
    
    
    /**
     * След преобразуване на записа в четим за хора вид.
     */
    public static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
    	parent::getVerbalInvoice($mvc, $rec, $row, $fields);
    	
    	if($fields['-single']){
    		if($rec->type == 'dc_note'){
    			$row->type = ($rec->dealValue <= 0) ? 'Кредитно известие' : 'Дебитно известие';
    			$type = ($rec->dealValue <= 0) ? 'Credit note' : 'Debit note';
    		} else {
    			$type = $rec->type;
    		}
    		
    		if($rec->tplLang != 'bg'){
    			$row->type = "<i>" . str_replace('_', " ", $type) . "</i> / {$row->type}";
    		}
    		
    		if($rec->accountId){
    			$Varchar = cls::get('type_Varchar');
    			$ownAcc = bank_OwnAccounts::getOwnAccountInfo($rec->accountId);
    			
    			$row->bank = $Varchar->toVerbal($ownAcc->bank);
    			if($rec->tplLang != 'bg'){
    				$row->bank = str::utf2ascii($row->bank);
    				$row->place = str::utf2ascii($row->place);
    			}
    			$row->bic = $Varchar->toVerbal($ownAcc->bic);
    		}
    	}
    }


    /*
     * Реализация на интерфейса doc_DocumentIntf
     */
    
    
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
    * Имплементиране на интерфейсен метод (@see doc_DocumentIntf)
    */
    public static function getHandle($id)
    {
        $self = cls::get(get_called_class());
        
        $rec = $self->fetch($id);
        
        if (!$rec->number) {
            
            $hnd = $self->abbr . $rec->id . doc_RichTextPlg::$identEnd;
        } else {
            $number = str_pad($rec->number, '10', '0', STR_PAD_LEFT);
        
            $hnd = $self->abbr . $number;
        }
        
        return $hnd;
    } 
    
    
   /**
    * Имплементиране на интерфейсен метод (@see doc_DocumentIntf)
    */
    public static function fetchByHandle($parsedHandle)
    {
        if ($parsedHandle['endDs'] && (strlen($parsedHandle['id']) != 10)) {
            $rec = static::fetch($parsedHandle['id']);
        } else {
            $number = ltrim($parsedHandle['id'], '0');
            if ($number) {
                $rec = static::fetch("#number = '{$number}'");
            }
        }
    	
        return $rec;
    }
    
    
    /**
     * Ф-я връщаща следващия номер на фактурата, ако той е в границите
     * 
     * @return int - следващия номер на фактура
     */
    protected static function getNexNumber($rec)
    {
    	$conf = core_Packs::getConfig('sales');
    	if($rec->numlimit == 2){
    		$min = $conf->SALE_INV_MIN_NUMBER2;
    		$max = $conf->SALE_INV_MAX_NUMBER2;
    	} else {
    		$min = $conf->SALE_INV_MIN_NUMBER1;
    		$max = $conf->SALE_INV_MAX_NUMBER1;
    	}
    	
    	$query = static::getQuery();
    	$query->XPR('maxNum', 'int', 'MAX(#number)');
    	$query->between("number", $min, $max);
    	
    	if(!$maxNum = $query->fetch()->maxNum){
    		$maxNum = $min;
    	}
    	$nextNum = $maxNum + 1;
    	
    	if($nextNum > $max) return NULL;
    	
    	return $nextNum;
    }
    
    
	/**
     * Извиква се след изчисляването на необходимите роли за това действие
     */
    public static function on_AfterGetRequiredRoles($mvc, &$res, $action, $rec = NULL, $userId = NULL)
    {
        // Ако резултата е 'no_one' пропускане
    	if($res == 'no_one') return;
    	
    	if($action == 'add' && isset($rec->threadId)){
    		 $firstDoc = doc_Threads::getFirstDocument($rec->threadId);
    		 $docState = $firstDoc->fetchField('state');
    		 if(!($firstDoc->getInstance() instanceof sales_Sales && $docState == 'active')){
    			$res = 'no_one';
    		}
    	}
    	
    	if($action == 'conto' && isset($rec)){
    	
    		// Не може да се контира, ако има ф-ра с по нова дата
    		$lastDate = $mvc->getNewestInvoiceDate();
    		if($lastDate > $rec->date) {
    			$res = 'no_one';
    		}
    	}
    	
    	// Само ceo,salesmaster и acc могат да оттеглят контирана фактура
    	if($action == 'reject' && isset($rec)){
    		if($rec->state == 'active'){
    			if(!haveRole('ceo,salesMaster,acc', $userId)){
    				$res = 'no_one';
    			}
    		}
    	}
    	
    	// Само ceo,salesmaster и acc могат да възстановят фактура
    	if($action == 'restore' && isset($rec)){
    		if(!haveRole('ceo,salesMaster,acc', $userId)){
    			$res = 'no_one';
    		}
    	}
    }
    
    
    /**
     * След рендиране на копия за принтиране
     * @see doc_plg_MultiPrint
     * 
     * @param core_Mvc $mvc - мениджър
     * @param core_ET $copyTpl - копие за рендиране
     * @param int $copyNum - пореден брой на копието за принтиране
     */
    public static function on_AfterRenderPrintCopy($mvc, &$copyTpl, $copyNum, $rec)
    {
    	if($rec->tplLang == 'bg'){
    		$inv_status = ($copyNum == '1') ?  tr('ОРИГИНАЛ') : tr('КОПИЕ');
    	} else {
    		$inv_status = ($copyNum == '1') ?  "<i>ORIGINAL</i>/" . tr('ОРИГИНАЛ') : "<i>COPY</i>/" . tr('КОПИЕ');
    	}
    	
    	$copyTpl->replace($inv_status, 'INV_STATUS');
    }
    
    
    /**
     * Преди експортиране като CSV
     */
   	public static function on_BeforeExportCsv($mvc, &$rec)
   	{
   		$rec->number = str_pad($rec->number, '10', '0', STR_PAD_LEFT);
   		$rec->dealValue = round($rec->dealValue + $rec->vatAmount - $rec->discountAmount, 2);
   		$rec->state = $mvc->getVerbal($rec, 'state');
   	}
   	
   	
   	/**
   	 * След подготвяне на заявката за експорт
   	 */
   	public static function on_AfterPrepareExportQuery($mvc, &$query)
   	{
   		$query->orWhere("#state = 'rejected' AND #brState = 'active'");
   		$query->where("#state != 'draft'");
   	}
   	
   	
   	/**
   	 *  Подготовка на филтър формата
   	 */
   	public static function on_AfterPrepareListFilter($mvc, $data)
   	{
   		if(!$data->listFilter->getField('invType', FALSE)){
   			$data->listFilter->FNC('invType', 'enum(all=Всички, invoice=Фактура, credit_note=Кредитно известие, debit_note=Дебитно известие)', 'caption=Вид,input,silent');
   		}
   		
   		$conf = core_Packs::getConfig('sales');
   		if($conf->SALE_INV_HAS_FISC_PRINTERS == 'yes'){
   			$data->listFields['paymentType'] = 'Плащане';
   			$data->listFilter->FNC('payType', 'enum(all=Всички,cash=В брой,bank=По банка)', 'caption=Начин на плащане,input');
   			$data->listFilter->showFields .= ",payType";
   		}
   		$data->listFilter->showFields .= ',invType';
   		
   		$data->listFilter->input(NULL, 'silent');
   		
   		if($rec = $data->listFilter->rec){
   			if($rec->invType){
   				if($rec->invType != 'all'){
   					$data->query->where("#type = '{$rec->invType}'");
   					
   					$sign = ($rec->invType == 'credit_note') ? "<=" : ">";
   					$data->query->orWhere("#type = 'dc_note' AND #dealValue {$sign} 0");
   				}
   			}
   			
   			if($rec->payType){
   				if($rec->payType != 'all'){
   					$data->query->where("#paymentType = '{$rec->payType}'");
   				}
   			}
   		}
   	}
   	
   	
   	/**
   	 * Връща сумата на ддс-то на платените в брой фактури, в основната валута
   	 * 
   	 * @param date $from - от
   	 * @param date $to - до
   	 * @return double $amount - сумата на ддс-то на платените в брой фактури
   	 */
   	public static function getVatAmountInCash($from, $to = NULL)
   	{
   		if(empty($to)){
   			$to = dt::today();
   		}
   		
   		$amount = 0;
   		$query = static::getQuery();
   		$query->where("#paymentType = 'cash'");
   		$query->where("#state = 'active'");
   		$query->between("date", $from, $to);
   		
   		while($rec = $query->fetch()){
   			$total = $rec->vatAmount;
   			$amount += $total;
   		}
   		
   		return round($amount, 2);
   	}

   	
   	/**
   	 * Валидиране на полето 'date' - дата на фактурата
   	 * Предупреждение ако има фактура с по-нова дата (само при update!)
   	 */
   	public static function on_ValidateDate(core_Mvc $mvc, $rec, core_Form $form)
   	{
   		$newDate = $mvc->getNewestInvoiceDate();
   		if($newDate > $rec->date) {
   	
   			// Най-новата валидна ф-ра в БД е по-нова от настоящата.
   			$form->setError('date',
   					'Не може да се запише фактура с дата по-малка от последната активна фактура (' .
   					dt::mysql2verbal($newestInvoiceRec->date, 'd.m.y') .
   					')'
   			);
   		}
   	}
}