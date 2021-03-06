<?php

/**
 * Базов драйвер за драйвер на артикул
 *
 *
 * @category  bgerp
 * @package   cat
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @title     Базов драйвер за драйвер на артикул
 */
abstract class cat_ProductDriver extends core_BaseClass
{
	
	
	/**
	 * Кой може да избира драйвъра
	 */
	public $canSelectDriver = 'ceo, cat, sales';
	
	
	/**
	 * Интерфейси които имплементира
	 */
	public $interfaces = 'cat_ProductDriverIntf';

	
	/**
	 * Мета данни по подразбиране
	 * 
	 * @param strint $defaultMetaData
	 */
	protected $defaultMetaData;
	
	
	/**
     * Икона за единичния изглед
     */
    protected $icon = 'img/16/wooden-box.png';
	
	
    /**
     * Добавя полетата на драйвера към Fieldset
     *
     * @param core_Fieldset $fieldset
     */
    public function addFields(core_Fieldset &$fieldset)
    {
    
    }
    
    
    /**
     * Кой може да избере драйвера
     */
    public function canSelectDriver($userId = NULL)
    {
    	return core_Users::haveRole($this->canSelectDriver, $userId);
    }
    
    
	/**
	 * Преди показване на форма за добавяне/промяна.
	 *
	 * @param cat_ProductDriver $Driver
	 * @param embed_Manager $Embedder
	 * @param stdClass $data
	 */
	public static function on_AfterPrepareEditForm(cat_ProductDriver $Driver, embed_Manager $Embedder, &$data)
	{
		$form = &$data->form;
		$driverFields = array_keys($Embedder->getDriverFields($Driver));
		
		$driverRefreshedFields = $form->getFieldParam($Embedder->driverClassField, 'removeAndRefreshForm');
		$driverRefreshedFields = explode('|', $driverRefreshedFields);
		
		$refreshFieldsDriver = array_unique(array_merge($driverFields, $driverRefreshedFields));
		$driverRefreshFields = implode('|', $refreshFieldsDriver);
		
		if($unIndex = array_search('proto', $refreshFieldsDriver)){
			unset($refreshFieldsDriver[$unIndex]);
		}
		
		$protoRefreshFields = implode('|', $refreshFieldsDriver);
		
		// Добавяме при смяна на драйвева или на прототип полетата от драйвера да се рефрешват и те
		$form->setField($Embedder->driverClassField, "removeAndRefreshForm={$driverRefreshFields}");
		$form->setField('proto', "removeAndRefreshForm={$protoRefreshFields}");
		
		// Намираме полетата на формата
		$fields = $form->selectFields();
		
		// Ако има полета
		if(count($fields)){
			
			// За всички полета
			foreach ($fields as $name => $fld){
					
				// Ако има атрибут display
				$display = $form->getFieldParam($name, 'display');
					
				// Ако е 'hidden' и има зададена стойност, правим полето скрито
				if($display === 'hidden'){
					if(!is_null($form->rec->$name)){
						$form->setField($name, 'input=hidden');
					}
				} elseif($display === 'readOnly'){
			
					// Ако е 'readOnly' и има зададена стойност, правим го 'само за четене'
					if(!is_null($form->rec->$name)){
						$form->setReadOnly($name);
					}
				}
			}
		}
	}
	
	
	/**
	 * Връща счетоводните свойства на обекта
	 */
	public function getFeatures($productId)
	{
		return array();
	}

	
	/**
	 * Кои опаковки поддържа продукта
	 *
	 * @param array $metas - кои са дефолтните мета данни от ембедъра
	 * @return array $metas - кои са дефолтните мета данни
	 */
	public function getDefaultMetas($metas)
	{
		// Взимаме дефолтните мета данни от ембедъра
		$metas = arr::make($metas, TRUE);
	
		// Ако за драйвера има дефолтни мета данни, добавяме ги към тези от ембедъра
		if(!empty($this->defaultMetaData)){
			$metas = $metas + arr::make($this->defaultMetaData, TRUE);
		}
	
		return $metas;
	}
	

