<?php
/**
 * Клас 'sales_Sales'
 *
 * Мениджър на документи за продажба на продукти от каталога
 *
 *
 * @category  bgerp
 * @package   sales
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class sales_Sales extends deals_DealMaster
{
	
    
    /**
     * Дали да се показва бутон на чернова документ
     * 
     * @see doc_EmailCreatePlg
     */
	public $canEmailDraft = TRUE;
	
	
	/**
     * Заглавие
     */
    public $title = 'Договори за продажба';
    
	
    /**
     * Флаг, който указва, че документа е партньорски
     */
    public $visibleForPartners = TRUE;
    
    
    /**
     * Абревиатура
     */
    public $abbr = 'Sal';
    
    
    /**
     * Поддържани интерфейси
     */
    public $interfaces = 'doc_DocumentIntf, email_DocumentIntf,
                          acc_TransactionSourceIntf=sales_transaction_Sale,
                          bgerp_DealIntf, bgerp_DealAggregatorIntf, deals_DealsAccRegIntf, 
                          acc_RegisterIntf,batch_MovementSourceIntf=batch_movements_Deal,deals_InvoiceSourceIntf';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_RowTools2, sales_Wrapper, plg_Sorting, acc_plg_Registry, doc_plg_MultiPrint, doc_plg_TplManager, doc_DocumentPlg, acc_plg_Contable, plg_Printing,
                    acc_plg_DocumentSummary, plg_Search, doc_plg_HidePrices, cond_plg_DefaultValues,
					doc_EmailCreatePlg, bgerp_plg_Blank, plg_Clone, doc_SharablePlg, doc_plg_Close,doc_plg_BusinessDoc';
    
    
    /**
     * Активен таб на менюто
     */
    public $menuPage = 'Търговия:Продажби';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'ceo,sales';
    
    
    /**
     * Кой може да затваря?
     */
    public $canClose = 'ceo,sales';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'ceo,sales';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'ceo,sales';
    
    
    /**
     * Кои роли могат да филтрират потребителите по екип в листовия изглед
     */
    public $filterRolesForTeam = 'ceo,salesMaster,manager';
    
    
    /**
     * Кой може да принтира фискална бележка
     */
    public $canPrintfiscreceipt = 'ceo,sales';


	/**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo,sales';
	
	
	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'ceo,sales';
    

    /**
     * Кой може да го активира?
     */
    public $canConto = 'ceo,sales,acc';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'valior, title=Документ, folderId, currencyId=Валута, amountDeal, amountDelivered, amountPaid, amountInvoiced,
                             dealerId, initiatorId,paymentState,
                             createdOn, createdBy';


    /**
     * Детайла, на модела
     */
    public $details = 'sales_SalesDetails';
    
    
    /**
     * Заглавие в единствено число
     */
    public $singleTitle = 'Продажба';
   
    
    /**
     * Групиране на документите
     */ 
    public $newBtnGroup = "3.1|Търговия";
   
    
    /**
     * Полета свързани с цени
     */
    public $priceFields = 'amountDeal,amountDelivered,amountPaid,amountInvoiced,amountToPay,amountToDeliver,amountToInvoice';
    
    
    /**
     * Файл с шаблон за единичен изглед на статия
     */
    public $singleLayoutFile = 'sales/tpl/SingleLayoutSale.shtml';
    
    
    /**
     * Икона за единичния изглед
     */
    public $singleIcon = 'img/16/cart_go.png';

    
    /**
     * Поле в което се замества шаблона от doc_TplManager
     */
    public $templateFld = 'SINGLE_CONTENT';
    
    
    /**
     * Стратегии за дефолт стойностти
     */
    public static $defaultStrategies = array(
    
    	'deliveryTermId'     => 'clientCondition|lastDocUser|lastDoc',
    	'paymentMethodId'    => 'clientCondition|lastDocUser|lastDoc',
    	'currencyId'         => 'lastDocUser|lastDoc|CoverMethod',
    	'bankAccountId'      => 'lastDocUser|lastDoc',
    	'makeInvoice'        => 'lastDocUser|lastDoc',
    	'deliveryLocationId' => 'lastDocUser|lastDoc',
    	'chargeVat'			 => 'lastDocUser|lastDoc|defMethod',
    	'template' 			 => 'lastDocUser|lastDoc|defMethod',
    );
    
    
    /**
     * В коя група по дефолт да влизат контрагентите, към които е направен документа
     */
    public $crmDefGroup = 'customers';
    
    
    /**
     * Кое поле показва сумата на сделката
     */
    public $canClosewith = 'ceo,salesMaster';
    
    
    /**
     * Позволени операции на последващите платежни документи
     */
    public $allowedPaymentOperations = array(
    		'customer2caseAdvance' => array('title' => 'Авансово плащане от Клиент', 'debit' => '501', 'credit' => '412'),
    		'customer2bankAdvance' => array('title' => 'Авансово плащане от Клиент', 'debit' => '503', 'credit' => '412'),
    		'customer2case'        => array('title' => 'Плащане от Клиент', 'debit' => '501', 'credit' => '411'),
    		'customer2bank'        => array('title' => 'Плащане от Клиент', 'debit' => '503', 'credit' => '411'),
    		'case2customer'        => array('title' => 'Връщане към Клиент', 'debit' => '411', 'credit' => '501', 'reverse' => TRUE),
    		'bank2customer'        => array('title' => 'Връщане към Клиент', 'debit' => '411', 'credit' => '503', 'reverse' => TRUE),
    		'caseAdvance2customer' => array('title' => 'Върнат аванс на Клиент', 'debit' => '412', 'credit' => '501', 'reverse' => TRUE),
    		'bankAdvance2customer' => array('title' => 'Върнат аванс на Клиент', 'debit' => '412', 'credit' => '503', 'reverse' => TRUE),
    		'debitDeals'           => array('title' => 'Прихващане на вземания', 'debit' => '*', 'credit' => '411'),
    		'creditDeals'          => array('title' => 'Прихващане на задължение', 'debit' => '411', 'credit' => '*', 'reverse' => TRUE), 
    		);

    
    /**
     * Позволени операции за посследващите складови документи/протоколи
     */
    public $allowedShipmentOperations = array('delivery'        => array('title' => 'Експедиране на стока', 'debit' => '411', 'credit' => 'store'),
    										  'deliveryService' => array('title' => 'Доставка на услуги', 'debit' => '411', 'credit' => 'service'),
    						                  'buyServices'     => array('title' => 'Връщане на услуги', 'debit' => 'service', 'credit' => '411', 'reverse' => TRUE),
    										  'stowage'         => array('title' => 'Връщане на стока', 'debit' => 'store', 'credit' => '411', 'reverse' => TRUE),
    );
    
    
    /**
     * Полета от които се генерират ключови думи за търсене (@see plg_Search)
     */
    public $searchFields = 'deliveryTermId, deliveryLocationId, shipmentStoreId, paymentMethodId, currencyId, bankAccountId, caseId, initiatorId, dealerId, folderId, id';
    
    
    /**
     * Как се казва приключващия документ
     */
    public $closeDealDoc = 'sales_ClosedDeals';
    
    
    /**
     * Главен детайл на модела
     */
    public $mainDetail = 'sales_SalesDetails';
    
    
    /**
     * Записите от кои детайли на мениджъра да се клонират, при клониране на записа
     * (@see plg_Clone)
     */
    public $cloneDetailes = 'sales_SalesDetails';
    
    
    /**
     * Кеш на уникален индекс
     */
    protected $unique = 0;
    
    
    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
        parent::setDealFields($this);
        $this->FLD('reff', 'varchar(255)', 'caption=Ваш реф.,class=contactData,after=valior');
        $this->FLD('bankAccountId', 'key(mvc=bank_Accounts,select=iban,allowEmpty)', 'caption=Плащане->Банкова с-ка,after=currencyRate');
        $this->FLD('priceListId', 'key(mvc=price_Lists,select=title,allowEmpty)', 'caption=Цени');
        $this->FLD('deliveryTermTime', 'time(uom=days,suggestions=1 ден|5 дни|10 дни|1 седмица|2 седмици|1 месец)', 'caption=Доставка->Срок дни,after=deliveryTime');
    }
    
    
    /**
     * Извиква се след въвеждането на данните от Request във формата ($form->rec)
     */
    public static function on_AfterInputEditForm($mvc, &$form)
    {
    	$rec = $form->rec;
    	
    	if(empty($rec->id)){
    		
    		// Ако има локация, питаме търговските маршрути, кой да е дефолтния търговец
    		if(isset($rec->deliveryLocationId)){
    			$dealerId = sales_Routes::getSalesmanId($rec->deliveryLocationId);
    		}
    		
    		// Ако няма, но отговорника на папката е търговец - него
    		if(empty($dealerId)){
    			$inCharge = doc_Folders::fetchField($rec->folderId, 'inCharge');
    			if(core_Users::haveRole('sales', $inCharge)){
    				$dealerId = $inCharge;
    			}
    		}
    		
    		// В краен случай от последната продажба на същия потребител
    		if(empty($dealerId)){
    			$dealerId = cond_plg_DefaultValues::getFromLastDocument($mvc, $rec->folderId, 'dealerId', TRUE);
    		}
    		
    		$form->setDefault('dealerId', $dealerId);
    	}
    	
    	if ($form->isSubmitted()) {
    		if(isset($rec->deliveryTermTime) && isset($rec->deliveryTime)){
    			$form->setError('deliveryTime,deliveryTermTime', 'Трябва да е избран само един срок на доставка');
    		}
    	}
    }
    
    
    /**
     * Преди запис на документ
     */
    public static function on_BeforeSave($mvc, $res, $rec)
    {
    	if($rec->reff === ''){
    		$rec->reff = NULL;
    	}
    	
		// Ако има б. сметка се нотифицират операторите и
    	if($rec->bankAccountId){
    		$operators = bank_OwnAccounts::fetchField("#bankAccountId = '{$rec->bankAccountId}'",'operators');
    		$rec->sharedUsers = keylist::merge($rec->sharedUsers, $operators);
    	}
    }
    
    
    /**
     * Преди ъпдейт след промяна на детайла
     */
    public static function on_BeforeUpdatedMaster($mvc, &$rec)
    {
    	if(isset($rec->id) && empty($rec->deliveryTime)){
    		
    		$dQuery = sales_SalesDetails::getQuery();
    		$dQuery->where("#saleId = {$rec->id}");
    		$dQuery->XPR('maxTerm', 'time', 'MAX(#term)');
    		$dQuery->show('maxTerm');
    		if($maxTerm = $dQuery->fetch()->maxTerm){
    			
    			$rec->deliveryTermTime = max($rec->deliveryTermTime, $maxTerm);
    		}
    	}
    }
    
    
    /**
     * Преди показване на форма за добавяне/промяна.
     *
     * @param sales_Sales $mvc
     * @param stdClass $data
     */
    public static function on_AfterPrepareEditForm($mvc, &$data)
    {
        $form = &$data->form;
        
        // При клониране
        if($data->action == 'clone'){
        	
        	// Ако няма reff взимаме хендлъра на оригиналния документ
        	if(empty($form->rec->reff)){
        		$form->rec->reff = $mvc->getHandle($form->rec->id);
        	}
        	
        	// Инкрементираме reff-а на оригинална
        	$form->rec->reff = str::addIncrementSuffix($form->rec->reff, 'v', 2);
        }
        
        $myCompany = crm_Companies::fetchOwnCompany();
        
        $options = bank_Accounts::getContragentIbans($myCompany->companyId, 'crm_Companies', TRUE);
        if(count($options)){
        	foreach ($options as $id => &$name){
        		if(is_numeric($id)){
        			$name = bank_OwnAccounts::fetchField("#bankAccountId = {$id}", 'title');
        		}
        	}
        }
       
        $form->setOptions('bankAccountId', $options);
        if($bankAccountId = bank_OwnAccounts::getCurrent('bankAccountId', FALSE)){
        	$accountRec = bank_Accounts::fetch($bankAccountId);
        	$bankCurrencyCode = currency_Currencies::getCodeById($accountRec->currencyId);
        	
        	if($form->rec->currencyId == $bankCurrencyCode){
        		$form->setDefault('bankAccountId', $bankAccountId);
        	}
        }
       
        $form->setDefault('contragentClassId', doc_Folders::fetchCoverClassId($form->rec->folderId));
        $form->setDefault('contragentId', doc_Folders::fetchCoverId($form->rec->folderId));
        
        $hideRate = core_Packs::getConfigValue('sales', 'SALES_USE_RATE_IN_CONTRACTS');
        if($hideRate == 'yes'){
        	$form->setField('currencyRate', 'input');
        }
        
        if(empty($form->rec->id)){
        	$form->setField('deliveryLocationId', 'removeAndRefreshForm=dealerId');
        }
        
        $form->setOptions('priceListId', array('' => '') + price_Lists::getAccessibleOptions($form->rec->contragentClassId, $form->rec->contragentId));
    }
    
    
	/**
     * След подготовка на тулбара на единичен изглед
     */
    static function on_AfterPrepareSingleToolbar($mvc, &$data)
    {
    	$rec = &$data->rec;
    	
    	if(empty($rec->threadId)){
    		$rec->threadId = $mvc->fetchField($rec->id, 'threadId');
    	}
    	
    	if($rec->state == 'active'){
    		$closeArr = array('sales_ClosedDeals', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE);
    		
    		if(sales_ClosedDeals::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
	    		$data->toolbar->addBtn('Приключване', $closeArr, "row=2,ef_icon=img/16/closeDeal.png,title=Приключване на продажбата");
	    	} else {
	    		
	    		// Ако разликата е над допустимата но потребителя има права 'sales', той вижда бутона но не може да го използва
	    		if(!sales_ClosedDeals::isSaleDiffAllowed($rec) && haveRole('sales')){
	    			$data->toolbar->addBtn('Приключване', $closeArr, "ef_icon=img/16/closeDeal.png,title=Приключване на продажбата,error=Нямате право да приключите продажба с разлика над допустимото");
	    		}
	    	}
    		
    		// Ако протокол може да се добавя към треда и не се експедира на момента
    		if (sales_Services::haveRightFor('add', (object)array('threadId' => $rec->threadId))) {
    			$serviceUrl =  array('sales_Services', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE);
	            $data->toolbar->addBtn('Пр. услуги', $serviceUrl, 'ef_icon = img/16/shipment.png,title=Продажба на услуги,order=9.22');
	        }
	        
	        // Ако ЕН може да се добавя към треда и не се експедира на момента
	    	if (store_ShipmentOrders::haveRightFor('add', (object)array('threadId' => $rec->threadId))) {
	    		$shipUrl = array('store_ShipmentOrders', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE);
	            $data->toolbar->addBtn('Експедиране', $shipUrl, 'ef_icon = img/16/EN.png,title=Експедиране на артикулите от склада,order=9.21');
	        }
	        
    		if(sales_Proformas::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
	    		$data->toolbar->addBtn("Проформа", array('sales_Proformas', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'row=2,ef_icon=img/16/proforma.png,title=Създаване на нова проформа фактура,order=9.9992');
		    }
	    	
	        if(sales_Invoices::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
	    		$data->toolbar->addBtn("Фактура", array('sales_Invoices', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/invoice.png,title=Създаване на нова фактура,order=9.9993');
		    }
		    
		    if(cash_Pko::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
		    	$data->toolbar->addBtn("ПКО", array('cash_Pko', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/money_add.png,title=Създаване на нов приходен касов ордер');
		    }
		    
    		if(bank_IncomeDocuments::haveRightFor('add', (object)array('threadId' => $rec->threadId))){
		    	$data->toolbar->addBtn("ПБД", array('bank_IncomeDocuments', 'add', 'originId' => $rec->containerId, 'ret_url' => TRUE), 'ef_icon=img/16/bank_add.png,title=Създаване на нов приходен банков документ');
		    }
		    
		    if(!Mode::is('printing') && !Mode::is('text', 'xhtml') && $mvc->haveRightFor('printFiscReceipt', $rec)){
		    	$data->toolbar->addBtn('КБ', array($mvc, 'printReceipt', $rec->id), NULL, 'ef_icon=img/16/cash-receipt.png,warning=Искате ли да издадете нова касова бележка ?,title=Издаване на касова бележка', array('class' => "actionBtn", 'target' => 'iframe_a'));
		    }
    	}
    }
    
    
    /**
     * Принтиране на касова бележка
     */
    public function act_PrintReceipt()
    {
    	expect($id = Request::get('id', 'int'));
    	expect($rec = $this->fetchRec($id));
    	$this->requireRightFor('printFiscReceipt', $rec);
    	
    	$conf = core_Packs::getConfig('sales');
    	$Driver = cls::get($conf->SALE_FISC_PRINTER_DRIVER);
    	$driverData = $this->prepareFiscPrinterData($rec);
    	
    	return $Driver->createFile($driverData);
    }
    
    
    /**
     * Подготвя данните за фискалния принтер
     */
    private function prepareFiscPrinterData($rec)
    {
    	$dQuery = $this->sales_SalesDetails->getQuery();
    	$dQuery->where("#saleId = {$rec->id}");
    	
    	$data = (object)array('products' => array(), 'payments' => array());
    	while($dRec = $dQuery->fetch()){
    		$nRec = new stdClass();
    		$nRec->id = $dRec->productId;
    		$nRec->managerId = cat_Products::getClassId();
    		$nRec->quantity = $dRec->packQuantity;
    		if($dRec->discount){
    			$nRec->discount = $dRec->discount;
    		}
    		$pInfo = cat_Products::getProductInfo($dRec->productId);
    		$nRec->measure = ($dRec->packagingId) ? cat_UoM::getTitleById($dRec->packagingId) : cat_UoM::getShortName($pInfo->productRec->measureId);
    		$nRec->vat = cat_Products::getVat($dRec->productId, $rec->valior);
    		if($rec->chargeVat != 'yes' && $rec->chargeVat != 'separate'){
    			$nRec->vat = 0;
    		}
    		
    		$nRec->price = $dRec->packPrice;
    		if($pInfo->productRec->vatGroup){
    			$nRec->vatGroup = $pInfo->productRec->vatGroup;
    		}
    		
    		$nRec->name = $pInfo->productRec->name;
    		
    		$data->products[] = $nRec;
    	}
    	
    	$nRec = new stdClass();
    	$nRec->type = 0;
    	$nRec->amount = round($rec->amountPaid, 2);
    	
    	$data->short = TRUE;
    	$data->hasVat = ($rec->chargeVat == 'yes' || $rec->chargeVat == 'separate') ? TRUE : FALSE;
    	$data->payments[] = $nRec;
    	$data->totalPaid = $nRec->amount;
    	
    	return $data;
    }
    
	
    /**
     * Имплементация на @link bgerp_DealIntf::getDealInfo()
     * 
     * @param int|object $id
     * @return bgerp_iface_DealAggregator
     * @see bgerp_DealIntf::getDealInfo()
     */
    public function pushDealInfo($id, &$result)
    {
        $rec = $this->fetchRec($id);
        $actions = type_Set::toArray($rec->contoActions);
        
        // Извличаме продуктите на продажбата
        $dQuery = sales_SalesDetails::getQuery();
        $dQuery->where("#saleId = {$rec->id}");
        $detailRecs = $dQuery->fetchAll();
       
        $downPayment = NULL;
        if(cond_PaymentMethods::hasDownpayment($rec->paymentMethodId)){
        	// Колко е очакваното авансово плащане
        	$downPayment = cond_PaymentMethods::getDownpayment($rec->paymentMethodId, $rec->amountDeal);
		}
        
        // Кои са позволените операции за последващите платежни документи
        $result->set('allowedPaymentOperations', $this->getPaymentOperations($rec));
        $result->set('allowedShipmentOperations', $this->getShipmentOperations($rec));
        $result->set('involvedContragents', array((object)array('classId' => $rec->contragentClassId, 'id' => $rec->contragentId)));
        
        $result->set('amount', $rec->amountDeal);
        $result->setIfNot('currency', $rec->currencyId);
        $result->setIfNot('rate', $rec->currencyRate);
        $result->setIfNot('vatType', $rec->chargeVat);
        $result->setIfNot('agreedValior', $rec->valior);
        $result->setIfNot('deliveryLocation', $rec->deliveryLocationId);
        $result->setIfNot('deliveryTime', $rec->deliveryTime);
        $result->setIfNot('deliveryTerm', $rec->deliveryTermId);
        $result->setIfNot('storeId', $rec->shipmentStoreId);
        $result->setIfNot('paymentMethodId', $rec->paymentMethodId);
        $result->setIfNot('caseId', $rec->caseId);
        $result->setIfNot('bankAccountId', $rec->bankAccountId);
        $result->setIfNot('priceListId', $rec->priceListId);
        
        sales_transaction_Sale::clearCache();
        $entries = sales_transaction_Sale::getEntries($rec->id);
        $deliveredAmount = sales_transaction_Sale::getDeliveryAmount($entries);
        $paidAmount = sales_transaction_Sale::getPaidAmount($entries, $rec);
        
        $result->set('agreedDownpayment', $downPayment);
        $result->set('downpayment', sales_transaction_Sale::getDownpayment($entries));
        $result->set('amountPaid', $paidAmount);
        $result->set('deliveryAmount', $deliveredAmount);
        $result->set('blAmount', sales_transaction_Sale::getBlAmount($entries));
        
        // Опитваме се да намерим очакваното плащане
        $expectedPayment = NULL;
        
        // Ако доставеното > платено това е разликата
        if($deliveredAmount > $paidAmount){
        	$expectedPayment = $deliveredAmount - $paidAmount;
        } elseif($amountFromProforma = sales_Proformas::getExpectedDownpayment($rec)){
        	
        	// Ако има авансова фактура след последния платежен документ, това е сумата от аванса и
        	$expectedPayment = $amountFromProforma;
        } else {
        	
        	// В краен случай това е очаквания аванс от метода на плащане
        	$expectedPayment = $downPayment;
        }
        
        // Ако има очаквано плащане, записваме го
        if($expectedPayment){
        	if(empty($deliveredAmount)){
        		$expectedPayment = $expectedPayment - $paidAmount;
        	}
        	
        	if($expectedPayment > 0){
        		$result->set('expectedPayment', $expectedPayment);
        	}
        }
        
        // Спрямо очакваното авансово плащане ако има, кои са дефолт платежните операции
        $agreedDp = $result->get('agreedDownpayment');
        $actualDp = $result->get('downpayment');
        
        // Дефолтните платежни операции са плащания към доставчик
        $result->set('defaultCaseOperation', 'customer2case');
        $result->set('defaultBankOperation', 'customer2bank');
        
        // Ако се очаква авансово плащане и платения аванс е под 80% от аванса,
        // очакваме още да се плаща по аванаса
        if($agreedDp){
        	if(empty($actualDp) || $actualDp < $agreedDp * 0.8){
        		$result->set('defaultCaseOperation', 'customer2caseAdvance');
        		$result->set('defaultBankOperation', 'customer2bankAdvance');
        	}
        }
        
        if (isset($actions['ship'])) {
            $result->setIfNot('shippedValior', $rec->valior);
        }
        
        $agreed = array();
        foreach ($detailRecs as $dRec) {
            $p = new bgerp_iface_DealProduct();
            foreach (array('productId', 'packagingId', 'discount', 'quantity', 'quantityInPack', 'price', 'notes') as $fld){
            	$p->{$fld} = $dRec->{$fld};
            }
            $p->weight  = cat_Products::getWeight($p->productId, $p->packagingId);
            $p->volume  = cat_Products::getVolume($p->productId, $p->packagingId);
            
            $agreed[] = $p;
            
            $push = TRUE;
            $index = $p->productId;
            $shipped = $result->get('shippedPacks');
            	
            $inPack = $p->quantityInPack;
            if($shipped && isset($shipped[$index])){
            	if($shipped[$index]->inPack < $inPack){
            		$push = FALSE;
            	}
            }
            	
            if($push){
            	$arr = (object)array('packagingId' => $p->packagingId, 'inPack' => $inPack);
            	$result->push('shippedPacks', $arr, $index);
            }
         }
         
         $agreed = deals_Helper::normalizeProducts(array($agreed));
         $result->set('products', $agreed);
         $result->set('contoActions', $actions);
         $result->set('shippedProducts', sales_transaction_Sale::getShippedProducts($entries));
    }
    
    
    /**
     * Кои са позволените платежни операции за тази сделка
     */
    public function getPaymentOperations($id)
    {
    	$rec = $this->fetchRec($id);
    	 
    	$allowedPaymentOperations = $this->allowedPaymentOperations;
    	 
    	if($rec->paymentMethodId){
    
    		// Ако има метод за плащане и той няма авансова част, махаме авансовите операции
    		if(!cond_PaymentMethods::hasDownpayment($rec->paymentMethodId)){
    			unset($allowedPaymentOperations['customer2caseAdvance'], 
    					$allowedPaymentOperations['customer2bankAdvance'], 
    					$allowedPaymentOperations['caseAdvance2customer'],
    					$allowedPaymentOperations['bankAdvance2customer']);
    		}
    	}
    	 
    	return $allowedPaymentOperations;
    }
    
    
    /**
     * Приключва всички приключени продажби
     */
    function cron_CloseOldSales()
    {
    	$conf = core_Packs::getConfig('sales');
    	$olderThan = $conf->SALE_CLOSE_OLDER_THAN;
    	$limit = $conf->SALE_CLOSE_OLDER_NUM;
    	$ClosedDeals = cls::get('sales_ClosedDeals');
    	
    	$this->closeOldDeals($olderThan, $ClosedDeals, $limit);
    }
    
    
    /**
     * Нагласяне на крон да приключва продажби и да проверява дали са просрочени
     */
    protected function setCron(&$res)
    {
    	// Крон метод за затваряне на остарели продажби
    	$rec = new stdClass();
        $rec->systemId = "Close sales";
        $rec->description = "Затваряне на приключените продажби";
        $rec->controller = "sales_Sales";
        $rec->action = "CloseOldSales";
        $rec->period = 180;
        $rec->offset = mt_rand(0,30);
        $rec->delay = 0;
        $rec->timeLimit = 100;
        $res .= core_Cron::addOnce($rec);

        // Проверка по крон дали продажбата е просрочена
        $rec2 = new stdClass();
        $rec2->systemId = "IsSaleOverdue";
        $rec2->description = "Проверяване за просрочени продажби";
        $rec2->controller = "sales_Sales";
        $rec2->action = "CheckSalesPayments";
        $rec2->period = 60;
        $rec2->offset = mt_rand(0,30);
        $rec2->delay = 0;
        $rec2->timeLimit = 100;
        $res .= core_Cron::addOnce($rec2);
    }
    
    
    /**
     * Зарежда шаблоните на продажбата в doc_TplManager
     */
    protected function setTemplates(&$res)
    {
    	$tplArr = array();
    	$tplArr[] = array('name' => 'Договор за продажба',    'content' => 'sales/tpl/sales/Sale.shtml', 'lang' => 'bg' , 'narrowContent' => 'sales/tpl/sales/SaleNarrow.shtml');
    	$tplArr[] = array('name' => 'Договор за изработка',   'content' => 'sales/tpl/sales/Manufacturing.shtml', 'lang' => 'bg', 'narrowContent' => 'sales/tpl/sales/ManufacturingNarrow.shtml');
    	$tplArr[] = array('name' => 'Договор за услуга',      'content' => 'sales/tpl/sales/Service.shtml', 'lang' => 'bg', 'narrowContent' => 'sales/tpl/sales/ServiceNarrow.shtml');
    	$tplArr[] = array('name' => 'Sales contract',         'content' => 'sales/tpl/sales/SaleEN.shtml', 'lang' => 'en', 'narrowContent' => 'sales/tpl/sales/SaleNarrowEN.shtml');
    	$tplArr[] = array('name' => 'Manufacturing contract', 'content' => 'sales/tpl/sales/ManufacturingEN.shtml', 'lang' => 'en', 'narrowContent' => 'sales/tpl/sales/ManufacturingNarrowEN.shtml');
    	$tplArr[] = array('name' => 'Service contract',       'content' => 'sales/tpl/sales/ServiceEN.shtml', 'lang' => 'en', 'narrowContent' => 'sales/tpl/sales/ServiceNarrowEN.shtml');
       
        $res .= doc_TplManager::addOnce($this, $tplArr);
    }
    
    
	/**
     * Проверява дали продажбата е просрочена или платени
     */
    function cron_CheckSalesPayments()
    {
    	core_App::setTimeLimit(300);
    	$conf = core_Packs::getConfig('sales');
    	$overdueDelay = $conf->SALE_OVERDUE_CHECK_DELAY;
    	
    	$this->checkPayments($overdueDelay);
    }
    
    
    /**
     * Изпълнява се след подготовката на ролите, които могат да изпълняват това действие
     */
    public static function on_AfterGetRequiredRoles($mvc, &$res, $action, $rec = NULL, $userId = NULL)
    {
    	if($action == 'printfiscreceipt' && isset($rec)){
    		
    		$actions = type_Set::toArray($rec->contoActions);
    		
    		if ($actions['ship'] && $actions['pay']) {
    			$conf = core_Packs::getConfig('sales');
    			
    			// Ако няма избран драйвер за принтер или той е деинсталиран никой не може да издава касова бележка
    			if($conf->SALE_FISC_PRINTER_DRIVER == '' || core_Classes::fetchField($conf->SALE_FISC_PRINTER_DRIVER, 'state') == 'closed'){
    				$res = 'no_one';
    			}
    		} else {
    			$res = 'no_one';
    		}
    	}
    	
    	if($action == 'closewith' && isset($rec)){
    		if(sales_SalesDetails::fetch("#saleId = {$rec->id}")){
    			$res = 'no_one';
    		}
    	}
    }
    
    
    /**
     * Извиква се преди рендирането на 'опаковката'
     */
    public static function on_AfterRenderSingleLayout($mvc, &$tpl, &$data)
    {
    	// Изкарваме езика на шаблона от сесията за да се рендира статистиката с езика на интерфейса
    	core_Lg::pop();
    	$statisticTpl = getTplFromFile('sales/tpl/SaleStatisticLayout.shtml');
    	$tpl->replace($statisticTpl, 'STATISTIC_BAR');
    	
    	// Отново вкарваме езика на шаблона в сесията
    	core_Lg::push($data->rec->tplLang);
    }
    
    
    /**
     * След рендиране на единичния изглед
     */
    public static function on_AfterRenderSingle($mvc, &$tpl, $data)
    {
    	// Слагаме iframe заради касовата бележка, ако не принтираме
    	if(!Mode::is('printing')){
    		$tpl->append("<iframe name='iframe_a' style='display:none'></iframe>");
    	}
    }
    
    
    /**
     * Показва информация за перото по Айакс
     */
    public function act_ShowInfo()
    {
    	$id = Request::get('id', 'varchar');
    	$unique = Request::get('unique', 'int');
    	
    	$tpl = new ET("[#link#]");
    	$row = new stdClass();
    	
    	if (substr(strstr($id, "job="), 1)) { 
    		
    		$jobId = substr(strstr($id, "="), 1);
    		$rec = planning_Jobs::fetchRec($jobId);
    		$row = planning_Jobs::recToVerbal($rec);
    		$row->link = planning_Jobs::getLink($rec->id, 0);
    		
    		$tpl->placeObject($row);
    	} else {
    		$saleId = substr(strstr($id, "="), 1);
	    	$rec = $this->fetchRec($saleId);
	    	$row = $this->recToVerbal($rec);
	    	$row->link = self::getLink($rec->id, 0);
	    	$tpl->placeObject($row);
    	}

    	if (Request::get('ajax_mode')) {
    		$resObj = new stdClass();
    		$resObj->func = "html";
    		$resObj->arg = array('id' => "info{$unique}", 'html' => $tpl->getContent(), 'replace' => TRUE);
    	
    		return array($resObj);
    	} else {
    		return $tpl;
    	}
    }
  
    
    /**
     *  Намира последната продажна цена на артикулите
     */
    public static function getLastProductPrices($contragentClass, $contragentId)
    {
    	$Contragent = cls::get($contragentClass);
    	$ids = array();
    	
    	// Намираме ид-та на всички продажби, ЕН и протоколи за този контрагент
    	foreach (array('sales_Sales', 'store_ShipmentOrders', 'sales_Services') as $Cls){
    		$query = $Cls::getQuery();
    		$query->where("#contragentClassId = {$Contragent->getClassId()} AND #contragentId = {$contragentId}");
    		$query->where("#state = 'active' || #state = 'closed'");
    		$query->show('id');
    		$query->orderBy("valior", 'DESC');
    		while($rec = $query->fetch()){
    			$ids[] = $rec->id;
    		}
    		$key = md5(implode('', $ids));
    	}
    	
    	if(!count($ids)) return array();
    	
    	$cacheArr = core_Cache::get('sales_Sales', $key);
    	
    	// Имаме ли кеширани данни
    	if(!$cacheArr){
    		
    		// Ако няма инвалидираме досегашните кешове за продажбите
    		core_Cache::removeByType('sales_Sales');
    		$cacheArr = array();
    		
    		// Проверяваме на какви цени сме продавали в детайлите на продажбите, ЕН и протоколите
    		foreach (array('sales_SalesDetails', 'store_ShipmentOrderDetails', 'sales_ServicesDetails') as $Detail){
    			$Detail = cls::get($Detail);
    			$dQuery = $Detail->getQuery();
    			$dQuery->where("#state = 'active' || #state = 'closed'");
    			$dQuery->show("productId,price,{$Detail->masterKey}");
    			
    			$dQuery->EXT('state', $Detail->Master->className, "externalName=state,externalKey={$Detail->masterKey}");
    			$dQuery->EXT('contragentClassId', $Detail->Master->className, "externalName=contragentClassId,externalKey={$Detail->masterKey}");
    			$dQuery->EXT('contragentId', $Detail->Master->className, "externalName=contragentId,externalKey={$Detail->masterKey}");
    			$dQuery->where("#contragentClassId = {$Contragent->getClassId()} AND #contragentId = {$contragentId}");
    			
    			// Кешираме артикулите с цените
    			while($dRec = $dQuery->fetch()){
    				$cacheArr[$dRec->productId] = $dRec->price;
    			}
    		}
    		
    		// Кешираме новите данни
    		core_Cache::set('sales_Sales', $key, $cacheArr, 1440);
    	}
    	
    	return $cacheArr;
    }
    
    
    /**
     * Метод по подразбиране за намиране на дефолт шаблона
     */
    public function getDefaultTemplate_($rec)
    {
    	$cData = doc_Folders::getContragentData($rec->folderId);
    	$bgId = drdata_Countries::fetchField("#commonName = 'Bulgaria'", 'id');
    	
    	$conf = core_Packs::getConfig('sales');
    	$def = (empty($cData->countryId) || $bgId === $cData->countryId) ? $conf->SALE_SALE_DEF_TPL_BG : $conf->SALE_SALE_DEF_TPL_EN;
    	
    	return $def;
    }
    
    
    /**
     * След подготовка на информацията за наличните табове
     */
    public static function on_AfterPrepareDealTabs($mvc, &$res, &$data)
    {
    	if(!isset($data->tabs)) return;
    	$url = getCurrentUrl();
    	
    	if(haveRole('ceo,planning,sales,store')){
    		$manifacturable = static::getManifacurableProducts($data->rec);
    		if(count($manifacturable)){
    			$url['dealTab'] = 'JobsInfo';
    			$data->tabs->TAB('JobsInfo', 'Задания' , $url);
    		}
    	}
    }
    
    
    /**
     * Подготвяме информацията за наличните задания към артикули от сделката
     * 
     * @param stdClass $data
     * @return void
     */
    protected function prepareJobsInfo($data)
    {
    	$rec = $data->rec;
    	$data->JobsInfo = array();
    	
    	// Подготвяме информацията за наличните задания към нестандартните (частните) артикули в продажбата
    	$dQuery = sales_SalesDetails::getQuery();
    	$dQuery->where("#saleId = {$rec->id}");
    	$dQuery->show('productId,packagingId,quantity,tolerance');
    	
    	while($dRec = $dQuery->fetch()){
    		if($dRow = sales_SalesDetails::prepareJobInfo($dRec, $rec)){
    			$data->JobsInfo[] = $dRow;
    		}
    	}
    	
    	if(planning_Jobs::haveRightFor('Createjobfromsale', (object)array('saleId' => $rec->id))){
    		$data->addJobUrl = array('planning_Jobs', 'CreateJobFromSale', 'saleId' => $rec->id, 'ret_url' => TRUE);
    	}
    }
    
    
    /**
     * Рендиране на информацията на заданията
     *
     * @param core_ET $tpl
     * @param stdClass $data
     */
    protected function renderJobsInfo(&$tpl, $data)
    {
    	// Ако има подготвена информация за наличните задания, рендираме я
    	if($data->tabs->hasTab('JobsInfo') && haveRole('ceo,planning,sales,store')){
    		
    		$Jobs = cls::get('planning_Jobs');
    		$table = cls::get('core_TableView', array('mvc' => $Jobs));
    		
    		plg_AlignDecimals2::alignDecimals($Jobs, $data->jobInfo, $data->jobInfo);
    		
    		foreach ($data->JobsInfo as &$row){
    			foreach (array('quantity', 'quantityFromTasks', 'quantityProduced') as $var){
    				if($row->{$var} == 0){
    						$row->{$var} = "<span class='quiet'>{$row->{$var}}</span>";
    					}
    				}
    			}
    	
    			$jobsTable = $table->get($data->JobsInfo, 'jobId=Задание,productId=Артикул,dueDate=Падеж,quantity=Количество->Планувано,quantityFromTasks=Количество->Произведено,quantityProduced=Количество->Заскладено');
    			$jobTpl = new core_ET("<div style='margin-top:6px'>[#table#]</div>");
    			$jobTpl->replace($jobsTable, 'table');
    			$tpl->replace($jobTpl, 'JOB_INFO');
    		}
    		
    		if(isset($data->addJobUrl)){
    			$addLink = ht::createLink('', $data->addJobUrl, FALSE, 'ef_icon=img/16/add.png,title=Създаване на ново задание за производство към артикул');
    			$tpl->replace($addLink, 'JOB_ADD_BTN');
    		}
    }
    
    
    /**
     * Връща всички производими артикули от продажбата
     * 
     * @param mixed $id - ид или запис
     * @return array $res - масив с производимите артикули
     */
    public static function getManifacurableProducts($id)
    {
    	$rec = static::fetchRec($id);
    	$res = array();
    	
    	$saleQuery = sales_SalesDetails::getQuery();
    	$saleQuery->where("#saleId = {$rec->id}");
    	$saleQuery->EXT('meta', 'cat_Products', 'externalName=canManifacture,externalKey=productId');
    	$saleQuery->where("#meta = 'yes'");
    	$saleQuery->show('productId');
    	
    	while($dRec = $saleQuery->fetch()){
    		$res[$dRec->productId] = cat_Products::getTitleById($dRec->productId, FALSE);
    	}
    	
    	return $res;
    }
    
    
    /**
     * След вербализиране на записа
     */
    public static function on_AfterRecToVerbal($mvc, &$row, $rec, $fields = array())
    {
    	if(isset($rec->bankAccountId)){
    		if(!Mode::is('text', 'xhtml') && !Mode::is('printing') && !Mode::is('pdf')){
    			$row->bankAccountId = bank_Accounts::getHyperlink($rec->bankAccountId);
    		}
    	}
    	
    	$commonSysId = ($rec->tplLang == 'bg') ? "commonConditionSale" : "commonConditionSaleEng";
    	if($cond = cond_Parameters::getParameter($rec->contragentClassId, $rec->contragentId, $commonSysId)){
    		$row->commonConditionQuote = cls::get('type_Varchar')->toVerbal($cond);
    	}
    	
    	if($rec->chargeVat != 'yes' && $rec->chargeVat != 'separate'){
    		
    		if(!Mode::is('printing') && !Mode::is('text', 'xhtml') && !Mode::is('pdf')){
    			if($rec->contragentClassId == crm_Companies::getClassId()){
    				$companyRec = crm_Companies::fetch($rec->contragentId);
    				$bulgariaCountryId = drdata_Countries::fetchField("#commonName = 'Bulgaria'");
    				if($companyRec->country != $bulgariaCountryId && drdata_Countries::isEu($companyRec->country)){
    					if(empty($companyRec->vatId)){
    						$row->vatId = tr('Ще бъде предоставен');
    						$row->vatId = "<span class='red'>{$row->vatId}</span>";
    					}
    				}
    			}
    		}
    	}
    	
    	if(isset($rec->priceListId)){
    		$row->priceListId = price_Lists::getHyperlink($rec->priceListId, TRUE);
    	}
    }


    /**
     * В кои корици може да се вкарва документа
     * 
     * @return array - интерфейси, които трябва да имат кориците
     */
    public static function getAllowedFolders()
    {
        
    	return array('crm_ContragentAccRegIntf');
    }

}