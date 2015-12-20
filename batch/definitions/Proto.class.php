<?php


/**
 * Базов драйвер за видове партиди
 *
 *
 * @category  bgerp
 * @package   batch
 * @author    Ivelin Dimov <ivelin_pdimov@abv.bg>
 * @copyright 2006 - 2015 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 */
abstract class batch_definitions_Proto extends core_BaseClass
{
	
	
	/**
	 * Интерфейси които имплементира
	 */
	public $interfaces = 'batch_BatchTypeIntf';
	
	
	/**
	 * Зареден запис
	 */
	protected $rec;
	
	
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
    	return TRUE;
    }
    
    
    /**
     * Връща автоматичния партиден номер според класа
     *
     * @param mixed $class - класа за който ще връщаме партидата
     * @param int $id - ид на документа за който ще връщаме партидата
     * @return mixed $value - автоматичния партиден номер, ако може да се генерира
     */
    public function getAutoValue($class, $id)
    {
    }
    
    
    /**
     * Проверява дали стойността е невалидна
     *
     * @param string $value - стойноста, която ще проверяваме
     * @param string &$msg -текста на грешката ако има
     * @return boolean - валиден ли е кода на партидата според дефиницията или не
     */
    public function isValid($value, &$msg)
    {
    	return TRUE;
    }
    
    
    /**
     * Добавя записа
     *
     * @param stdClass $rec
     * @return void
     */
    public function setRec($rec)
    {
    	$this->rec = $rec;
    }
    
    
    /**
     * Проверява дали стойността е невалидна
     *
     * @return core_Type - инстанция на тип
     */
    public function getBatchClassType()
    {
    	$Type = core_Type::getByName('varchar');

    	return $Type;
    }
}