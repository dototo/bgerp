<?php


/**
 * Мениджър за "Средства за плащане" 
 *
 *
 * @category  bgerp
 * @package   cond
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.11
 */
class cond_Payments extends core_Manager {
	
	
	/**
	 * Интерфейси, поддържани от този мениджър
	 */
	public $interfaces = 'cond_PaymentAccRegIntf';
	
	
	/**
	 * За конвертиране на съществуващи MySQL таблици от предишни версии
	 */
	public $oldClassName = 'pos_Payments';
	
	
    /**
     * Заглавие
     */
    public $title = "Безналични методи за плащане";
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_Created, plg_RowTools, plg_State2, cond_Wrapper, acc_plg_Registry';

    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'id, title, change, code, state';
    
    
    /**
     * Кой може да го прочете?
     */
    public $canRead = 'ceo, cond';
    
    
    /**
     * Кой може да променя?
     */
    public $canWrite = 'ceo, cond';
    
    
    /**
	 * Кой може да променя състоянието на валутата
	 */
    public $canChangestate = 'ceo,cond,admin';
    
    
    /**
     * Кой може да го отхвърли?
     */
    public $canReject = 'ceo, cond';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo,cond';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'ceo,cond';
	
	
    /**
     * Описание на модела
     */
    function description()
    {
    	$this->FLD('title', 'varchar(255)', 'caption=Наименование');
    	$this->FLD('change', 'enum(yes=Да,no=Не)', 'caption=Ресто?,value=no,tdClass=centerCol');
    	$this->FLD('code', 'int', 'caption=Код,mandatory,tdClass=centerCol');
    	
    	$this->setDbUnique('title');
    }
    
    
    /**
     * Записи за инициализиране на таблицата
     */
    protected static function on_AfterSetupMvc($mvc, &$res)
    {
    	$file = "cond/csv/Pospayments.csv";
    	
    	$fields = array(
	    	0 => "title", 
	    	1 => "state", 
	    	2 => "change",
    		3 => "code",);
    	
    	$cntObj = csv_Lib::importOnce($mvc, $file, $fields);
    	
    	$res .= $cntObj->html;
    	
    	return $res;
    }
    
    
    /**
     * Връща масив от обекти, които са ид-та и заглавията на методите
     * @return array $payments
     */
    public static function fetchSelected()
    {
    	$payments = array();
    	$query = static::getQuery();
	    $query->where("#state = 'active'");
	    $query->orderBy("code");
	    while($rec = $query->fetch()) {
	    	$payment = new stdClass();
	    	$payment->id = $rec->id;
	    	$payment->title = $rec->title;
	    	$payments[] = $payment;
	    }
	    
    	return $payments;
    }
    
    
    /**
     *  Метод отговарящ дали даден платежен връща ресто
     *  @param int $id - ид на метода
     *  @return boolean $res - дали връща или не връща ресто
     */
    public static function returnsChange($id)
    {
    	expect($rec = static::fetch($id), 'Няма такъв платежен метод');
    	($rec->change == 'yes') ? $res = TRUE : $res = FALSE;
    	
    	return $res;
    }
    
    
    /**
     * @see crm_ContragentAccRegIntf::getItemRec
     * @param int $objectId
     */
    public static function getItemRec($objectId)
    {
    	$self = cls::get(__CLASS__);
    	$result = NULL;
    
    	if ($rec = $self->fetch($objectId)) {
    		$result = (object)array(
    				'num' => $rec->id,
    				'title' => $rec->title,
    		);
    	}
    
    	return $result;
    }
    
    
    /**
     * @see crm_ContragentAccRegIntf::itemInUse
     * @param int $objectId
     */
    public static function itemInUse($objectId)
    {
    	// @todo!
    }
    
    
    /**
     * След промяна на обект от регистър
     */
    protected static function on_AfterSave($mvc, &$id, &$rec, $fieldList = NULL)
    {
    	if($rec->state == 'active'){
    
    		// Ако валутата е активна, добавя се като перо
    		$rec->lists = keylist::addKey($rec->lists, acc_Lists::fetchField(array("#systemId = '[#1#]'", 'nonCash'), 'id'));
    		acc_Lists::updateItem($mvc, $rec->id, $rec->lists);
    	} else {
    		// Ако валутата НЕ е активна, перото се изтрива ("изключва" ако вече е използвано)
    		$rec->lists = keylist::addKey($rec->lists, acc_Lists::fetchField(array("#systemId = '[#1#]'", 'nonCash'), 'id'));
    		acc_Lists::removeItem($mvc, $rec->id, $rec->lists);
    	}
    }
}