	/**
	 * Връща стойността на параметъра с това име, или
	 * всички параметри с техните стойностти
	 * 
	 * @param string $classId - ид на ембедъра
	 * @param string $id   - ид на записа
	 * @param string $name - име на параметъра, или NULL ако искаме всички
	 * @return mixed - стойност или FALSE ако няма
	 */
	public function getParams($classId, $id, $name = NULL)
	{
		return FALSE;
	}
	
	
	/**
	 * Подготовка за рендиране на единичния изглед
	 *
	 * @param cat_ProductDriver $Driver
	 * @param embed_Manager $Embedder
	 * @param stdClass $res
	 * @param stdClass $data
	 */
	public static function on_AfterPrepareSingle(cat_ProductDriver $Driver, embed_Manager $Embedder, &$res, &$data)
	{
		$data->Embedder = $Embedder;
		$data->isSingle = TRUE;
		$data->documentType = 'internal';
		$Driver->prepareProductDescription($data);
	}
	
	
	/**
	 * Подготвя данните за показване на описанието на драйвера
	 *
	 * @param stdClass $data
	 * @return void
	 */
	public function prepareProductDescription(&$data)
	{
	}
	
	
	/**
	 * Кои документи са използвани в полетата на драйвера
	 */
	public function getUsedDocs()
	{
		return FALSE;
	}
	
	
	/**
	 * Връща задължителната основна мярка
	 *
	 * @return int|NULL - ид на мярката, или NULL ако може да е всяка
	 */
	public function getDefaultUomId()
	{
		return NULL;
	}
	
	
	/**
	 * Връща иконата на драйвера
	 * 
	 * @return string - пътя към иконата
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * След рендиране на единичния изглед
	 *
	 * @param cat_ProductDriver $Driver
	 * @param embed_Manager $Embedder
	 * @param core_ET $tpl
	 * @param stdClass $data
	 */
	public static function on_AfterRenderSingle(cat_ProductDriver $Driver, embed_Manager $Embedder, &$tpl, $data)
	{
		$nTpl = $Driver->renderProductDescription($data);
		$tpl->append($nTpl, 'innerState');
	}
	
	
	/**
	 * Рендиране на описанието на драйвера
	 *
	 * @param stdClass $data
	 * @return core_ET $tpl
	 */
	protected function renderProductDescription($data)
	{   
        $title = tr($this->singleTitle);

		$tpl = new ET(tr("|*
                    <div class='groupList'>
                        <div class='richtext' style='margin-top: 5px; font-weight:bold;'>{$title}</div>
                        <!--ET_BEGIN info-->
                        <div style='margin-top:5px;'>[#info#]</div>
                        <!--ET_END info-->
						<table class = 'no-border small-padding' style='margin-bottom: 5px;'>
							[#INFO#]
						</table>
					</div>
					[#ROW_AFTER#]
					[#COMPONENTS#]
				"));
		
        $form = cls::get('core_Form');
        $this->addFields($form);
		$driverFields = $form->fields;
		$tpl->replace($data->row->info, 'info');

		if(is_array($driverFields)){
 
            $usedGroups = core_Form::getUsedGroups($form, $driverFields, $data->rec, $data->row, 'single');
    
			foreach ($driverFields as $name => $field){
				if($field->single != 'none' && isset($data->row->{$name})){

                    $caption = $field->caption;

                    if(strpos($caption, '->')) {
                        list($group, $caption) = explode('->', $caption);
                        
                        // Групите, които не се използват - не се показват
                        if(!isset($usedGroups[$group])) continue;

                        $group = tr($group);
                        if($group != $lastGroup) {
                            
                            $dhtml = "<tr><td colspan='3' class='productGroupInfo'>{$group}</td></tr>";
                            $tpl->append($dhtml, 'INFO');
                        }

                        $lastGroup = $group;
                    }

                    $caption = tr($caption);
                    $unit = tr($field->unit);
					
                    if($field->inlineTo) { 
                        $dhtml = new ET(" {$caption} " . $data->row->{$name} . " {$unit}");
                        $tpl->prepend($dhtml, $field->inlineTo);
                    } else {
                        $dhtml = new ET("<tr><td>&nbsp;-&nbsp;</td> <td> {$caption}:</td><td style='padding-left:5px; font-weight:bold;'>" . $data->row->{$name} . " {$unit}[#$name#]</td></tr>");
                        $tpl->append($dhtml, 'INFO');
                    }
				}
			}
		}
 
		return $tpl;
	}
	
	
	/**
	 * Как да се казва дефолт папката където ще отиват заданията за артикулите с този драйвер
	 */
	public function getJobFolderName()
	{
		$title = core_Classes::fetchField($this->getClassId(), 'title');
		
		return "Задания за " . mb_strtolower($title);
	}
	
	
	/**
	 * Връща информация за какви дефолт задачи могат да се задават към заданието за производство
	 * 
	 * @param double $quantity - к-во
	 * @return array $drivers - масив с информация за драйверите, с ключ името на масива
	 * 				    -> title        - дефолт име на задачата
	 * 					-> driverClass  - драйвър на задача
	 * 					-> priority     - приоритет (low=Нисък, normal=Нормален, high=Висок, critical)
	 */
	public function getDefaultProductionTasks($quantity = 1)
	{
		return array();
	}
	
	
	/**
	 * Връща дефолтното име на артикула
	 * 
	 * @param stdClass $rec
	 * @return NULL|string
	 */
	public function getProductTitle($rec)
	{
		return NULL;
	}
	
	
	/**
	 * Връща данни за дефолтната рецепта за артикула
	 * 
	 * @param stdClass $rec - запис
	 * @return FALSE|array
	 * 			['quantity'] - К-во за което е рецептата
	 * 			['expenses'] - % режийни разходи
	 * 			['materials'] array
	 * 				 o code              string          - Код на материала
     * 				 o baseQuantity      double          - Начално количество на вложения материал
     * 				 o propQuantity      double          - Пропорционално количество на вложения материал
     * 				 o type              input|pop|stage - вида на записа материал|отпадък|етап
     * 				 o parentResourceId  string          - ид на артикула на етапа
     * 				 o expenses          double          - % режийни разходи
	 * 				
	 */
	public function getDefaultBom($rec)
	{
		return FALSE;
	}
	
	
	/**
	 * Връща цената за посочения продукт към посочения клиент на посочената дата
	 *
	 * @param mixed $customerClass - клас на контрагента
	 * @param int $customerId - ид на контрагента
	 * @param int $productId - ид на артикула
	 * @param int $packagingId - ид на опаковка
	 * @param double $quantity - количество
	 * @param datetime $datetime - дата
	 * @param double $rate  - валутен курс
	 * @param enum(yes=Включено,no=Без,separate=Отделно,export=Експорт) $chargeVat - начин на начисляване на ддс
	 * @return double|NULL $price  - цена
	 */
	public function getPrice($customerClass, $customerId, $productId, $packagingId = NULL, $quantity = NULL, $datetime = NULL, $rate = 1, $chargeVat = 'no')
	{
		return NULL;
	}
	
	
	/**
	 * Връща дефолтната дефиниция за партида на артикула
	 * Клас имплементиращ интерфейса 'batch_BatchTypeIntf'
	 * 
	 * @param mixed $id - ид или запис на артикул
	 * @return NULL|core_BaseClass - клас за дефиниция на партида
	 */
	public function getDefaultBatchDef($id)
	{
		return NULL;
	}
}
