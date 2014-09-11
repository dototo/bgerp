<?php



/**
 * Документ за "Прехвърляне на вземания"
 * Могат да се добавят към нишки на покупки, продажби и финансови сделки
 *
 *
 * @category  bgerp
 * @package   deals
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2014 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class deals_DebitDocuments extends deals_Document
{
    
    
	/**
	 * За конвертиране на съществуващи MySQL таблици от предишни версии
	 */
	public $oldClassName = 'deals_DebitDocument';
	
	
    /**
     * Какви интерфейси поддържа този мениджър
     */
    public  $interfaces = 'doc_DocumentIntf, acc_TransactionSourceIntf=deals_transaction_DebitDocument, sales_PaymentIntf, bgerp_DealIntf, email_DocumentIntf, doc_ContragentDataIntf';
   
    
    /**
     * Заглавие на мениджъра
     */
    public $title = "Прехвърляне на вземания";
    
    
    /**
     * Неща, подлежащи на начално зареждане
     */
    public $loadList = 'plg_RowTools, deals_Wrapper, plg_Sorting, acc_plg_Contable,
                     doc_DocumentPlg, plg_Printing, acc_plg_DocumentSummary,
                     plg_Search, bgerp_plg_Blank,bgerp_DealIntf, doc_EmailCreatePlg';
    
    
    /**
	 * Кой може да го разглежда?
	 */
	public $canList = 'ceo, dealsMaster';


	/**
	 * Кой може да разглежда сингъла на документите?
	 */
	public $canSingle = 'ceo, deals';
    
    
    /**
     * Заглавие на единичен документ
     */
    public $singleTitle = 'Прехвърляне на взeмане';
    
    
    /**
     * Абревиатура
     */
    public $abbr = "Cdd";
    
    
    /**
     * Кой има право да чете?
     */
    public $canRead = 'deals, ceo';
    
    
    /**
     * Кой може да пише?
     */
    public $canWrite = 'deals, ceo';
    
    
    /**
     * Кой може да го контира?
     */
    public $canConto = 'deals, ceo';
    
    
    /**
     * Кой може да го оттегля
     */
    public $canRevert = 'deals, ceo';
    
    
    /**
     * Файл с шаблон за единичен изглед на статия
     */
    public $singleLayoutFile = 'deals/tpl/SingleLayoutDebitDocument.shtml';

    
    /**
     * Групиране на документите
     */
    public $newBtnGroup = "4.5|Финанси";
    
    
    /**
     * Основна операция
     */
    protected static $operationSysId = 'debitDeals';
    
    
    /**
     * Описание на модела
     */
    public function description()
    {
    	parent::addDocumentFields($this);
    }
    
    
    /**
     * Проверка и валидиране на формата
     */
    function on_AfterInputEditForm($mvc, $form)
    {
    	$rec = &$form->rec;
    	
    	if ($form->isSubmitted()){
    		$oprtations = $form->dealInfo->get('allowedPaymentOperations');
    		$operation = $oprtations[$rec->operationSysId];
    		$debitAcc = deals_Deals::fetchField($rec->dealId, 'accountId');
    		
    		$debitAccount = empty($operation['reverse']) ? acc_Accounts::fetchRec($debitAcc)->systemId : $operation['credit'];
    		$creditAccount = empty($operation['reverse']) ? $operation['credit'] : acc_Accounts::fetchRec($debitAcc)->systemId;
    		
    		// Коя е дебитната и кредитната сметка
    		$rec->debitAccount = $debitAccount;
    		$rec->creditAccount = $creditAccount;
    		$rec->isReverse = empty($operation['reverse']) ? 'no' : 'yes';
    		acc_Periods::checkDocumentDate($form, 'valior');
    		
    		$currencyCode = currency_Currencies::getCodeById($rec->currencyId);
    		if(!$rec->rate){
    			$rec->rate = round(currency_CurrencyRates::getRate($rec->valior, $currencyCode, NULL), 4);
    		} else {
    			if($msg = currency_CurrencyRates::hasDeviation($rec->rate, $rec->valior, $currencyCode, NULL)){
    				$form->setWarning('rate', $msg);
    			}
    		}
    	}
    }
}
