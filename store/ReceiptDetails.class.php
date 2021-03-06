<?php
/**
 * Клас 'store_ReceiptDetails'
 *
 * Детайли на мениджър на детайлите на складовите разписки (@see store_ReceiptDetails)
 *
 * @category  bgerp
 * @package   store
 * @author    Ivelin Dimov <ivelin_pdimov@abv.com>
 * @copyright 2006 - 2013 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class store_ReceiptDetails extends deals_DeliveryDocumentDetail
{
    
    
    /**
     * Заглавие
     */
    public $title = 'Детайли на складовите разписки';


    /**
     * Заглавие в единствено число
     */
    public $singleTitle = 'Продукт';
    
    
    /**
     * Име на поле от модела, външен ключ към мастър записа
     */
    public $masterKey = 'receiptId';
    
    
    /**
     * Плъгини за зареждане
     */
    public $loadList = 'plg_RowTools, plg_Created, store_Wrapper, plg_SaveAndNew, plg_RowNumbering,Policy=purchase_PurchaseLastPricePolicy, 
                        plg_AlignDecimals2, plg_Sorting, doc_plg_HidePrices, ReverseLastPricePolicy=sales_SalesLastPricePolicy, Policy=purchase_PurchaseLastPricePolicy';
    
    
    /**
     * Активен таб на менюто
     */
    public $menuPage = 'Логистика:Складове';
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'ceo, store';
    
    
    /**
     * Кой има право да променя?
     */
    public $canEdit = 'ceo, store, purchase, sales';
    
    
    /**
     * Кой има право да добавя?
     */
    public $canAdd = 'ceo, store, purchase, sales';
    
    
    /**
     * Кой може да го изтрие?
     */
    public $canDelete = 'ceo, store, purchase, sales';
    
    
    /**
     * Полета, които ще се показват в листов изглед
     */
    public $listFields = 'productId, packagingId, packQuantity, packPrice, discount, amount, weight, volume, quantityInPack';
    
        
    /**
     * Полето в което автоматично се показват иконките за редакция и изтриване на реда от таблицата
     */
    public $rowToolsField = 'RowNumb';
    
    
	/**
     * Полета свързани с цени
     */
    public $priceFields = 'price, amount, discount, packPrice';
    
    
    /**
     * Полета за скриване/показване от шаблоните
     */
    public $toggleFields = 'packagingId=Опаковка,packQuantity=Количество,packPrice=Цена,discount=Отстъпка,amount=Сума,weight=Обем,volume=Тегло,info=Инфо';
    
    
    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
        $this->FLD('receiptId', 'key(mvc=store_Receipts)', 'column=none,notNull,silent,hidden,mandatory');
        parent::setDocumentFields($this);
        $this->FLD('batch', 'text', 'input=none,caption=Партида,after=productId,forceField');
        
        $this->FLD('weight', 'cat_type_Weight', 'input=none,caption=Тегло');
        $this->FLD('volume', 'cat_type_Volume', 'input=none,caption=Обем');
    }

    
    /**
     * Достъпните продукти
     */
    protected function getProducts($masterRec)
    {
    	$property = ($masterRec->isReverse == 'yes') ? 'canSell' : 'canBuy';
    	$property .= ',canStore';
    	
    	// Намираме всички продаваеми продукти, и оттях оставяме само складируемите за избор
    	$products = cat_Products::getProducts($masterRec->contragentClassId, $masterRec->contragentId, $masterRec->date, $property);
    	
    	return $products;
    }


    /**
     * Извиква се след въвеждането на данните от Request във формата ($form->rec)
     *
     * @param core_Mvc $mvc
     * @param core_Form $form
     */
    public static function on_AfterInputEditForm(core_Mvc $mvc, core_Form &$form)
    {
    	parent::inputDocForm($mvc, $form);
    }
    
    
    /**
     * След обработка на записите от базата данни
     */
    public static function on_AfterPrepareListRows(core_Mvc $mvc, $data)
    {
    	$date = ($data->masterData->rec->state == 'draft') ? NULL : $data->masterData->rec->modifiedOn;
    	if(count($data->rows)) {
    		foreach ($data->rows as $i => &$row) {
    			$rec = &$data->recs[$i];
    
    			$row->productId = cat_Products::getAutoProductDesc($rec->productId, $date, 'short', 'public', $data->masterData->rec->tplLang);
    			batch_Defs::appendBatch($rec->productId, $rec->batch, $rec->notes);
    			
    			if($rec->notes){
    				deals_Helper::addNotesToProductRow($row->productId, $rec->notes);
    			}
    		}
    	}
    }
    
    
    /**
     * Преди запис на продукт
     */
    public static function on_BeforeSave($mvc, &$id, $rec, $fields = NULL, $mode = NULL)
    {
    	$rec->weight = cat_Products::getWeight($rec->productId, $rec->packagingId);
    	$rec->volume = cat_Products::getVolume($rec->productId, $rec->packagingId);
    }
}