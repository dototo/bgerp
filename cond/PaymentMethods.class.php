<?php



/**
 * Клас 'cond_PaymentMethods' - Начини на плащане
 *
 *
 * @category  bgerp
 * @package   cond
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class cond_PaymentMethods extends core_Master
{
    
    
    /**
     * Плъгини за зареждане
     */
    var $loadList = 'plg_Created, plg_RowTools2, cond_Wrapper, plg_State2,plg_Translate';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    var $listFields = 'id, title, state, type';
    
    
    /**
     * Заглавие
     */
    var $title = 'Методи на плащане';
    
    
    /**
     * Наименование на единичния обект
     */
    var $singleTitle = "Метод на плащане";
    
    
    /**
     * Кой има право да чете?
     */
    var $canRead = 'ceo, cond, admin';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	var $canList = 'ceo,cond, admin';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	var $canSingle = 'ceo,cond, admin';
    
    
    /**
     * Кой има право да променя?
     */
    var $canEdit = 'ceo, cond, admin';
    
    
    /**
     * Кой има право да добавя?
     */
    var $canAdd = 'ceo, cond, admin';
    
    
    /**
     * Кой може да го изтрие?
     */
    var $canDelete = 'ceo, cond, admin';
    
    
    /**
     * Шаблон за единичен изглед
     */
    var $singleLayoutFile = "cond/tpl/SinglePaymentMethod.shtml";
    
    
    /**
     * Хипервръзка на даденото поле и поставяне на икона за индивидуален изглед пред него
     */
    var $rowToolsSingleField = 'title';
    
    
    /**
     * Описание на модела
     */
    function description()
    {
        // Съкратено име на плащането
        $this->FLD('sysId', 'varchar(16)', 'caption=Системно ID, input=none');

        // Текстово описание
        $this->FLD('title', 'varchar', 'caption=Описание, mandatory, translate,oldFieldName=description');
        $this->FLD('type', 'enum(,cash=В брой,bank=По банков път,intercept=С прихващане,card=С карта)', 'caption=Вид плащане');
        
        // Процент на авансовото плащане
        $this->FLD('downpayment', 'percent(min=0,max=1)', 'caption=Авансово плащане->Дял,hint=Процент,oldFieldName=payAdvanceShare');
        
        // Процент на плащане преди експедиция
        $this->FLD('paymentBeforeShipping', 'percent(min=0,max=1)', 'caption=Плащане преди получаване->Дял,hint=Процент,oldFieldName=payBeforeReceiveShare');
        
        // Плащане при получаване
        $this->FLD('paymentOnDelivery', 'percent(min=0,max=1)', 'caption=Плащане при доставка->Дял,hint=Процент,oldFieldName=payOnDeliveryShare');
        
        // Колко дни след фактуриране да е балансовото плащане?
        $this->FLD('timeBalancePayment', 'time(uom=days,suggestions=веднага|15 дни|30 дни|60 дни)', 'caption=Плащане след фактуриране->Срок,hint=дни,oldFieldName=payBeforeInvTerm');
        
        $this->setDbUnique('sysId');
        $this->setDbUnique('title');
    }
    
    
    /**
     * Извиква се след въвеждането на данните от Request във формата
     */
    protected static function on_AfterInputEditForm($mvc, &$form)
    {
    	if($form->isSubmitted()){
	    	
            $rec = &$form->rec;
	    	
            $total = $rec->downpayment + $rec->paymentBeforeShipping + $rec->paymentOnDelivery;
	    	 
	    	if($total > 1){
	    		$form->setError('downpayment,paymentBeforeShipping,paymentOnDelivery', 'Въведените проценти не бива да надвишават 100%');
	    	}
    	}
    }
    

    /**
     * Дали подадения метод е Наложен платеж (Cash on Delivery)
     * @param mixed $payment - ид или име на метод
     * @return boolean
     */
    public static function isCOD($payment)
    {
    	// Ако няма избран метод се приема, че е COD
    	if(!$payment) return TRUE;
    	
    	$where = (is_numeric($payment)) ? $payment : "#sysId = '{$payment}'";
    	$sysId = static::fetchField($where, 'sysId');
    	
    	return ($sysId == 'COD');
    }
    
    
    /**
     * Връща масив съдържащ плана за плащане
     * @param int $pmId - ид на метод
     * @param double $amount - сума
     * @param invoiceDate - дата на фактуриране (ако няма е датата на продажбата)
     * @return array $res - масив съдържащ информация за плащането
     * 
     * 		['downpayment'] 		      - сума за авансово плащане
     * 		['paymentBeforeShipping']     - сума за плащане преди експедиране
     * 		['paymentOnDelivery']         - сума за плащане при получаване
     * 		['paymentAfterInvoice']       - сума за плащане след фактуриране
     * 		['deadlineForBalancePayment'] - крайна дата за окончателно плащане
     * 		['timeBalancePayment']        - срок за окончателно плащане
     */
    public static function getPaymentPlan($pmId, $amount, $invoiceDate)
    {
        expect($rec = self::fetch($pmId));
		
        if($rec->downpayment) {
            $res['downpayment'] = $rec->downpayment * $amount;
        }

        if($rec->paymentBeforeShipping) {
            $res['paymentBeforeShipping'] = $rec->paymentBeforeShipping * $amount;
        }

        if($rec->paymentOnDelivery) {
            $res['paymentOnDelivery'] = $rec->paymentOnDelivery * $amount;
        }

        $paymentAfterInvoice = 1 - $rec->paymentOnDelivery - $rec->paymentBeforeShipping - $rec->downpayment;
        $paymentAfterInvoice = round($paymentAfterInvoice * $amount, 4);
        $res['timeBalancePayment'] = $rec->timeBalancePayment;
        
        if($paymentAfterInvoice > 0) {
            $res['paymentAfterInvoice']       = $paymentAfterInvoice;
            $res['deadlineForBalancePayment'] = dt::addSecs($rec->timeBalancePayment, $invoiceDate);
            $res['deadlineForBalancePayment'] = dt::verbal2mysql($res['deadlineForBalancePayment'], FALSE);
        }
        
        // Ако плащането е на момента, крайната дата за плащане е подадената дата
        if($rec->sysId == 'COD'){
        	$res['deadlineForBalancePayment'] = dt::verbal2mysql($invoiceDate, FALSE);
        }
        
        return $res;
    }


	/**
     * Подготвя условията за плащане
     */
    public static function preparePaymentPlan(&$data, $pmId, $amount, $invoiceDate, $currencyId) 
    {
        $planArr = self::getPaymentPlan($pmId, $amount, $invoiceDate);
        
        if(count($planArr)){
        	$Double = cls::get('type_Double');
        	$Double->params['decimals'] = 2;
        	$Date = cls::get('type_Date');
        	
	        foreach($planArr as $key => &$value){
	        	if($key != 'deadlineForBalancePayment'){
	        		$value = $Double->toVerbal($value);
	        		$value = "<span class='cCode'>{$currencyId}</span> {$value}";
	        	} else {
	        		$value = $Date->toVerbal($value);
	        	}
	        }
	        
	        $data->paymentPlan = $planArr;
        }
    }
    
    
    /**
     * Дали платежния план е просрочен
     * 
     * @param array $payment - платежния план (@see static::getPaymentPlan)
     * @param double $restAmount - оставаща сума за плащане
     * @return boolean
     */
    public static function isOverdue($payment, $restAmount)
    {
    	expect(is_array($payment) && isset($restAmount));
    	$today = dt::today();
    	$restAmount = round($restAmount, 4);
    	
    	// Ако остатъка за плащане е 0 или по-малко
    	if($restAmount <= 0) return FALSE;
    	
    	// Ако няма крайна дата на плащане, не е просрочена
    	if(!$payment['deadlineForBalancePayment']) return FALSE;
    	
    	// Ако текущата дата след крайния срок за плащане, документа е просрочен
    	return ($today > $payment['deadlineForBalancePayment']);
    }
    
    
    /**
     * Дали платежния метод има авансова част
     * @param int $id - ид на метод
     * @return boolean
     */
    public static function hasDownpayment($id)
    {
    	// Ако няма избран метод се приема, че няма авансово плащане
    	if(!$id) return FALSE;
    	
    	expect($rec = static::fetch($id));
    	
    	return ($rec->downpayment) ? TRUE : FALSE;
    }
    
    
    /**
     * Сортиране по name
     */
    protected static function on_BeforePrepareListFilter($mvc, &$res, $data)
    {
        $data->query->orderBy('#title');
    }
    
    
	/**
     * Извиква се след SetUp-а на таблицата за модела
     */
    static function on_AfterSetupMvc($mvc, &$res)
    {
    	$file = "cond/csv/PaymentMethods.csv";
    	$fields = array(
            0 => 'sysId',
	    	1 => 'title',
            2 => 'downpayment',
            3 => 'paymentBeforeShipping',
            4 => 'paymentOnDelivery',
            5 => 'timeBalancePayment');
            
    	$cntObj = csv_Lib::importOnce($mvc, $file, $fields);
    	$res .= $cntObj->html;
    	
    	return $res;
    }
    
    
    /**
     * Връща очакваното авансово плащане
     * 
     * @param int $id - ид на платежен метод
     * @param double $amount - сума
     * @return double $amount - сумата на авансовото плащане
     */
    public static function getDownpayment($id, $amount)
    {
    	// Ако няма ид, няма очакван аванс
    	if(!$id) return NULL;
    	
    	// Ако сумата е 0, няма очакван аванс
    	if($amount == 0) return NULL;
    	
    	// Трябва да са подадени валидни данни
    	expect(is_numeric($amount));
    	expect($rec = static::fetch($id));
    	
    	// Ако няма авансово плащане в метода, няма очакван аванс
    	if(empty($rec->downpayment)) return NULL;
    	
    	// Изчисляване на очаквания аванс
    	$amount = $rec->downpayment * $amount;
    	
    	// Връщане на аванса
    	return $amount;
    }
}