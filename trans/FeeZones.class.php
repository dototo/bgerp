<?php


/**
 * Модел "Взаимодействие на Зони и Налва"
 *
 *
 * @category  bgerp
 * @package   trans
 * @author    Kristiyan Serafimov <kristian.plamenov@gmail.com>
 * @copyright 2006 - 2016 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
class trans_FeeZones extends core_Master
{


    /**
     * Полета, които се виждат
     */
    public $listFields = "name,deliveryTermId, createdOn, createdBy";


    /**
     * Старо име на класа
     */
    public $oldClassName = "trans_ZoneNames";


    /**
     * Заглавие
     */
    public $title = "Имена на зони";


    /**
     * Плъгини за зареждане
     */
    public $loadList = "plg_Created, plg_Sorting, plg_RowTools2, plg_Printing, trans_Wrapper";


    /**
     * Време за опресняване информацията при лист на събитията
     */
    var $refreshRowsTime = 5000;


    /**
     * Кой има право да чете?
     */
    var $canRead = 'ceo,admin,trans';


    /**
     * Кой има право да променя?
     */
    var $canEdit = 'ceo,admin,trans';


    /**
     * Кой има право да добавя?
     */
    var $canAdd = 'ceo,admin,trans';


    /**
     * Кой може да го разглежда?
     */
    var $canList = 'ceo,admin,trans';


    /**
     * Кой може да разглежда сингъла на документите?
     */
    var $canSingle = 'ceo,admin,trans';


    /**
     * Кой може да го види?
     */
    var $canView = 'ceo,admin,trans';


    /**
     * Кой може да го изтрие?
     */
    var $canDelete = 'ceo,admin,trans';


    /**
     * Детайли за зареждане
     */
    public $details = "trans_Fees, trans_Zones";


    /**
     * Единично поле за RowTools
     */
    public $rowToolsSingleField = 'name';


    /**
     * Описание на модела (таблицата)
     */
    public function description()
    {
        //id column
        $this->FLD('name', 'varchar(16)', 'caption=Зона, mandatory');
        $this->FLD('deliveryTermId', 'key(mvc=cond_DeliveryTerms, select = codeName)', 'caption=Условие на доставка, mandatory');
    }
}